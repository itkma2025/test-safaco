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

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST'];
    $currentUrl = $protocol . $host;

    header("Location:{$domain_sso}logout.php?url={$currentUrl}");
    exit;
}

require_once base_path('public/vendor/autoload.php');
require_once base_path('config/database/database.php');
require_once base_path('helpers/domain.php');
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');

use Illuminate\Database\Capsule\Manager as DB;

$draw   = intval($_POST['draw'] ?? 0);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');

/* ===============================
   2. Connection
================================ */
$db_safaco   = DB::connection('safaco');
$db_kat_prod = DB::connection('kat_produk');

/* ===============================
   3. BASE QUERY (SAFACO ONLY)
================================ */
$baseQuery = $db_safaco
    ->table('produk_set as ps')
    ->leftJoin('produk_gambar_set as pgs', 'ps.id_produk_set', '=', 'pgs.id_produk_set')
    ->leftJoin('produk_grade as pg', 'ps.id_grade_produk', '=', 'pg.id_grade_produk')
    ->where('ps.status_active', '1');

/* ===============================
   4. TOTAL DATA
================================ */
$totalQuery = clone $baseQuery;
$recordsTotal = $totalQuery->count();

/* ===============================
   5. SEARCH
================================ */
if ($search !== '') {
    $baseQuery->where(function ($q) use ($search) {
        $q->where('ps.kode_produk_set', 'like', "%{$search}%")
          ->orWhere('ps.nama_produk_set', 'like', "%{$search}%");
    });
}

/* ===============================
   6. TOTAL FILTERED
================================ */
$filteredQuery = clone $baseQuery;
$recordsFiltered = $filteredQuery->count();

/* ===============================
   7. ORDER
================================ */
$baseQuery->orderBy('ps.nama_produk_set', 'asc');

/* ===============================
   8. DATA (SAFACO)
================================ */
$rows = $baseQuery
    ->select(
        'ps.id_produk_set',
        'ps.kode_produk_set',
        'ps.nama_produk_set',
        'ps.id_kategori_produk',
        'pg.grade',
        'pgs.filename'
    )
    ->offset($start)
    ->limit($length)
    ->get();

/* ===============================
   9. Produk yang sudah ada di SPK (untuk disabled)
================================ */
$id_produk_list = [];
if (!empty($id_spk_produksi)) {
    $id_produk_list = $db_safaco
        ->table('details_produksi')
        ->where('id_spk_produksi', $id_spk_produksi)
        ->pluck('id_produk')
        ->toArray();
}

/* ===============================
   9b. Ambil kategori+merk dari DB kat_produk (sekali saja)
================================ */
$idKategoriList = $rows->pluck('id_kategori_produk')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

$kategoriMap = collect();
if (!empty($idKategoriList)) {
    $kategoriMap = $db_kat_prod
        ->table('tb_kat_produk as tkp')
        ->leftJoin('tb_merk as tm', 'tkp.id_merk', '=', 'tm.id_merk')
        ->whereIn('tkp.id_kat_produk', $idKategoriList)
        ->select(
            'tkp.id_kat_produk',
            'tkp.no_izin_edar',
            'tkp.nama_kategori',
            'tm.nama_merk'
        )
        ->get()
        ->keyBy('id_kat_produk');
}

/* ===============================
   10. FORMAT
================================ */
$data = [];
$no = $start + 1;

foreach ($rows as $row) {

    // kategori + merk (hasil mapping)
    $kat = $kategoriMap[$row->id_kategori_produk] ?? null;
    $namaKategori = $kat->nama_kategori ?? '-';
    $nie     = $kat->no_izin_edar ?? '-';

    // gambar
    $gambar = 'Tidak ada foto';
    if (!empty($row->filename)) {
        $imgSrc = 'view-img.php?id=' . encryptId($row->id_produk_set, $key_akses);
        $gambar = "
            <a href='{$imgSrc}' data-fancybox data-width='1600' data-height='1200'>
                <img src='{$imgSrc}' class='img-fluid img-produk-master' alt='Produk'>
            </a>";
    }

    // disabled jika sudah ada
    $disable_button = in_array($row->id_produk_set, $id_produk_list) ? 'disabled' : '';

    $aksi = "
        <button class='btn btn-sm btn-primary selectProduk' {$disable_button}
            data-id-spk=\"" . htmlspecialchars($id_spk_produksi ?? '') . "\"
            data-id-produk=\"" . htmlspecialchars($row->id_produk_set) . "\"
            data-kode-produk=\"" . htmlspecialchars($row->kode_produk_set) . "\"
            data-nama-produk=\"" . htmlspecialchars($row->nama_produk_set) . "\"
            data-nama-kategori=\"" . htmlspecialchars($namaKategori) . "\"
            data-nama-merk=\"" . htmlspecialchars($kat->nama_merk ?? '-') . "\"
            data-nama-grade=\"" . htmlspecialchars($row->grade ?? '-') . "\"
            data-satuan=\"" . htmlspecialchars('Set') . "\"
        >
            Pilih
        </button>
    ";

    $data[] = [
        "<div class='align-middle text-center'>{$no}</div>",
        "<div class='align-middle text-center'>" . htmlspecialchars($row->kode_produk_set) . "</div>",
        "<div class='align-middle'>" . htmlspecialchars($row->nama_produk_set) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($namaKategori) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($nie) . "</div>",
        "<div class='align-middle text-center'>{$aksi}</div>",
    ];

    $no++;
}

/* ===============================
   11. JSON
================================ */
ob_clean();
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
]);
exit;
