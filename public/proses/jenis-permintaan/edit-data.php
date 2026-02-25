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
require_once __DIR__ . '/log-data.php';

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
            'nama_jenis_permintaan'    => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
        ],
        // Custom Messages
        [   
            // Custom error messages grade
            'nama_jenis_permintaan.required'  => 'Nama jenis permintaan wajib diisi.',
            'nama_jenis_permintaan.max'       => 'Nama jenis permintaan maksimal 100 karakter.',
            'nama_jenis_permintaan.regex'     => 'Format nama jenis permintaan hanya boleh huruf dan spasi.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/edit_jenis_permintaan_form_' . date('Y-m-d') . '.log';

        // Tambahkan entry error ke log
        $logError = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type'          => 'validation_error',
            'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'post_data'     => $_POST,
            'errors'        => $validator->errors()->all()
        ];

        file_put_contents($logFile, json_encode($logError, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

        // Kirim response ke frontend
        $allErrors = implode(" ", $validator->errors()->all());
        echo json_encode([
            'status'    => 'error',
            'message'   => $allErrors
        ]);

        exit;
    }

    try {
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $id_jenis_permintaan = decryptId($sanitasi_post['id_jenis_permintaan'],  $key_akses);

        $data_check = [
            'nama_jenis_permintaan' => $sanitasi_post['nama_jenis_permintaan'],
        ];

        $exists = $conn->table('jenis_permintaan')
            ->where('id_jenis_permintaan', '!=',  $id_jenis_permintaan) // langsung pakai array semua kondisi
            ->where($data_check) // langsung pakai array semua kondisi
            ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, kategori perbaikan sudah ada"
            ]);
            exit;
        }

        // Proses data cs
        $data_jenis_permintaan = [
            'nama_jenis_permintaan'   => $sanitasi_post['nama_jenis_permintaan'],
            'updated_by'            => $_SESSION['id_user']
        ];

        // Proses simpan data jenis produksi
        $conn->table('jenis_permintaan')
                ->where('id_jenis_permintaan', $id_jenis_permintaan)
                ->update($data_jenis_permintaan);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status'        => 'success',
            'message'       => 'Data berhasil disimpan.',
            'redirect_url'  => 'jenis-permintaan.php?action=view'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data' .$e->getMessage()
        ]);
        exit;
    }
}
?>
