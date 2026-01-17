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
// Library API
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Koneksi DB
$connect = require_once base_path('config/database/database.php');

// Sanitasi input
$sanitasi_post = sanitizeInput($_POST);

// Domain tujuan
$domain_sso = DOMAIN_SSO;
// Security API
$verify = VERIFY_API;
$tokenJwt = require base_path('helpers/jwt-token.php');

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
    try {
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $id_alat_mesin = decryptId($sanitasi_post['id_alat_mesin'], $key_akses);

        // Proses unlink file
        // Tampilkan filename untuk di kirim ke api
        $data_gambar = $conn->table('alat_mesin_gambar')
                            ->select('filename')
                            ->where('id_alat_mesin', $id_alat_mesin)
                            ->first();
        $filename = $data_gambar->filename;
        // File path
        $file_path  = 'file-safaco/alat-mesin/';

        // Delete data alat_mesin
        $conn->table('alat_mesin')
                ->where('id_alat_mesin', $id_alat_mesin)
                ->delete();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        $conn->commit();

        if ($filename) {
            try {
                $client->request('POST', $domain_sso . 'api/unlink-file.php', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $tokenJwt
                    ],
                    'multipart' => [
                        ['name' => 'filename', 'contents' => $filename],
                        ['name' => 'folder', 'contents' => 'file-safaco/alat-mesin/']
                    ],
                    'verify' => $verify
                ]);
            } catch (\Exception $e) {
                // ❗ JANGAN rollback
                logError('Unlink gagal', $e->getMessage());
            }
        }


    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
         $errorMessage = $e->getMessage();

        // Deteksi jika error karena foreign key constraint (data masih digunakan)
        if (strpos($errorMessage, 'Integrity constraint violation') !== false) {
            $userMessage = 'Data tidak dapat dihapus karena masih digunakan di tabel lain.';
        } else {
            $userMessage = 'Gagal hapus data: ' . $errorMessage;
        }

        echo json_encode([
            'status' => 'error',
            'message' => $userMessage,
            'redirect_url' => 'perawatan-alat-mesin.php?action=list-alat-mesin'
        ]);
        exit;
    }
}
?>
