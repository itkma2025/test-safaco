<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'edit':
        $title = 'Edit Customer';
        $content = 'customer/edit-data-customer';
        break;
    case 'create':
        $title = 'Tambah Customer';
        $content = 'customer/tambah-data-customer';
        break;
    default:
        $title = 'Data Customer';
        $content = 'customer/data-customer';
        break;
}

view('layouts/app', [
    'title' => $title,
    'content' => $content
]);
