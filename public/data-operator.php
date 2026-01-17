<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'operator':
        $title = 'Data Operator';
        $content = 'operator/data-operator';
        break;

    case 'keahlian':
        $title = 'Data Keahlian';
        $submenu = 'operator';
        $content = 'operator/data-keahlian';
        break;

     case 'export-excel-keahlian':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/operator/export-excel-keahlian.php';
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
    'active_menu' => 'data-operator',
]);
