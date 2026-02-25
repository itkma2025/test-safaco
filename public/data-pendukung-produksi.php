<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'jenis-produksi':
        $title      = 'Jenis Produksi';
        $menu       = 'data-pendukung-produksi';
        $submenu    = 'jenis-produksi';
        $content    = 'data-pendukung-produksi/jenis-produksi/jenis-produksi';
        break;
        
    case 'jenis-pengerjaan':
        $title      = 'Jenis Pengerjaan';
        $menu       = 'data-pendukung-produksi';
        $submenu    = 'jenis-pengerjaan';
        $content    = 'data-pendukung-produksi/jenis-pengerjaan/jenis-pengerjaan';
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
