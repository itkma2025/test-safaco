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
require_once base_path('public/function-php/uuid.php');
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
    // Setup validator
    $loader = new ArrayLoader();
    $translator = new Translator($loader, 'en');
    $validatorFactory = new Factory($translator);

    $validator = $validatorFactory->make($sanitasi_post,
        // Rules
        [
            'id_spk_produksi'       => 'required|string|max:50',
            'id_produk'             => 'required|string|max:50',
        ],
        // Custom Messages
        [   
            // Custom error messages spk produksi
            'id_spk_produksi.required'  => 'ID SPK Produksi wajib diisi.',
            'id_spk_produksi.max'       => 'ID SPK Produksi maksimal 50 karakter.',

            'id_produk.required'        => 'No SPK wajib diisi.',
            'id_produk.max'             => 'No SPK maksimal 50 karakter.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/add_produk_form_' . date('Y-m-d') . '.log';

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

        // Create ID details
        $id_details_produksi   = 'DETAILS_PROD_' . uuid();
        $id_spk_decrypt        = $sanitasi_post['id_spk_produksi'];

        $data_check = [
            'id_spk_produksi' => $id_spk_decrypt,
        ];

        $exists = $conn->table('spk_produksi')
            ->where($data_check) // langsung pakai array semua kondisi
            ->exists();

        if (!$exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, id spk tidak di temukan."
            ]);
            exit;
        }

        // Proses data spk
        $data_detail = [
            'id_details_produksi'   => $id_details_produksi,
            'id_spk_produksi'       => $id_spk_decrypt,
            'id_produk'             => $sanitasi_post['id_produk'],
            'created_by'            => $_SESSION['id_user'] ?? 'Sistem'
        ];
        // Proses simpan data grade
        $conn->table('details_produksi')->insert($data_detail);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }

        // Pastikan folder log ada
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/spk_db_error_' . date('Y-m-d') . '.log';

        // Ambil info error selengkap mungkin
        $errorLog = [
            'timestamp'   => date('Y-m-d H:i:s'),
            'type'        => 'db_exception',
            'message'     => $e->getMessage(),
            'code'        => $e->getCode(),
            'file'        => $e->getFile(),
            'line'        => $e->getLine(),
            'sql_state'   => method_exists($e, 'errorInfo') ? $e->errorInfo[0] ?? null : null,
            'post_data'   => $sanitasi_post,
            'insert_data' => $data_spk ?? null
        ];

        file_put_contents(
            $logFile,
            json_encode($errorLog, JSON_PRETTY_PRINT) . PHP_EOL,
            FILE_APPEND
        );

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data'
        ]);
        exit;
    }
}
?>
