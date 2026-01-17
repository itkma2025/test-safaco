<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'list-alat-mesin':
        $title = 'Data Alat Mesin';
        $submenu = 'list-alat-mesin';
        $content = 'perawatan-alat-mesin/list-alat-mesin/list-alat-mesin';
        break;

    case 'history-maintenance':
        $title = 'Data Alat Mesin';
        $submenu = 'list-alat-mesin';
        $content = 'perawatan-alat-mesin/list-alat-mesin/history-maintenance';
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
