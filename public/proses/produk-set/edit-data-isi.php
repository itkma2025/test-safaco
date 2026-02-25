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
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php'); 
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
           'id_isi_produk_set'  => 'required|string',
           'id_produk'          => 'required|string',
           'qty'                => 'required|integer|min:1',
        ],
        // Custom Messages
        [   
            // Custom error messages id isi set
            'id_isi_produk_set.required'  => 'ID isi set wajib diisi.',

            // Custom error messages id user
            'id_produk.required'      => 'Produk wajib diisi.',

             // Custom error messages id keahlian
            'qty.required'  => 'Qty wajib diisi.',
            'qty.integer'   => 'Qty harus berupa angka.',
            'qty.min'       => 'Qty Minimal 1.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/edit_isi_produk_set' . date('Y-m-d') . '.log';

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
        $connSafaco->beginTransaction();

        $id_isi_produk_set  = decryptId($sanitasi_post['id_isi_produk_set'], $key_akses);
        $id_produk_set      = decryptId($sanitasi_post['id_produk_set'], $key_akses);
        $id_produk          = decryptId($sanitasi_post['id_produk'], $key_akses);
        
        // Cek data set
        $check_data_set = [
            'id_produk_set'  => $id_produk_set,
        ];

        $exists_data_set = $connSafaco->table('produk_set')
            ->where($check_data_set) // langsung pakai array semua kondisi
            ->exists();
     
         // Kondisi untuk pengecekan seluruh data di database berbeda
        if (!$exists_data_set) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, Data tidak valid"
            ]);
            exit;
        }

        // Cek id produk set, dan id produk yang sama
        $exists = $connSafaco->table('isi_produk_set')
            ->where('id_produk_set', $id_produk_set)
            ->where('id_produk', $id_produk)
            ->where('id_isi_produk_set', '!=', $id_isi_produk_set)
            ->exists();

        if ($exists) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menyimpan data, data sudah ada'
            ]);
            exit;
        }


        // Proses data operator
        $data_isi_set = [
            'id_produk'         => $id_produk,
            'qty'               => $sanitasi_post['qty'],
            'updated_by'        => $_SESSION['id_user']
        ];
        // Proses simpan data operator
        $connSafaco->table('isi_produk_set')->where('id_isi_produk_set', $id_isi_produk_set)->update($data_isi_set);

        $connSafaco->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-produk.php?action=isi-produk-set&&id=' . $sanitasi_post['id_produk_set']
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
