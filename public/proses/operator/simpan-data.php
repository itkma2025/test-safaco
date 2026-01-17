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
           'id_operator'    => 'required|string|max:50',
           'id_user'        => 'required|string|max:50',
           'id_keahlian'    => 'required|string|max:50',
        ],
        // Custom Messages
        [   
            // Custom error messages id operator
            'id_operator.required'  => 'ID operator wajib diisi.',
            'id_operator.max'       => 'ID operator maksimal 50 karakter.',

             // Custom error messages id user
            'id_user.required'      => 'ID user wajib diisi.',
            'id_user.max'           => 'ID user maksimal 50 karakter.',

             // Custom error messages id keahlian
            'id_keahlian.required'  => 'ID keahlian wajib diisi.',
            'id_keahlian.max'       => 'ID keahlian maksimal 50 karakter.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/operator_form_' . date('Y-m-d') . '.log';

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
        $connSafaco  = $connect->getConnection('safaco');
        $connUser    = $connect->getConnection('user');
        
        // array semua koneksi
        $connections = [$connSafaco, $connUser];

         // Mulai transaksi di semua koneksi
        foreach ($connections as $conn) {
            $conn->beginTransaction();
        }

        // Start cek data pada database safaco
        // ====================================================================
        // Cek keahlian
        $check_keahlian = [
            'id_keahlian'  => $sanitasi_post['id_keahlian'],
        ];

        $exists_keahlian = $connSafaco->table('keahlian')
            ->where($check_keahlian) // langsung pakai array semua kondisi
            ->exists();
        // ====================================================================
        // End cek data pada database safaco

        // Start cek data pada database user
        // ====================================================================
        // Cek user
        $check_user = [
            'id_user'  => $sanitasi_post['id_user'],
        ];

        $exists_user = $connUser->table('user')
            ->where($check_user) // langsung pakai array semua kondisi
            ->exists();
        // ====================================================================
        // End cek data pada database user
         // Kondisi untuk pengecekan seluruh data di database berbeda
        if (!$exists_keahlian && !$exists_user) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, Data tidak valid"
            ]);
            exit;
        }

        // Pengecekan duplikat data pada table operator
        $check_operator = [
            'id_operator'     => $sanitasi_post['id_operator'],
            'id_user'         => $sanitasi_post['id_user'],
            'id_operator'     => $sanitasi_post['id_operator'],
        ];

        $labels = [
            'id_operator'     => 'ID operator',
            'id_user'         => 'ID user',
            'id_operator'     => 'ID keahlian'
        ];

        foreach ($check_operator as $field => $value) {
            if ($connSafaco->table('operator')->where($field, $value)->exists()) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Gagal menyimpan data, {$labels[$field]} sudah ada"
                ]);
                exit;
            }
        }


        // Proses data operator
        $data_operator = [
            'id_operator'    => $sanitasi_post['id_operator'],
            'id_user'        => $sanitasi_post['id_user'],
            'id_keahlian'    => $sanitasi_post['id_keahlian'],
            'created_by'     => $_SESSION['id_user']
        ];
        // Proses simpan data operator
        $connSafaco->table('operator')->insert($data_operator);

        $connSafaco->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-operator.php?action=operator'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($connSafaco)) {
            $connSafaco->rollBack();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data' . $e->getMessage()
        ]);
        exit;
    }

}
?>
