<?php
use Illuminate\Database\Capsule\Manager as DB;
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');

// --- Ambil keahlian ---
$data_keahlian = $db_safaco->table('keahlian')->orderBy('created_date', 'desc')->get();