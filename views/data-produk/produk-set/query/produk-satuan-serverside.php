<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    $domain_sso = DOMAIN_SSO;

    // Untuk memeriksa domain asal
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST']; // akan memberi 'localhost:8084'

    $currentUrl = $protocol . $host;
    header("Location:{$domain_sso}logout.php?url={$currentUrl}");
    exit;
}

require_once base_path('public/vendor/autoload.php');
require_once base_path('config/database/database.php');
require_once base_path('helpers/domain.php');
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php'); 

use Illuminate\Database\Capsule\Manager as DB;

/* ===============================
   1. DataTables params
================================ */
$draw   = intval($_POST['draw'] ?? 0);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');

/* ===============================
   2. DB Connections
================================ */
$db_produk   = DB::connection('safaco');
$db_kat_prod = DB::connection('kat_produk');

/* ===============================
   3. BASE QUERY PRODUK
================================ */
$baseQuery = $db_produk
    ->table('produk_satuan as ps')
    ->leftJoin('produk_gambar_satuan as pgs', 'ps.id_produk', '=', 'pgs.id_produk')
    ->where('ps.status_active', '1');

/* ===============================
   4. TOTAL DATA
================================ */
$recordsTotal = (clone $baseQuery)->count();

/* ===============================
   5. SEARCH
================================ */
if ($search !== '') {
    $baseQuery->where(function ($q) use ($search) {
        $q->where('ps.kode_produk', 'like', "%{$search}%")
          ->orWhere('ps.nama_produk', 'like', "%{$search}%");
    });
}

/* ===============================
   6. TOTAL FILTERED
================================ */
$recordsFiltered = (clone $baseQuery)->count();

/* ===============================
   7. ORDER
================================ */
$baseQuery->orderBy('ps.nama_produk', 'asc');

/* ===============================
   8. AMBIL DATA PRODUK (PAGING)
================================ */
$rows = $baseQuery
    ->select(
        'ps.id_produk',
        'ps.kode_produk',
        'ps.nama_produk',
        'ps.id_kategori_produk',
        'pgs.filename'
    )
    ->offset($start)
    ->limit($length)
    ->get();

/* ===============================
   9. AMBIL KATEGORI + MERK (BULK)
================================ */
$idKategori = collect($rows)
    ->pluck('id_kategori_produk')
    ->filter()
    ->unique()
    ->toArray();

$kategoriMap = [];

if (!empty($idKategori)) {
    $kategoriMap = $db_kat_prod
        ->table('tb_kat_produk as tkp')
        ->leftJoin('tb_merk as mr', 'tkp.id_merk', '=', 'mr.id_merk')
        ->whereIn('tkp.id_kat_produk', $idKategori)
        ->select(
            'tkp.id_kat_produk',
            'mr.nama_merk'
        )
        ->get()
        ->keyBy('id_kat_produk');
}

/* ===============================
   10. FORMAT OUTPUT
================================ */
$data = [];
$no = $start + 1;

foreach ($rows as $row) {
    // id produk
    $id_produk = encryptId($row->id_produk, $key_akses);

    // --- Merk ---
    $kategori = $kategoriMap[$row->id_kategori_produk] ?? null;
    $namaMerk = $kategori->nama_merk ?? '-';

    // --- Gambar ---
    $imgSrc = '';
    $gambar = 'Tidak ada foto';

    if (!empty($row->filename)) {
        $imgSrc = "view-img.php?id=" . encryptId($row->id_produk, $key_akses);
        $gambar = "
            <a href='{$imgSrc}' data-fancybox data-width='1600' data-height='1200'>
                <img src='{$imgSrc}' class='img-thumbnail' alt='Produk'>
            </a>
        ";
    }

    // --- Aksi ---
    $aksi = "
        <button class='btn btn-sm btn-primary selectProduk'
            data-bs-dismiss='offcanvas'
            data-id-produk='{$id_produk}'
            data-kode-produk=\"" . htmlspecialchars($row->kode_produk, ENT_QUOTES) . "\"
            data-nama-produk=\"" . htmlspecialchars($row->nama_produk, ENT_QUOTES) . "\"
            data-merk=\"" . htmlspecialchars($namaMerk, ENT_QUOTES) . "\"
            data-img-src=\"{$imgSrc}\">
            Pilih
        </button>
    ";

    $data[] = [
        "<td><div class='text-center'>{$no}</div></td>",
        "<td><div class='text-center'>{$gambar}</div></td>",
        "<td><div class='text-center'>{$row->kode_produk}</div></td>",
        "<td><div>{$row->nama_produk}</div></td>",
        "<td><div class='text-center'>{$namaMerk}</div></td>",
        "<td><div class='text-center'>{$aksi}</div></td>"
    ];

    $no++;
}

/* ===============================
   11. JSON RESPONSE
================================ */
ob_clean();
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
]);
exit;
