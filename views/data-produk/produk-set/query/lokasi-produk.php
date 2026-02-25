<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}

require_once base_path('public/vendor/autoload.php');
require_once base_path('config/database/database.php');

use Illuminate\Database\Capsule\Manager as DB;

$lokasi_produk = DB::connection('safaco')
                    ->table('produk_lokasi')
                    ->select(
                        'id_lokasi',
                        'nama_lokasi',
                        'lantai',
                        'area',
                        'no_rak'
                    )
                    ->where('status_active', '1')
                    ->orderBy('nama_lokasi', 'asc')
                    ->get();
?>