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
        'create'               => 'simpan-data.php',
        'edit'                 => 'edit-data.php',
        'delete'               => 'hapus-data.php',
        'update_status_produk' => 'update-status.php',
    ];

    if (array_key_exists($action, $routes)) {
        require_once base_path('public/proses/produk-satuan/' . $routes[$action]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action tidak dikenali'
        ]);
    }
}




