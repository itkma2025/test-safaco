<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'kategori-perbaikan':
        $title = 'Data Kategori Perbaikan';
        $submenu = 'kategori-perbaikan';
        $content = 'data-jenis-perbaikan/kategori-perbaikan/kategori-perbaikan';
        break;

    case 'jenis-perbaikan':
        $title = 'Data jenis Perbaikan';
        $submenu = 'jenis-perbaikan';
        $content = 'data-jenis-perbaikan/jenis-perbaikan/jenis-perbaikan';
        break;


    default:
        require_once __DIR__ . '/../views/layouts/404.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'data-jenis-perbaikan',
    'active_submenu' => $submenu
]);
