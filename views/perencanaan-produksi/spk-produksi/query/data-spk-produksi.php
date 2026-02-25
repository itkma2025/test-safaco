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

// Ambil status
$status = isset($_GET['status']) ? trim($_GET['status']) : 'Draft';
$valStatus = '';
$activeBelumDimulai = '';
$activeSudahDimulai = '';
$activeSudahSelesai = '';
$activeBatal = '';
$activeDraft = '';
if($status == 'belum-dimulai'){
   $valStatus = 'Belum Dimulai';
   $activeBelumDimulai = 'active';
} else if ($status == 'sudah-dimulai') {
    $valStatus = 'Sudah Dimulai';
    $activeSudahDimulai = 'active';
} else if ($status == 'sudah-selesai') {
    $valStatus = 'Sudah Selesai';
    $activeSudahSelesai = 'active';
} else if ($status == 'batal') {
    $valStatus = 'Batal';
    $activeBatal = 'active';
} else if ($status == 'draft') {
    $valStatus = 'Draft';
    $activeDraft = 'active';
} else {
    $valStatus = 'Draft';
    $activeDraft = 'active';
}

// --- Konfigurasi Pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10; // jumlah data per halaman
$offset  = ($page - 1) * $limit;

// --- Query total data (untuk hitung halaman) ---
$queryTotal = DB::connection('safaco')->table('spk_produksi');
if ($search !== '') {
    $queryTotal->where('nama_spk', 'like', "%{$search}%");
}
$total   = $queryTotal->count();
$pages   = ceil($total / $limit);

// --- Query data sesuai halaman ---
$queryData = DB::connection('safaco')->table('spk_produksi as sp')
                ->leftJoin('jenis_produksi as jp', 'sp.id_jenis_produksi', '=', 'jp.id_jenis_produksi')
                ->leftJoin('jenis_pengerjaan as jn', 'sp.id_jenis_pengerjaan', '=', 'jn.id_jenis_pengerjaan')
                ->select('sp.*', 'jp.nama_jenis_produksi', 'jn.nama_jenis_pengerjaan')
                ->where('status_spk', '=', $valStatus)
                ->orderBy('sp.nama_spk', 'asc');
if ($search !== '') {
    $queryData->where('nama_spk', 'like', "%{$search}%");
}
$data_spk = $queryData->offset($offset)->limit($limit)->get();

// Info pagination untuk view
$pagination = [
    'page'  => $page,
    'limit' => $limit,
    'total' => $total,
    'pages' => $pages
];

// Query untuk menampilkan badge
$badge = DB::connection('safaco')->table('spk_produksi')
            ->selectRaw("
                COUNT(CASE WHEN status_spk = 'Belum Dimulai' THEN 1 END) AS belum_dimulai,
                COUNT(CASE WHEN status_spk = 'Sudah Dimulai' THEN 1 END) AS sudah_dimulai,
                COUNT(CASE WHEN status_spk = 'Sudah Selesai' THEN 1 END) AS sudah_selesai,
                COUNT(CASE WHEN status_spk = 'Batal' THEN 1 END) AS batal,
                COUNT(CASE WHEN status_spk = 'Draft' THEN 1 END) AS draft
            ")
            ->first();
?>