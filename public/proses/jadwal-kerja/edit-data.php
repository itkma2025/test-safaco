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
require_once base_path('public/function-php/uuid.php');
require_once base_path('helpers/domain.php');
require_once base_path('helpers/functionNull.php');
require_once __DIR__ . '/log-data.php';

// Library validasi input
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

// Load koneksi database
$connect = require_once base_path('config/database/database.php');

// Sanitasi input
$sanitasi_post = sanitizeInput($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot
    if (!empty($sanitasi_post['honeypot'])) {
        echo json_encode(['status' => 'error', 'message' => 'Form ini dikirim oleh bot.']);
        exit;
    }

    // CSRF
    if ($sanitasi_post['csrf_token'] != $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }
    unset($_SESSION['csrf_token']);

    $loader = new ArrayLoader();
    $translator = new Translator($loader, 'en');
    $validatorFactory = new Factory($translator);

    $validator = $validatorFactory->make($sanitasi_post, [
        'nama_jam_kerja'    => 'required|string|max:100',
        'jam_mulai'         => 'required|date_format:H:i',
        'jam_akhir'         => 'required|date_format:H:i',
        'tipe_jam_kerja'    => 'required|in:normal,lembur|string|max:6',
    ], 
    
    // Custom Messages
    [
        'nama_jam_kerja.required'   => 'Nama jam kerja wajib diisi.',
        'nama_jam_kerja.max'        => 'Nama jam kerja max 100 karakter.',
        'jam_mulai.required'        => 'Jam mulai wajib diisi.',
        'jam_mulai.date_format'     => 'Format jam mulai harus HH:MM 24 jam.',
        'jam_akhir.required'        => 'Jam akhir wajib diisi.',
        'jam_akhir.date_format'     => 'Format jam akhir harus HH:MM 24 jam.',
        'tipe_jam_kerja.required'   => 'Tipe jam kerja wajib diisi.'
    ]);

   // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/edit_form_' . date('Y-m-d') . '.log';

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
        $conn = $connect->getConnection('safaco');
        $conn->beginTransaction();

        $id_jadwal_kerja = decryptId($sanitasi_post['id_jadwal_kerja'], $key_akses);
        // Data untuk insert
        $data_jadwal = [
            'nama_jam_kerja'  => $sanitasi_post['nama_jam_kerja'],
            'jam_mulai'       => $sanitasi_post['jam_mulai'], // tersimpan 24 jam, MySQL TIME menambah :00 detik otomatis
            'jam_akhir'       => $sanitasi_post['jam_akhir'],
            'tipe_jam_kerja'  => $sanitasi_post['tipe_jam_kerja'],
            'created_by'      => $_SESSION['id_user']
        ];

        $conn->table('jadwal_kerja')->where('id_jadwal_kerja', $id_jadwal_kerja)->update($data_jadwal);
        $conn->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Data jadwal kerja berhasil disimpan.',
            'redirect_url' => 'data-jadwal-kerja.php?action=jadwal-kerja'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        echo json_encode([
            'status' => 'error', 
            'message' => 'Gagal menyimpan data jadwal kerja.' . $e->getMessage()
        ]);
        exit;
    }
}
?>
