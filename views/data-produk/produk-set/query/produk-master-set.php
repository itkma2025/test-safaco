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

use Illuminate\Database\Capsule\Manager as DB;

$domain_sso = DOMAIN_SSO;

/* ===============================
   1. DataTables params
================================ */
$draw   = intval($_POST['draw'] ?? 0);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');

/* ===============================
   2. Connection (SAMA DENGAN KODE LAMA)
================================ */
$db_produk_master = DB::connection('produk_master');
$db_user = DB::connection('user')->getDatabaseName();

/* ===============================
   3. BASE QUERY (IDENTIK)
================================ */
$baseQuery = $db_produk_master
    ->table('tb_produk_set as pm')
    ->leftJoin("file_produk_master as fpm", 'pm.foto_produk', '=', 'fpm.id')
    ->where('pm.status', '1');

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
        $q->where('pm.nama_produk', 'like', "%{$search}%")
          ->orWhere('pm.deskripsi_produk', 'like', "%{$search}%");
    });
}

/* ===============================
   6. TOTAL FILTERED
================================ */
$filteredQuery = clone $baseQuery;
$recordsFiltered = $filteredQuery->count();

/* ===============================
   7. ORDER (TANPA RAW)
================================ */
$baseQuery->orderBy('pm.nama_produk', 'asc');

/* ===============================
   8. DATA
================================ */
$rows = $baseQuery
    ->select(
        'pm.id_produk_master',
        'pm.nama_produk',
        'pm.deskripsi_produk',
        'fpm.filename',
        'fpm.nama_folder',
        'fpm.mime_type',
        'fpm.key_produk'
    )
    ->offset($start)
    ->limit($length)
    ->get();

/* ===============================
   9. FORMAT
================================ */
$data = [];
$no = $start + 1;

foreach ($rows as $row) {

    $imgSrc = '';
    $gambar = 'Tidak ada foto';

    if ($row->filename && $row->nama_folder) {
        $imgSrc = $domain_sso . "enkripsi_file/decrypt_pm.php?"
            . "name=" . rawurlencode($row->filename)
            . "&path=" . rawurlencode($row->nama_folder)
            . "&mime_type=" . rawurlencode($row->mime_type)
            . "&key=" . rawurlencode($row->key_produk);

        $gambar = "
            <a href='$imgSrc' data-fancybox data-width='1600' data-height='1200'>
                <img src='$imgSrc' class='img-fluid img-produk-master' alt='Produk'>
            </a>";
    }

    $aksi = "
        <button class='btn btn-sm btn-primary selectProdukMaster' data-bs-dismiss='modal'
            data-id-produk-master='{$row->id_produk_master}'
            data-nama-produk-master=\"" . htmlspecialchars($row->nama_produk) . "\"
            data-deskripsi-produk-master=\"" . htmlspecialchars($row->deskripsi_produk) . "\"
            data-img-src=\"$imgSrc\">
            Pilih
        </button>
    ";

    $data[] = [
        "<div class='align-middle text-center'>$no</div>",
        "<div class='align-middle text-center'>$gambar</div>",
        "<div class='align-middle'>$row->nama_produk</div>",
        "<div class='align-middle text-wrap'>$row->deskripsi_produk</div>",
        "<div class='align-middle text-center'>$aksi</div>"
    ];

    $no++;
}

/* ===============================
   10. JSON
================================ */
ob_clean();
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
]);
exit;
