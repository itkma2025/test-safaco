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

$gradeProduk = DB::connection('safaco')
                    ->table('produk_grade')
                    ->select(
                        'id_grade_produk',
                        'grade'
                    )
                    ->where('status_active', '1')
                    ->orderBy('grade', 'asc')
                    ->get();
?>