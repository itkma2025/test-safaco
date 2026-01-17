<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'jadwal-kerja':
        $title = 'Data jadwal-kerja';
        $content = 'jadwal-kerja/data-jadwal-kerja';
        break;

    case 'add-jadwal-kerja':
        $title = 'Data jadwal-kerja';
        $content = 'data-jadwal-kerja/jadwal-kerja/form-jadwal-kerja';
        break;

    case 'edit-jadwal-kerja':
        $title = 'Data jadwal-kerja';
        $content = 'data-jadwal-kerja/jadwal-kerja/form-jadwal-kerja';
        break;
    
     case 'export-excel-jadwal-kerja':
        // Jalankan file export langsung
        require_once __DIR__ . '/../views/jadwal-kerja/export-excel-jadwal-kerja.php';
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
    'active_menu' => 'data-jadwal-kerja',
]);
