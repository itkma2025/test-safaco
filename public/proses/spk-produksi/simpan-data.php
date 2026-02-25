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
            'id_spk_produksi'       => 'required|string|max:50',
            'no_spk'                => 'required|string|max:100',
            'nama_spk'              => 'required|string|max:100',
            'tgl_spk'               => 'required|date',
            'tgl_mulai'             => 'required|date',
            'tgl_akhir'             => 'required|date',
            'id_jenis_produksi'     => 'required|string|max:50',
            'id_jenis_pengerjaan'   => 'required|string|max:50',
            'prioritas_produksi'    => 'required|string|max:50',
            'status_spk'            => 'required|string|max:50',
            'catatan'               => 'required|string|min:20',
        ],
        // Custom Messages
        [   
            // Custom error messages spk produksi
            'id_spk_produksi.required'  => 'ID SPK Produksi wajib diisi.',
            'id_spk_produksi.max'       => 'ID SPK Produksi maksimal 50 karakter.',

            'no_spk.required'           => 'No SPK wajib diisi.',
            'no_spk.max'                => 'No SPK maksimal 100 karakter.',

            'nama_spk.required'         => 'Nama SPK wajib diisi.',
            'nama_spk.max'              => 'Nama SPK maksimal 100 karakter.',

            'tgl_spk.required'          => 'Tgl SPK wajib diisi.',
            'tgl_spk.date'              => 'Tgl SPK harus berupa tanggal yang valid.',

            'tgl_mulai.required'        => 'Tgl Mulai wajib diisi.',
            'tgl_akhir.required'        => 'Tgl Selesai wajib diisi.',

            'id_jenis_produksi.required'   => 'Jenis Produksi wajib diisi.',
            'id_jenis_produksi.max'        => 'Jenis Produksi maksimal 50 karakter.',

            'id_jenis_pengerjaan.required' => 'Jenis Pengerjaan wajib diisi.',
            'id_jenis_pengerjaan.max'      => 'Jenis Pengerjaan maksimal 50 karakter.',

            'prioritas_produksi.required' => 'Prioritas Produksi wajib diisi.',
            'prioritas_produksi.max'      => 'Prioritas Produksi maksimal 50 karakter.',

            'status_spk.required'           => 'Status wajib diisi.',
            'status_spk.max'                => 'Status maksimal 50 karakter.',

            'catatan.required'              => 'Catatan wajib diisi.',
            'catatan.min'                   => 'Catatan minimal 20 karakter.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/spk_add_form_' . date('Y-m-d') . '.log';

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
            'id_spk_produksi' => $sanitasi_post['id_spk_produksi'],
        ];

        $exists = $conn->table('spk_produksi')
            ->where($data_check) // langsung pakai array semua kondisi
            ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, id sudah ada."
            ]);
            exit;
        }

        // Normalisasi datetime tanggal

        $tgl_mulai = null;
        if (!empty($sanitasi_post['tgl_mulai'])) {
            // dari 2026-02-09T14:44 ke 2026-02-09 14:44:00
            $tgl_mulai = date('Y-m-d H:i:s', strtotime($sanitasi_post['tgl_mulai']));
        }

        $tgl_akhir = null;
        if (!empty($sanitasi_post['tgl_akhir'])) {
            $tgl_akhir = date('Y-m-d H:i:s', strtotime($sanitasi_post['tgl_akhir']));
        }



        // Proses data spk
        $data_spk = [
            'id_spk_produksi'       => $sanitasi_post['id_spk_produksi'],
            'no_spk'                => toNullIfEmpty($sanitasi_post['no_spk']),
            'nama_spk'              => toNullIfEmpty($sanitasi_post['nama_spk']),
            'tgl_spk'               => toNullIfEmpty($sanitasi_post['tgl_spk']),
            'tgl_mulai'             => $tgl_mulai,
            'tgl_akhir'             => $tgl_akhir,
            'id_jenis_produksi'     => toNullIfEmpty($sanitasi_post['id_jenis_produksi']),
            'id_jenis_pengerjaan'   => toNullIfEmpty($sanitasi_post['id_jenis_pengerjaan']),
            'prioritas_produksi'    => toNullIfEmpty($sanitasi_post['prioritas_produksi']),
            'status_spk'            => toNullIfEmpty($sanitasi_post['status_spk']),
            'catatan'               => toNullIfEmpty($sanitasi_post['catatan']),
            'created_by'            => $_SESSION['id_user']
        ];
        // Proses simpan data grade
        $conn->table('spk_produksi')->insert($data_spk);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'perencanaan-produksi.php?action=spk-produksi'
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
