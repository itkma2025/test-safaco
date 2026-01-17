<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'export-excel-provinsi':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-excel-provinsi.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-provinsi':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-pdf-provinsi.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-excel-kotakab':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-excel-kotakab.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-kotakab':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-pdf-kotakab.php';
        exit; // Penting! Agar tidak lanjut ke view() 
        break;
    case 'export-excel-kecamatan':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-excel-kecamatan.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-kecamatan':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-pdf-kecamatan.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-excel-kelurahan':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-excel-kelurahan.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf-kelurahan':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-wilayah/export/export-pdf-kelurahan.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'provinsi':
        $title = 'Data Provinsi';
        $submenu = 'data-provinsi';
        $content = 'data-wilayah/data-provinsi';
        break;
    case 'kotakab':
        $title = 'Data Kota / Kabupaten';
        $submenu = 'data-kota-kab';
        $content = 'data-wilayah/data-kota-kab';
        break;
    case 'kecamatan':
        $title = 'Data Kecamatan';
        $submenu = 'data-kecamatan';
        $content = 'data-wilayah/data-kecamatan';
        break;
    case 'kelurahan':
        $title = 'Data Kelurahan';
        $submenu = 'data-kelurahan';
        $content = 'data-wilayah/data-kelurahan';
        break;
    default:
        view('layouts/404');
        exit;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'data-wilayah',
    'active_submenu' => $submenu
]);
