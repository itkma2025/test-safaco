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
            'grade'   => 'required|string|max:30|regex:/^[a-zA-Z\s]+$/',
        ],
        // Custom Messages
        [   
            // Custom error messages grade
            'grade.required'  => 'Grade wajib diisi.',
            'grade.max'       => 'Grade maksimal 30 karakter.',
            'grade.regex'     => 'Format grade hanya boleh huruf dan spasi.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/grade_form_' . date('Y-m-d') . '.log';

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

        $id_grade = decryptId($sanitasi_post['id_grade'], $key_akses);

        $data_check = [
            'grade' => $sanitasi_post['grade'],
        ];

        $exists = $conn->table('produk_grade')
                        ->where($data_check)
                        ->where('id_grade_produk', '!=',  $id_grade)
                        ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, grade sudah ada"
            ]);
            exit;
        }


        // Proses data
        $data_grade = [
            'id_grade_produk'   => $id_grade,
            'grade'             => toNullIfEmpty($sanitasi_post['grade']),
            'updated_by'        => $_SESSION['id_user']
        ];
        
        // Proses simpan data grade
        $conn->table('produk_grade')
                ->where('id_grade_produk', $id_grade)
                ->update($data_grade);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-produk.php?action=grade'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data: ' . $e->getMessage()
        ]);
        exit;
    }

}
?>
