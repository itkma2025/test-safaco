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

$db_kat_prod = DB::connection('kat_produk'); // ini masih object connection
$db_user = DB::connection('user')->getDatabaseName(); // ini string nama database
$katProduk = $db_kat_prod
                ->table('tb_kat_produk as tkp')
                ->leftJoin("file_nie as fn", 'tkp.file_nie', '=', 'fn.id')
                ->leftJoin("tb_merk as mr", 'tkp.id_merk', '=', 'mr.id_merk')
                ->select(
                    'tkp.id_kat_produk',
                    'tkp.nama_kategori',
                    'tkp.no_izin_edar',
                    'tkp.file_nie',
                    'tkp.jenis_nie',
                    'mr.nama_merk',
                    'fn.nama_folder', 
                    'fn.mime_type', 
                    'fn.key_nie', 
                    'fn.filename' 
                )
                ->where('status_aktif', '1')
                ->where('jenis_nie', 'Lokal')
                ->orderBy('nama_kategori', 'asc')
                ->get();
?>