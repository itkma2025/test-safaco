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
            'nama_kategori'    => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'deskripsi'        => 'required|string|max:2000',
        ],
        // Custom Messages
        [   
            // Custom error messages grade
            'nama_kategori.required'  => 'Nama kategori wajib diisi.',
            'nama_kategori.max'       => 'Nama kategori maksimal 30 karakter.',
            'nama_kategori.regex'     => 'Format nama kategori hanya boleh huruf dan spasi.',

            // Custom error messages deskripsi
            'deskripsi.required'  => 'Deskripsi kategori wajib diisi.',
            'deskripsi.max'       => 'Deskripsi kategori maksimal 2000 karakter.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/kategori_perbaikan_form_' . date('Y-m-d') . '.log';

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

        $id_kategori_perbaikan = decryptId($sanitasi_post['id_kategori_perbaikan'],  $key_akses);

        $data_check = [
            'nama_kategori' => $sanitasi_post['nama_kategori'],
        ];

        $exists = $conn->table('kategori_perbaikan')
            ->where('id_kategori_perbaikan', '!=',  $id_kategori_perbaikan) // langsung pakai array semua kondisi
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
        $data_kat_perbaikan = [
            'nama_kategori'         => $sanitasi_post['nama_kategori'],
            'deskripsi'             => $sanitasi_post['deskripsi'],
            'updated_by'            => $_SESSION['id_user']
        ];

        // Proses simpan data kategori perbaikan
        $conn->table('kategori_perbaikan')
                ->where('id_kategori_perbaikan', $id_kategori_perbaikan)
                ->update($data_kat_perbaikan);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status'        => 'success',
            'message'       => 'Data berhasil disimpan.',
            'redirect_url'  => 'data-jenis-perbaikan.php?action=kategori-perbaikan'
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
