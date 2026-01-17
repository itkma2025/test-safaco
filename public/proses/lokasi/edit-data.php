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
require_once base_path('helpers/domain.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once base_path('helpers/functionNull.php');
require_once __DIR__ . '/log-data.php';

// Library validasi
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

// Koneksi DB
$connect = require_once base_path('config/database/database.php');

// Sanitasi input
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
            'message' => "Token CSRF tidak valid."
        ]);
        exit;
    }

    // Validasi
    $loader = new ArrayLoader();
    $translator = new Translator($loader, 'en');
    $validatorFactory = new Factory($translator);

    $validator = $validatorFactory->make($sanitasi_post, [
        'id_lokasi'     => 'required|string',
        'nama_lokasi'   => 'required|string|max:30|regex:/^[a-zA-Z\s]+$/',
        'lantai'        => 'required|max:2|regex:/^[0-9]+$/',
        'area'          => 'required|string|max:20',
        'no_rak'        => 'required|string|max:10',
    ], 
    // Custom Messages
    [
        'id_lokasi.required'    => 'ID lokasi wajib ada.',
        'nama_lokasi.required'  => 'Nama lokasi wajib diisi.',
        'nama_lokasi.max'       => 'Nama lokasi maksimal 30 karakter.',
        'nama_lokasi.regex'     => 'Format nama lokasi hanya boleh huruf dan spasi.',
        'lantai.required'       => 'Lantai wajib diisi.',
        'lantai.max'            => 'Lantai maksimal 2 karakter.',
        'lantai.regex'          => 'Format lantai hanya boleh angka.',
        'area.required'         => 'Area wajib diisi.',
        'area.max'              => 'Area maksimal 20 karakter.',
        'no_rak.required'       => 'Nomor rak wajib diisi.',
        'no_rak.max'            => 'Nomor rak maksimal 10 karakter.',
    ]);

    if ($validator->fails()) {
        $logDir = __DIR__ . '/logs';
        $logFile = $logDir . '/lokasi_form_' . date('Y-m-d') . '.log';

        $logError = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'validation_error',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'post_data' => $_POST,
            'errors' => $validator->errors()->all()
        ];
        file_put_contents($logFile, json_encode($logError, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

        echo json_encode([
            'status' => 'error',
            'message' => implode(" ", $validator->errors()->all())
        ]);
        exit;
    }

    try {
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $id_lokasi = decryptId($sanitasi_post['id_lokasi'], $key_akses);

        // Siapkan data untuk pengecekan duplikat
        $data_check = [
            'nama_lokasi' => $sanitasi_post['nama_lokasi'],
            'lantai'      => $sanitasi_post['lantai'],
            'area'        => $sanitasi_post['area'],
            'no_rak'      => $sanitasi_post['no_rak']
        ];

        // Cek duplikat tapi abaikan data sendiri
        $exists = $conn->table('produk_lokasi')
            ->where($data_check)
            ->where('id_lokasi', '!=', $id_lokasi) // abaikan record yang sedang diedit
            ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal mengubah data, kombinasi Nama Lokasi, No. Lantai, Area, dan No. Rak sudah ada"
            ]);
            exit;
        }


        // Data update
        $data_update = [
            'nama_lokasi'   => toNullIfEmpty($sanitasi_post['nama_lokasi']),
            'lantai'        => toNullIfEmpty($sanitasi_post['lantai']),
            'area'          => toNullIfEmpty($sanitasi_post['area']),
            'no_rak'        => toNullIfEmpty($sanitasi_post['no_rak']),
            'updated_by'    => $_SESSION['id_user'],
        ];

        $conn->table('produk_lokasi')
            ->where('id_lokasi', $id_lokasi)
            ->update($data_update);

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'redirect_url' => 'data-produk.php?action=lokasi'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengubah data'
        ]);
        exit;
    }
}
?>
