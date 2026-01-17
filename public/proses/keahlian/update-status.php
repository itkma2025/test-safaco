<?php  
ob_start(); // Tangkap semua output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}
require_once __DIR__ . '/../../../helpers/basepath.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/sanitasi-input.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once __DIR__ . '/log-data.php';

// Library validasi input
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

// Load koneksi database (akan tampil debug jika aktif)
$connect = require_once base_path('config/database/database.php');

// Setup validator
$loader = new ArrayLoader();
$translator = new Translator($loader, 'en');
$validatorFactory = new Factory($translator);

// Kode utuk sanitasi input
$sanitasi_post = sanitizeInput($_POST);

$validator = $validatorFactory->make($sanitasi_post,
    // Rules
    [
        'status_active' => 'in:0,1',
    ],
    // Custom Messages
    [   
        'status_active.in' => 'Status active tidak valid.',
    ]
);

// Jika Validasi Gagal
if ($validator->fails()) {
    // Ambil file log yang sedang dipakai
    $logDir = __DIR__ . '/logs'; 
    $logFile = $logDir . '/keahlian_form_' . date('Y-m-d') . '.log';

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

    $id_keahlian = decryptId($sanitasi_post['id'], $key_akses);
    $status_active = $sanitasi_post['status'];
    $status_active_update = $status_active == '1' ? '0' : '1';
    // Proses data keahlian
    $data_keahlian = [
        'id_keahlian' => $id_keahlian,
        'status_active' => $status_active_update,
        'updated_by' => $_SESSION['id_user']
    ];
    // Proses update status active keahlian
    $conn->table('keahlian')
            ->where('id_keahlian', $id_keahlian)
            ->update($data_keahlian);

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Status berhasil update.'
    ]);
    exit;

} catch (\Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan data: '
    ]);
    exit;
}

?>
