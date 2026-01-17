<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'produk':
        $title = 'Data Produk';
        $submenu = 'produk';
        $content = 'data-produk/produk/data-produk';
        break;

    case 'add-produk':
        $title = 'Data Produk';
        $submenu = 'produk';
        $content = 'data-produk/produk/form-produk';
        break;

    case 'edit-produk':
        $title = 'Data Produk';
        $submenu = 'produk';
        $content = 'data-produk/produk/form-produk';
        break;
    
    case 'detail-produk':
        $title = 'Data Detail Produk';
        $submenu = 'produk';
        $content = 'data-produk/produk/detail-produk';
        break;

    case 'grade':
        $title = 'Data Grade Produk';
        $submenu = 'grade';
        $content = 'data-produk/grade/grade-produk';
        break;

    case 'lokasi':
        $title = 'Data Lokasi Produk';
        $submenu = 'lokasi';
        $content = 'data-produk/lokasi/lokasi-produk';
        break;

    case 'kategori-penjualan':
        $title = 'Data Kategori Penjualan';
        $submenu = 'kategori-penjualan';
        $content = 'data-produk/kategori-penjualan/kategori-penjualan';
        break;
    
     case 'export-excel-produk':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk/export-excel-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-produk':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk/export-pdf-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-master-satuan':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk/query/produk-master-satuan.php';
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
    'active_menu' => 'data-produk',
    'active_submenu' => $submenu
]);
