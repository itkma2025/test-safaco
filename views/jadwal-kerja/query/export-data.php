<?php
use Illuminate\Database\Capsule\Manager as DB;
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');       // server 1

// --- Ambil produk ---
$data_jadwal_kerja = $db_safaco->table('jadwal_kerja')->orderBy('created_date', 'desc')->get(); // collection of objects