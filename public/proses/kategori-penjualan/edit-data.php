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
            'kategori_penjualan'       => 'required|string|max:30|regex:/^[a-zA-Z\s]+$/',
            'min_stock'                => 'required|max:9|regex:/^\d{1,3}(\.\d{3})*$/',
            'max_stock'                => 'required|max:9|regex:/^\d{1,3}(\.\d{3})*$/',
            'min_stock_ready'          => 'required|max:9|regex:/^\d{1,3}(\.\d{3})*$/',
            'max_stock_ready'          => 'required|max:9|regex:/^\d{1,3}(\.\d{3})*$/',
        ],
        // Custom Messages
        [   // Custom error messages kategori penjualan
            'kategori_penjualan.required'  => 'Nama kategori wajib diisi.',
            'kategori_penjualan.max'       => 'Nama kategori maksimal 30 karakter.',
            'kategori_penjualan.regex'     => 'Format nama kategori hanya boleh huruf dan spasi.',

            // Custom error messages min stock
            'min_stock.required'   => 'Min stock wajib diisi.',
            'min_stock.max'        => 'Min stock maksimal 9 karakter.',
            'min_stock.regex'      => 'Format min stock hanya boleh angka.',

            // Custom error messages min stock
            'min_stock.required'   => 'Min stock wajib diisi.',
            'min_stock.max'        => 'Min stock maksimal 9 karakter.',
            'min_stock.regex'      => 'Format min stock hanya boleh angka.',

            // Custom error messages max stock
            'max_stock.required'   => 'Max stock wajib diisi.',
            'max_stock.max'        => 'Max stock maksimal 9 karakter.',
            'max_stock.regex'      => 'Format max stock hanya boleh angka.',

            // Custom error messages min stock ready
            'min_stock_ready.required'   => 'Min stock ready wajib diisi.',
            'min_stock_ready.max'        => 'Min stock ready maksimal 9 karakter.',
            'min_stock_ready.regex'      => 'Format min stock ready hanya boleh angka.',

            // Custom error messages max stock ready
            'max_stock_ready.required'   => 'Max stock ready wajib diisi.',
            'max_stock_ready.max'        => 'Max stock ready maksimal 9 karakter.',
            'max_stock_ready.regex'      => 'Format max stock ready hanya boleh angka.',

        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/kategori_penjualan_form_' . date('Y-m-d') . '.log';

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

        $id_kategori_penjualan = decryptId($sanitasi_post['id_kategori_penjualan'], $key_akses);

        $data_check = [
            'kategori_penjualan' => $sanitasi_post['kategori_penjualan'],
        ];

        $exists = $conn->table('kategori_penjualan')
                            ->where($data_check) // langsung pakai array semua kondisi
                            ->where('id_kategori_penjualan', '!=', $id_kategori_penjualan) // abaikan record yang sedang diedit
                            ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, kombinasi Nama Kategori sudah ada"
            ]);
            exit;
        }


        // Proses data kategori
        $data_kategori = [
            'id_kategori_penjualan'   => $id_kategori_penjualan,
            'kategori_penjualan'      => $sanitasi_post['kategori_penjualan'],
            'min_stock'               => str_replace('.', '', $sanitasi_post['min_stock']),
            'max_stock'               => str_replace('.', '', $sanitasi_post['max_stock']),
            'min_stock_ready'         => str_replace('.', '', $sanitasi_post['min_stock_ready']),
            'max_stock_ready'         => str_replace('.', '', $sanitasi_post['max_stock_ready']),
            'updated_by'              => $_SESSION['id_user']
        ];
        
        // Proses simpan data kategori penjualan
        $conn->table('kategori_penjualan')
                ->where('id_kategori_penjualan', $id_kategori_penjualan)
                ->update($data_kategori);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-produk.php?action=kategori-penjualan'
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
