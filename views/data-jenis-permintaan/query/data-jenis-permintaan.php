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

// --- Ambil keyword pencarian ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Konfigurasi Pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10; // jumlah data per halaman
$offset  = ($page - 1) * $limit;

// --- Query total data (untuk hitung halaman) ---
$queryTotal = DB::connection('safaco')->table('jenis_permintaan');
if ($search !== '') {
    $queryTotal->where('nama_jenis_permintaan', 'like', "%{$search}%");
}
$total   = $queryTotal->count();
$pages   = ceil($total / $limit);

// --- Query data sesuai halaman ---
$queryData = DB::connection('safaco')->table('jenis_permintaan')->orderBy('nama_jenis_permintaan', 'asc');
if ($search !== '') {
    $queryData->where('nama_jenis_produksi', 'like', "%{$search}%");
}
$data_jenis = $queryData->offset($offset)->limit($limit)->get();

// Info pagination untuk view
$pagination = [
    'page'  => $page,
    'limit' => $limit,
    'total' => $total,
    'pages' => $pages
];
?>