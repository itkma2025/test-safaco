<?php  
ob_start(); // Tangkap semua output
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
require_once base_path('public/function-php/uuid.php');
require_once base_path('helpers/domain.php');
require_once base_path('helpers/functionNull.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 

// Library validasi input
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

// Load koneksi database (akan tampil debug jika aktif)
$connect = require_once base_path('config/database/database.php');

// Library sanitasi input data
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
            'message' => "Token CSRF tidak valid.\n\n"
                    // . "Dari Form (POST): " . ($sanitasi_post['csrf_token'] ?? '[kosong]') . "\n"
                    // . "Dari Session: " . ($_SESSION['csrf_token'] ?? '[kosong]')
        ]);
        exit;
    }

    // Setup validator
    $loader = new ArrayLoader();
    $translator = new Translator($loader, 'en');
    $validatorFactory = new Factory($translator);

    $validator = $validatorFactory->make($sanitasi_post,
        // Rules
        [
            'status' => 'required|string|in:Diterima,Ditolak|max:8',
            'alasan' => 'required_if:status,Ditolak|nullable|string|max:255',
        ],
        // Custom Messages
        [
            'status.required' => 'Status wajib diisi.',
            'status.in'       => 'Status tidak valid.',

            'alasan.required_if' => 'Alasan wajib diisi jika status ditolak.',
            'alasan.max'         => 'Alasan maksimal 255 karakter.',
        ]
    );

    
    // Jika Validasi Gagal
    if ($validator->fails()) {
        require_once __DIR__ . '/log-error-validasi.php';
        exit;
    }

    try{
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $id_permintaan_barang = decryptId($sanitasi_post['id_permintaan_barang'], $key_akses);
        $status = $sanitasi_post['status'];
        $alasan = $sanitasi_post['alasan'];

        // Proses data cs
        $data_permintaan = [
            'persetujuan_mr'        => $sanitasi_post['status'] === 'Diterima' ? '1' : '0',
            'alasan_penolakan_mr'   => $sanitasi_post['alasan'],
            'updated_by'            => $_SESSION['id_user']
        ];

        // Proses simpan
        $conn->table('permintaan_barang_karsa')->where('id_permintaan_barang', $id_permintaan_barang)->update($data_permintaan);
        
        $conn->commit();
        echo json_encode([
            "status" => "success",
            "message" => "berhasil diupdate"
        ]);
        exit;
    } catch (Exception $e) {
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/error_log.log';

        // Jika folder belum ada → buat folder
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        // Jika file belum ada → buat file kosong
        if (!file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        
        file_put_contents($logFile, 
            "[" . date('d-m-Y H:i:s') . "] Error: " . $e->getMessage() . "\n", FILE_APPEND);

        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat memproses permintaan.'
        ]);
        exit;
    }
}