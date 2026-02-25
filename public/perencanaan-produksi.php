<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'spk-produksi':
        $title      = 'Perencanaan Produksi';
        $menu       = 'perencanaan-produksi';
        $submenu    = 'spk-produksi';
        $content    = 'perencanaan-produksi/spk-produksi/spk-produksi';
        break;

    case 'detail':
        $title      = 'Perencanaan Produksi';
        $menu       = 'perencanaan-produksi';
        $submenu    = 'spk-produksi';
        $content    = 'perencanaan-produksi/spk-produksi/detail-spk-produksi';
        break;

    case 'produk-satuan':
        require_once __DIR__ . '/../views/perencanaan-produksi/spk-produksi/query/produk-satuan.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-set':
        require_once __DIR__ . '/../views/perencanaan-produksi/spk-produksi/query/produk-set.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'insert-produk':
        require_once __DIR__ . '/proses/spk-produksi/simpan-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    
    default:
        require_once __DIR__ . '/../views/layouts/404.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => $menu,
    'active_submenu' => $submenu
]);
