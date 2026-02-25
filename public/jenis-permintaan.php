<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'view':
        $title = 'Data Jenis Permintaan';
        $submenu = 'jenis-permintaan';
        $content = 'data-jenis-permintaan/jenis-permintaan';
        break;

    default:
        require_once __DIR__ . '/../views/layouts/404.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'data-jenis-permintaan',
    'active_submenu' => $submenu
]);
