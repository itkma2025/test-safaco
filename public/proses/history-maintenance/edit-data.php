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

    $loader = new ArrayLoader();
    $translator = new Translator($loader, 'en');
    $validatorFactory = new Factory($translator);

    $validator = $validatorFactory->make($sanitasi_post, [
        'tgl_maintenance'       => 'required|date_format:Y-m-d',
        'petugas_pelaksana'     => 'required|string|max:100',
        'nama_petugas'          => 'required|string|max:100',
        'keterangan_pengerjaan' => 'required|string|max:100',
    ], 
    
    // Custom Messages
    [
        'tgl_maintenance.required'          => 'Tanggal maintenance wajib diisi.',
        'tgl_maintenance.date_format'       => 'Format tanggal maintenance harus YYYY-MM-DD.',
        'petugas_pelaksana.required'        => 'Petugas pelaksana wajib diisi.',
        'petugas_pelaksana.max'             => 'Petugas pelaksana max 100 karakter.',
        'nama_petugas.required'             => 'Nama petugas wajib diisi.',
        'nama_petugas.max'                  => 'Nama petugas max 100 karakter.',
        'keterangan_pengerjaan.required'    => 'Keterangan pengerjaan wajib diisi.',
        'keterangan_pengerjaan.max'         => 'Keterangan pengerjaan max 100 karakter.',
    ]);

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/simpan_form_' . date('Y-m-d') . '.log';

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

        $id_history_maintenance = decryptId($sanitasi_post['id_history_maintenance'], $key_akses);

        // Data untuk insert
        $data_maintenance = [
            'id_history_maintenance'    => $id_history_maintenance,
            'id_alat_mesin'             => decryptId($sanitasi_post['id_alat_mesin'], $key_akses),
            'tgl_maintenance'           => $sanitasi_post['tgl_maintenance'],
            'id_jenis_perbaikan'        => $sanitasi_post['id_jenis_perbaikan'],
            'petugas_pelaksana'         => $sanitasi_post['petugas_pelaksana'],
            'id_supplier'               => $sanitasi_post['id_supplier'],
            'nama_petugas'              => $sanitasi_post['nama_petugas'],
            'keterangan'                => $sanitasi_post['keterangan_pengerjaan'],
            'created_by'                => $_SESSION['id_user']
        ];

        $conn->table('history_maintenance')->where('id_history_maintenance', $id_history_maintenance)->update($data_maintenance);
        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Data berhasil update.',
            'redirect_url' => 'perawatan-alat-mesin.php?action=history-maintenance&id_alat=' . $sanitasi_post['id_alat_mesin']
        ]);
        exit;

    } catch (\Exception $e) {
         if (isset($conn)) {
            $conn->rollBack();
        }

        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/simpan_form_' . date('Y-m-d') . '.log';

        $logError = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'database_error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'post_data' => $_POST,
            'user_id' => $_SESSION['id_user'] ?? null
        ];

        file_put_contents(
            $logFile,
            json_encode($logError, JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data history maintenance.'
        ]);
        exit;
    }
}
?>