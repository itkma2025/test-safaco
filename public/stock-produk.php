<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'produk-karsa-reg':
        $title = 'Stock Produk';
        $submenu = 'stock-karsa-reg';
        $content = 'stock-produk/karsa/stock-produk-reguler-satuan';
        break;

    case 'produk-karsa-reg-set':
        $title = 'Stock Produk';
        $submenu = 'stock-karsa-reg';
        $content = 'stock-produk/karsa/stock-produk-reguler-set';
        break;

    case 'produk-karsa-ecat':
        $title = 'Stock Produk';
        $submenu = 'stock-karsa-ecat';
        $content = 'stock-produk/karsa/stock-produk-ecat-satuan';
        break;

    case 'produk-karsa-ecat-set':
        $title = 'Stock Produk';
        $submenu = 'stock-karsa-ecat';
        $content = 'stock-produk/karsa/stock-produk-ecat-set';
        break;

    default:
        require_once __DIR__ . '/../views/layouts/404.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' =>  'stock-karsa',
    'active_submenu' => $submenu
]);
