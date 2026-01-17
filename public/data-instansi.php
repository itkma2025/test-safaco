<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'export-excel':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-instansi/export-excel-instansi.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-instansi/export-pdf-instansi.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    default:
        $title = 'Data Instansi';
        $content = 'data-instansi/data-instansi';
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'data-instansi'
]);
