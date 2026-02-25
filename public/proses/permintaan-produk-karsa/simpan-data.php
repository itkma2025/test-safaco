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
            'no_permintaan'    => 'required|string|max:25',
            'tgl_permintaan'   => 'required|date',
            'jenis_permintaan' => 'required|string|max:100',
        ],
        // Custom Messages
        [   
            // Custom error messages kategori perbaikan
            'no_permintaan.required'  => 'Nomor permintaan wajib diisi.',
            'no_permintaan.max'       => 'Nomor permintaan maksimal 25 karakter.',

            'tgl_permintaan.required' => 'Tanggal permintaan wajib diisi.',
            'tgl_permintaan.date'     => 'Tanggal permintaan tidak valid.',

            'jenis_permintaan.required' => 'Jenis permintaan wajib diisi.',
            'jenis_permintaan.max'      => 'Jenis permintaan maksimal 100 karakter.',
        ]
    );

    
    // Jika Validasi Gagal
    if ($validator->fails()) {
        require_once __DIR__ . '/log-error-validasi.php';
        exit;
    }

    try{
        $conn = $connect->getConnection('safaco'); // koneksi safaco
        $conn->beginTransaction();

        $data_check = [
            'id_jenis_permintaan' => $sanitasi_post['jenis_permintaan'],
        ];

        $exists = $conn->table('jenis_permintaan')
            ->where($data_check) // langsung pakai array semua kondisi
            ->exists();

        if (!$exists) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, jenis permintaan tidak di temukan."
            ]);
            exit;
        }

        // Proses data cs
        $data_permintaan = [
            'id_permintaan_barang' => $sanitasi_post['id_permintaan_barang'],
            'no_permintaan'        => $sanitasi_post['no_permintaan'],
            'tgl_permintaan'       => $sanitasi_post['tgl_permintaan'],
            'id_jenis_permintaan'  => $sanitasi_post['jenis_permintaan'],
            'catatan'              => $sanitasi_post['catatan'],
            'status_permintaan'    => 'Permohonan Baru',
            'created_by'           => $_SESSION['id_user']
        ];

        // Proses simpan
        $conn->table('permintaan_barang_karsa')->insert($data_permintaan);

        // Proses simpan detail produk
        $produk_json = $_POST['produk'] ?? '[]';
        $produk      = json_decode($produk_json, true);

        foreach ($produk as $item) {
            $data_detail = [
                'id_details_permintaan' => "DTL_PERM_BRG_" . uuid(),
                'id_permintaan_barang'  => $sanitasi_post['id_permintaan_barang'],
                'id_produk'             => $item['idProduk'],
                'qty_request'           => $item['qty'],
                'created_by'            => $_SESSION['id_user']
            ];

            $conn->table('details_permintaan_barang_karsa')->insert($data_detail);
        }
        
        $conn->commit();
        echo json_encode([
            "status" => "success",
            "message" => "berhasil disimpan",
            "data" => [
                "no_permintaan" => $sanitasi_post['no_permintaan'],
                "jumlah_produk" => count($produk)
            ]
        ]);
        exit;
    } catch (Exception $e) {
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/error_log.log';

        // Jika folder belum ada → buat folder
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        // Jika file belum ada → buat file kosong
        if (!file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        
        file_put_contents($logFile, 
            "[" . date('d-m-Y H:i:s') . "] Error: " . $e->getMessage() . "\n", FILE_APPEND);

        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat memproses permintaan.'
        ]);
        exit;
    }
}