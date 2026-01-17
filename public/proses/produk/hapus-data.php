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

        $id_produk = decryptId($sanitasi_post['id_produk'], $key_akses);

        // Tampilkan data filename gambar produk\
        $data_gambar = $conn->table('produk_gambar')
                            ->where('id_produk', $id_produk)
                            ->first();
        $filename = $data_gambar->filename;

        if($filename){
            // File path
            $file_path  = 'file-safaco/produk/';

            $client = new Client();
            try {
                $response = $client->request('POST', $domain_sso . 'api/unlink-file.php', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $tokenJwt
                    ],
                    'multipart' => [
                        [
                            'name'     => 'filename',
                            'contents' => $filename
                        ],
                        [
                            'name'     => 'folder',
                            'contents' =>  $file_path // folder target di SSO
                        ]
                    ],
                    'verify' => $verify
                ]);

                // Decode response dari API
                $result = json_decode($response->getBody(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Respons dari API bukan JSON valid.',
                        'raw_body' => (string) $response->getBody()
                    ]);
                    exit;
                }

                // Cek apakah response berhasil
                if (!empty($result['success'])){

                    try {

                        // Delete data produk
                        $conn->table('produk')
                                ->where('id_produk', $id_produk)
                                ->delete();

                        $conn->table('produk_gambar')
                                ->where('id_produk', $id_produk)
                                ->delete();
                        
                        unset($_SESSION['csrf_token']);

                        $conn->commit();

                        // Unset sesi setelah validasi
                        unset($_SESSION['csrf_token']);

                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Data berhasil di hapus.',
                            'redirect_url' => 'perawatan-alat-mesin.php?action=list-alat-mesin'
                        ]);
                        exit;
                    } catch (\Exception $e) {
                        $connect->getConnection()->rollBack();
                        $errorMessage = 'Gagal kirim data ke server.';
                        $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

                        if ($responseBody) {
                            $decoded = json_decode($responseBody, true);
                            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                                // Ambil pesan dari API
                                $errorMessage .= ' ' . $decoded['message'];
                            } else {
                                // Jika bukan JSON valid, tampilkan ringkas
                                $errorMessage .= ' Response: ' . substr($responseBody, 0, 200) . '...';
                            }
                        } else {
                            $errorMessage .= ' ' . $e->getMessage();
                        }

                        echo json_encode([
                            'status' => 'error',
                            'message' => $errorMessage
                        ]);
                        exit;
                    }


                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Data berhasil di hapus.',
                        'redirect_url' => 'perawatan-alat-mesin.php?action=list-alat-mesin'
                    ]);
                    exit;
                }
            } catch (RequestException $e) {
                    $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
                    $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Gagal memanggil API enkripsi.',
                        'http_status' => $statusCode,
                        'error_response' => $errorBody,
                        'exception' => $e->getMessage()
                    ]);
                    exit;
                } catch (\Exception $e) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Terjadi kesalahan tidak terduga saat unlink file.',
                        'exception' => $e->getMessage()
                    ]);  
                    exit;
                }
        }
        
        // Delete data produk
        $conn->table('produk')
                ->where('id_produk', $id_produk)
                ->delete();

        $conn->table('produk_gambar')
                ->where('id_produk', $id_produk)
                ->delete();
        
        unset($_SESSION['csrf_token']);

        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil di hapus.',
            'redirect_url' => 'data-produk.php?action=produk'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal hapus data',
            'redirect_url' => 'data-produk.php?action=produk'
        ]);
        exit;
    }
}
?>
