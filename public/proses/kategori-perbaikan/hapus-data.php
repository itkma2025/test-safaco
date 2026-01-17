<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesi tidak valid'
    ]);
    exit;
}

require_once __DIR__ . '/../../../helpers/basepath.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/sanitasi-input.php');
require_once base_path('helpers/domain.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once base_path('helpers/functionNull.php');
require_once __DIR__ . '/log-data.php';

// Library validasi
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

// Koneksi DB
$connect = require_once base_path('config/database/database.php');

// Sanitasi input
$sanitasi_post = sanitizeInput($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($sanitasi_post['honeypot'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Form ini dikirim oleh bot. Permintaan dibatalkan.'
        ]);
        exit;
    }

    if ($sanitasi_post['csrf_token'] != $_SESSION['csrf_token']) {
        echo json_encode([
            'status' => 'error',
            'message' => "Token CSRF tidak valid."
        ]);
        exit;
    }
    unset($_SESSION['csrf_token']);

    try {
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $id_kategori_perbaikan = decryptId($sanitasi_post['id_kategori_perbaikan'], $key_akses);
        
        // Delete data grade
        $conn->table('kategori_perbaikan')
                ->where('id_kategori_perbaikan', $id_kategori_perbaikan)
                ->delete();

        $conn->commit();

        echo json_encode([
            'status'        => 'success',
            'message'       => 'Data berhasil di hapus.',
            'redirect_url'  => 'data-jenis-perbaikan.php?action=kategori-perbaikan'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        echo json_encode([
            'status'    => 'error',
            'message'   => 'Gagal hapus data'
        ]);
        exit;
    }
}
?>
