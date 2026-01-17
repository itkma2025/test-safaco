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
$queryTotal = DB::connection('safaco')->table('jenis_perbaikan');
if ($search !== '') {
    $queryTotal->where('nama_jenis_perbaikan', 'like', "%{$search}%");
}
$total   = $queryTotal->count();
$pages   = ceil($total / $limit);

// --- Query data sesuai halaman ---
$queryData = DB::connection('safaco')
            ->table('jenis_perbaikan as jp')
            ->leftJoin("kategori_perbaikan as kp", 'jp.id_kategori_perbaikan', '=', 'kp.id_kategori_perbaikan')
            ->select(
                'jp.id_jenis_perbaikan',
                'jp.nama_jenis_perbaikan',
                'kp.nama_kategori',
                'kp.deskripsi',
                'jp.status_active',
                'jp.created_date',
                'jp.created_by',
                'jp.updated_date',
                'jp.updated_by'
            )
            ->orderBy('jp.nama_jenis_perbaikan', 'asc');
if ($search !== '') {
    $queryData->where('jp.nama_jenis_perbaikan', 'like', "%{$search}%");
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