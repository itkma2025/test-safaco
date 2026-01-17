<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'export-excel':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-sales/export-excel-sales.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    case 'export-pdf':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/data-sales/export-pdf-sales.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
    default:
        $title = 'Data Instansi';
        $content = 'data-sales/data-sales';
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'data-sales'
]);
