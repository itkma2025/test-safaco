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
// Library API
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
            'nama_lokasi'   => 'required|string|max:30|regex:/^[a-zA-Z\s]+$/',
            'lantai'        => 'required|max:2|regex:/^[0-9]+$/',
            'area'          => 'required|string|max:20',
            'no_rak'        => 'required|string|max:10',
        ],
        // Custom Messages
        [   
            // Custom error messages nama lokasi
            'nama_lokasi.required'  => 'Nama lokasi wajib diisi.',
            'nama_lokasi.max'       => 'Nama lokasi maksimal 30 karakter.',
            'nama_lokasi.regex'     => 'Format nama lokasi hanya boleh huruf dan spasi.',

            // Custom error messages lantai
            'lantai.required'   => 'Lantai wajib diisi.',
            'lantai.max'        => 'Lantai maksimal 2 karakter.',
            'lantai.regex'      => 'Format lantai hanya boleh angka.',

            // Custom error messages area
            'area.required'     => 'Area wajib diisi.',
            'area.max'          => 'Area maksimal 20 karakter.',

            // Custom error messages no rak
            'no_rak.required'   => 'Nomor rak wajib diisi.',
            'no_rak.max'        => 'Nomor rak maksimal 10 karakter.',
            'no_rak.regex'      => 'Format nomor rak hanya boleh huruf dan angka.',

        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/lokasi_form_' . date('Y-m-d') . '.log';

        // Tambahkan entry error ke log
        $logError = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'validation_error',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'post_data' => $_POST,
            'errors' => $validator->errors()->all()
        ];

        file_put_contents($logFile, json_encode($logError, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

        // Kirim response ke frontend
        $allErrors = implode(" ", $validator->errors()->all());
        echo json_encode([
            'status' => 'error',
            'message' => $allErrors
        ]);

        exit;
    }

    try {
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $data_check = [
            'nama_lokasi' => $sanitasi_post['nama_lokasi'],
            'lantai'      => $sanitasi_post['lantai'],
            'area'        => $sanitasi_post['area'],
            'no_rak'      => $sanitasi_post['no_rak']
        ];

        $exists = $conn->table('produk_lokasi')
            ->where($data_check) // langsung pakai array semua kondisi
            ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, kombinasi Nama Lokasi, No. Lantai, Area, dan No. Rak sudah ada"
            ]);
            exit;
        }


        // Proses data cs
        $data_lokasi = [
            'id_lokasi'     => $sanitasi_post['id_lokasi'],
            'nama_lokasi'   => toNullIfEmpty($sanitasi_post['nama_lokasi']),
            'lantai'        => toNullIfEmpty($sanitasi_post['lantai']),
            'area'          => toNullIfEmpty($sanitasi_post['area']),
            'no_rak'        => toNullIfEmpty($sanitasi_post['no_rak']),
            'created_by'    => $_SESSION['id_user']
        ];
        // Proses simpan data customer
        $conn->table('produk_lokasi')->insert($data_lokasi);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-produk.php?action=lokasi'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data'
        ]);
        exit;
    }

}
?>
