<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}

require_once base_path('helpers/domain.php');
require_once base_path('config/database/database.php');
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php'); 
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

use Illuminate\Database\Capsule\Manager as DB;

$db_safaco = DB::connection('safaco');

// --- Ambil keyword pencarian ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Konfigurasi Pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10; // jumlah data per halaman
$offset  = ($page - 1) * $limit;

// --- Query total data (untuk hitung halaman) ---
$queryTotal = $db_safaco->table('keahlian');
if ($search !== '') {
    $queryTotal->where('nama_keahlian', 'like', "%{$search}%");
}
$total   = $queryTotal->count();
$pages   = ceil($total / $limit);

// --- Query data sesuai halaman (join alat_mesin) ---
$queryData = $db_safaco
    ->table('keahlian as kh')
    ->leftJoin('keahlian_alat_mesin as kam', 'kh.id_keahlian', '=', 'kam.id_keahlian')
    ->leftJoin('alat_mesin as am', 'kam.id_alat_mesin', '=', 'am.id_alat_mesin')
    ->select(
        'kh.*',
        $db_safaco->raw("GROUP_CONCAT(am.nama_barang SEPARATOR ', ') as nama_mesin")
    )
    ->orderBy('kh.nama_keahlian', 'asc')
    ->groupBy('kh.id_keahlian');

if ($search !== '') {
    $queryData->where('kh.nama_keahlian', 'like', "%{$search}%");
}

$data_keahlian = $queryData->offset($offset)->limit($limit)->get();


$data_keahlian = $queryData->offset($offset)->limit($limit)->get();

// --- Info pagination untuk view ---
$pagination = [
    'page'  => $page,
    'limit' => $limit,
    'total' => $total,
    'pages' => $pages
];
?>
