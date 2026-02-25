<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'karsa':
        $title = 'Data Produk Masuk';
        $submenu = 'produk-masuk-karsa';
        $content = 'produk-masuk/permintaan-produk-karsa';
        break;

    case 'simpan-permintaan-produk-karsa':
        require_once __DIR__ . '/proses/permintaan-produk-karsa/simpan-data.php';
        break;

    

    default:
        require_once __DIR__ . '/../views/layouts/404.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
}
 
view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'perawatan-alat-mesin',
    'active_submenu' => $submenu
]);
