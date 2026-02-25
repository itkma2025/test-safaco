<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}
require_once __DIR__ . '/../../helpers/basepath.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
// Library sanitasi input data
require_once base_path('public/function-php/sanitasi-input.php');
$sanitasi_post = sanitizeInput($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = decryptId($sanitasi_post['action'], $key_akses);

    // Daftar action dan file handler
    $routes = [
        // Untuk produk set
        'create'               => 'simpan-data.php',
        'edit'                 => 'edit-data.php',
        'delete'               => 'hapus-data.php',
        'update_status_produk' => 'update-status.php',

        // Untuk isi set
        'create-isi'           => 'simpan-data-isi.php',
        'edit-isi'             => 'edit-data-isi.php',
        'delete-isi'           => 'hapus-data-isi.php',
    ];

    if (array_key_exists($action, $routes)) {
        require_once base_path('public/proses/produk-set/' . $routes[$action]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action tidak dikenali'
        ]);
    }
}


