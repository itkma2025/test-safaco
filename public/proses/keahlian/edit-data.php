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
            'nama_keahlian'     => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'status_mesin'      => 'required|in:0,1',
            'id_alat_mesin'     =>  [
                                        'required_if:status_mesin,1',          // wajib isi kalau Baru atau Bekas
                                        'prohibited_if:status_mesin,0' // harus kosong kalau Custom
                                    ],
        ],
        // Custom Messages
        [   
            // Custom error messages keahlian
            'nama_keahlian.required'    => 'Nama keahlian wajib diisi.',
            'nama_keahlian.max'         => 'Nama keahlian maksimal 50 karakter.',
            'nama_keahlian.regex'       => 'Format nama keahlian hanya boleh huruf dan spasi.',

            // Custom error messages id alat / mesin
            'id_alat_mesin.required_if'       => 'Alat / Mesin wajib diisi jika status Alat / Mesin Barang adalah Ada.',
            'id_alat_mesin.prohibited_if'     => 'Alat / Mesin harus dikosongkan jika status merk Barang adalah Tidak Ada.',
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

        $id_keahlian = decryptId($sanitasi_post['id_keahlian'], $key_akses);

        $data_check = [
            'nama_keahlian' => $sanitasi_post['nama_keahlian'],
        ];

        $exists = $conn->table('keahlian')
                        ->where($data_check)
                        ->where('id_keahlian', '!=', $id_keahlian)
                        ->exists();

        if ($exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, kombinasi Nama Keahlian sudah ada"
            ]);
            exit;
        }

        // Conversi id mesin menjadi string
        $id_alat_mesin = null;
        if (!empty($sanitasi_post['id_alat_mesin']) && is_array($sanitasi_post['id_alat_mesin'])) {
            // gabungkan array jadi string "mesin001,mesin002"
            $id_alat_mesin = implode(',', $sanitasi_post['id_alat_mesin']);
        }

        // Proses data keahlian
        $data_keahlian = [
            'nama_keahlian'     => toNullIfEmpty($sanitasi_post['nama_keahlian']),
            'status_mesin'      => $sanitasi_post['status_mesin'],
            'updated_by'        => $_SESSION['id_user']
        ];

        // Proses simpan data keahlian
        $conn->table('keahlian')->where('id_keahlian', $id_keahlian)->update($data_keahlian);

        // Hapus relasi alat mesin sebelumnya
        $conn->table('keahlian_alat_mesin')->where('id_keahlian', $id_keahlian)->delete();

        // Simpan ke pivot table keahlian_alat_mesin
        if (!empty($sanitasi_post['id_alat_mesin']) && is_array($sanitasi_post['id_alat_mesin'])) {
            $pivotData = [];
            foreach ($sanitasi_post['id_alat_mesin'] as $idMesin) {
                $pivotData[] = [
                    'id_keahlian_alat_mesin' => 'K_AM_' . uuid(), // kalau pakai UUID, kalau auto increment tidak perlu
                    'id_keahlian'   => $id_keahlian,
                    'id_alat_mesin' => $idMesin,
                    'created_date'  => date('Y-m-d H:i:s'),
                    'created_by'    => $_SESSION['id_user']
                ];
            }
            $conn->table('keahlian_alat_mesin')->insert($pivotData);
        }

        $conn->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-operator.php?action=keahlian'
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
