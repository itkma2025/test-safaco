<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'produk-satuan':
        $title      = 'Data Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-satuan';
        $content    = 'data-produk/produk-satuan/data-produk';
        break;

    case 'add-produk-satuan':
        $title      = 'Data Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-satuan';
        $content    = 'data-produk/produk-satuan/form-produk';
        break;

    case 'edit-produk-satuan':
        $title      = 'Data Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-satuan';
        $content    = 'data-produk/produk-satuan/form-produk';
        break;
    
    case 'detail-produk-satuan':
        $title      = 'Data Detail Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-satuan';
        $content    = 'data-produk/produk-satuan/detail-produk';
        break;

    case 'produk-set':
        $title      = 'Data Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-set';
        $content    = 'data-produk/produk-set/data-produk';
        break;
    
    case 'add-produk-set':
        $title      = 'Data Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-set';
        $content    = 'data-produk/produk-set/form-produk';
        break;

    case 'edit-produk-set':
        $title      = 'Data Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-set';
        $content    = 'data-produk/produk-set/form-produk';
        break;
    
    case 'detail-produk-set':
        $title      = 'Data Detail Produk';
        $menu       = 'data-produk';
        $submenu    = 'produk-set';
        $content    = 'data-produk/produk-set/detail-produk';
        break;

    case 'isi-produk-set':
        $title      = 'Data Isi Produk Set';
        $menu       = 'data-produk';
        $submenu    = 'produk-set';
        $content    = 'data-produk/produk-set/isi-produk-set';
        break;

    case 'grade':
        $title      = 'Data Grade Produk';
        $menu       = 'grade-produk';
        $submenu    = '';
        $content    = 'data-produk/grade/grade-produk';
        break;

    case 'lokasi':
        $title      = 'Data Lokasi Produk';
        $menu       = 'lokasi-produk';
        $submenu    = '';
        $content    = 'data-produk/lokasi/lokasi-produk';
        break;

    case 'kategori-penjualan':
        $title      = 'Data Kategori Penjualan';
        $menu       = 'kategori-penjualan';
        $submenu    = '';
        $content    = 'data-produk/kategori-penjualan/kategori-penjualan';
        break;
    
    case 'export-excel-produk':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-satuan/export-excel-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-produk':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-satuan/export-pdf-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'export-excel-produk-set':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-set/export-excel-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-produk-set':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-set/export-pdf-produk.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-master-satuan':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-satuan/query/produk-master-satuan.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-master-set':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-set/query/produk-master-set.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-satuan-set':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-produk/produk-set/query/produk-satuan-serverside.php';
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
