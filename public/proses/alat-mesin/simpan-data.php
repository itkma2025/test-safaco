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

// Domain tujuan
$domain_sso = DOMAIN_SSO;
$verify = VERIFY_API;
$tokenJwt = require base_path('helpers/jwt-token.php');

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
            'kode_barang'       => 'required|string|max:50',
            'nama_barang'       => 'required|string|max:100',
            'kalibrasi'         => 'required|in:0,1',
            'jenis_barang'      => 'required|in:Alat,Mesin,Operasional',
            'status_merk'       => 'required|in:0,1',
            'id_merk'           => [
                                        'string',
                                        'max:50',
                                        'required_if:status_merk,1',          // wajib isi kalau Baru atau Bekas
                                        'prohibited_if:status_merk,0' // harus kosong kalau Custom
                                    ],
            'tgl_pembelian'     => 'required|date',
            'kondisi'           => 'required|in:Baru,Bekas,Custom Sendiri (DIY)',
            'id_supplier'       => [
                                        'string',
                                        'max:50', 
                                        'required_if:kondisi,Baru',
                                        'required_if:kondisi,Bekas',
                                        'prohibited_if:kondisi,Custom Sendiri (DIY)'
                                    ],
            'id_lokasi'         => 'required|string|max:50',
        ],
        // Custom Messages
        [   
            // Custom error messages kode barang
            'kode_barang.required'  => 'Kode Barang wajib diisi.',
            'kode_barang.max'       => 'Kode Barang maksimal 50 karakter.',

            // Custom error messages nama barang
            'nama_barang.required'  => 'Nama Barang wajib diisi.',
            'nama_barang.max'       => 'Nama Barang maksimal 100 karakter.',

            // Custom error messages kalibrasi
            'kalibrasi.required'  => 'Kalibrasi wajib diisi.',
            'kalibrasi.in'        => 'Kalibrasi tidak valid.',

            // Custom error messages jenis_barang
            'jenis_barang.required'  => 'Jenis Barang wajib diisi.',
            'jenis_barang.in'        => 'Jenis Barang tidak valid.',

            // Custom error messages merk_barang
            'id_merk.string'        => 'Merk harus berupa teks.',
            'id_merk.max'           => 'Merk maksimal 50 karakter.',
            'id_merk.required_if'   => 'Merk wajib diisi jika status merk Barang adalah Ada.',
            'id_merk.prohibited_if' => 'Merk harus dikosongkan jika status merk Barang adalah Tidak Ada.',

            // Custom error messages tgl_pembelian
            'tgl_pembelian.required'    => 'Tanggal Pembelian wajib diisi.',
            'tgl_pembelian.date'        => 'Tanggal Pembelian tidak valid.',

            // Custom error messages kondisi
            'kondisi.required'  => 'Kondisi wajib diisi.',
            'kondisi.in'        => 'Kondisi tidak valid.',

            // Custom error messages id supplier
            'id_supplier.string'        => 'Supplier harus berupa teks.',
            'id_supplier.max'           => 'Supplier maksimal 50 karakter.',
            'id_supplier.required_if'   => 'Supplier wajib diisi jika kondisi Barang adalah Baru atau Bekas.',
            'id_supplier.prohibited_if' => 'Supplier harus dikosongkan jika kondisi Barang adalah Custom Sendiri (DIY).',

              // Custom error messages id lokasi
            'id_lokasi.required'  => 'Lokasi wajib diisi.',
            'id_lokasi.max'       => 'Lokasi maksimal 50 karakter.',

        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/alat_mesin_form_' . date('Y-m-d') . '.log';

        // Tambahkan entry error ke log
        $logError = [
            'timestamp'     => date('Y-m-d H:i:s'),
            'type'          => 'validation_error',
            'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'post_data'     => $_POST,
            'errors'        => $validator->errors()->all()
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
        $connSafaco         = $connect->getConnection('safaco');
        $connKatProduk      = $connect->getConnection('kat_produk');
        $connSupplier       = $connect->getConnection('supplier');
        
        // array semua koneksi
        $connections = [$connSafaco, $connKatProduk, $connSupplier];

        // Mulai transaksi di semua koneksi
        foreach ($connections as $conn) {
            $conn->beginTransaction();
        }

        // Kondisi untuk cek merk
        if($sanitasi_post['status_merk'] == '1'){
            // Cek data merk
            $check_merk = [
                'id_merk'  => $sanitasi_post['id_merk'],
            ];

            $exists_merk = $connKatProduk->table('tb_merk')
                ->where($check_merk) // langsung pakai array semua kondisi
                ->exists();

            if (!$exists_merk) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Gagal menyimpan data, Data merk tidak valid"
                ]);
                exit;
            }
        }

        // Kondisi untuk cek supplier
        if($sanitasi_post['kondisi'] != 'Custom Sendiri (DIY)'){
            // Cek data supplier
            $check_supplier = [
                'id_supplier'  => $sanitasi_post['id_supplier'],
            ];

            $exists_supplier = $connSupplier->table('supplier')
                ->where($check_supplier) // langsung pakai array semua kondisi
                ->exists();

            // Kondisi untuk pengecekan seluruh data di database berbeda
            if (!$exists_supplier) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Gagal menyimpan data, Data supplier tidak valid"
                ]);
                exit;
            }
        }


        // Cek id dan kode alat mesin
        $check_alat_mesin = [
            'id_alat_mesin'     => $sanitasi_post['id_alat_mesin'],
            'kode_barang'       => $sanitasi_post['kode_barang'],
        ];

        $labels = [
            'id_alat_mesin'     => 'ID Alat Mesin',
            'kode_barang'       => 'Kode Barang',
        ];

        // Buat query pengecekan menggunakan orWhere untuk setiap field
        $query = $connSafaco->table('alat_mesin');
        foreach ($check_alat_mesin as $field => $value) {
            $query->orWhere($field, $value);
        }

        $exists = $query->first();

        if ($exists) {
            // Cari field mana yang bentrok
            foreach ($check_alat_mesin as $field => $value) {
                if ($exists->$field == $value) {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => "Gagal menyimpan data, {$labels[$field]} sudah ada"
                    ]);
                    exit;
                }
            }
        }


        // Proses data alat mesin
        $data_alat_mesin = [
            'id_alat_mesin'     => $sanitasi_post['id_alat_mesin'],
            'kode_barang'       => $sanitasi_post['kode_barang'],
            'nama_barang'       => $sanitasi_post['nama_barang'],
            'kalibrasi'         => $sanitasi_post['kalibrasi'],
            'jenis_barang'      => $sanitasi_post['jenis_barang'],
            'status_merk'       => $sanitasi_post['status_merk'],
            'id_merk'           => toNullIfEmpty($sanitasi_post['id_merk']),
            'tgl_pembelian'     => $sanitasi_post['tgl_pembelian'],
            'kondisi'           => $sanitasi_post['kondisi'],
            'id_supplier'       => toNullIfEmpty($sanitasi_post['id_supplier']),
            'id_lokasi'         => $sanitasi_post['id_lokasi'],
            'created_by'        => $_SESSION['id_user']
        ];

          // Proses simpan gambar
        if (isset($_FILES['fileInput']) && $_FILES['fileInput']['error'] === UPLOAD_ERR_OK) {
            $file_tmp   = $_FILES['fileInput']['tmp_name'];
            $file_name  = $_FILES['fileInput']['name'];
            $file_size  = $_FILES['fileInput']['size'];
        
            if ($file_tmp && is_uploaded_file($file_tmp)) {
                $mime_type = mime_content_type($file_tmp);
                $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_mimes = ['image/jpeg', 'image/png'];
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($mime_type, $allowed_mimes) || !in_array($ext, $allowed_exts)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Format file tidak didukung. Hanya JPEG, PNG dan Webp yang diperbolehkan.'
                    ]);
                    exit;
                }

                if ($file_size > 3 * 1024 * 1024) { // 3MB = 3 * 1024 * 1024 bytes
                     echo json_encode([
                        'status' => 'error',
                        'message' => 'Ukuran gambar terlalu besar. Mohon unggah file dengan ukuran maksimal 3 MB.'
                    ]);
                    exit;
                }

                // Kompres gambar sebelum dikirim ke API, Aktifkan jika di butuhkan
                // if ($file_size > 1.5 * 1024 * 1024) { // 1.5MB = 1.5 * 1024 * 1024 bytes
                //     $compressed = compressImage($file_tmp, $file_name, $mime_type, 90);
                //     $file_tmp   = $compressed['tmp_path'];
                //     $file_name  = $compressed['file_name'];
                //     $mime_type  = $compressed['mime_type'];
                // }

                $key_file   = $_SESSION['key'];
                $file_path  = 'file-safaco/alat-mesin/';

                $client = new Client();
                try {
                    $response = $client->request('POST', $domain_sso . 'api/encryptor.php', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $tokenJwt
                        ],
                        'multipart' => [
                            [
                                'name'     => 'file',
                                'contents' => fopen($file_tmp, 'r'),
                                'filename' => $file_name,
                                'headers'  => ['Content-Type' => $mime_type]
                            ],
                            [
                                'name'     => 'aes_key',
                                'contents' => $key_file
                            ],
                            [
                                'name'     => 'folder',
                                'contents' =>  $file_path // folder target di SSO
                            ]
                        ],
                        'verify' => $verify
                    ]);

                    // Decode response dari API
                    $result = json_decode($response->getBody(), true);
                    // untuk debug
                    // print_r($result);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Respons dari API bukan JSON valid.',
                            'raw_body' => (string) $response->getBody()
                        ]);
                        exit;
                    }

                    // Cek apakah response lengkap
                    if (!empty($result['filename']) && !empty($result['iv']) && !empty($result['signature'])) {
                        // Setelah dapat hasil enkripsi dari Domain 
                        $filename  = base64_decode($result['filename']);   // base64_encode dari Domain C
                        $iv        = base64_decode($result['iv']);         // base64_encode dari Domain C
                        $signature = base64_decode($result['signature']);  // base64_encode dari Domain C
                        // Ambil ekstensi dan nama file dasar
                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                        $base = pathinfo($file_name, PATHINFO_FILENAME);

                        // Generate id
                        $id_gambar_produk = "ALMES_FILE_" . uuid();

                        try {

                            $data_gambar_alat_mesin = [
                                'id_alat_mesin_gambar'      => $id_gambar_produk,
                                'id_alat_mesin'             => $sanitasi_post['id_alat_mesin'],
                                'filename'                  => toNullIfEmpty($filename),
                                'mime_type'                 => toNullIfEmpty($mime_type),
                                'file_path'                 => toNullIfEmpty($file_path),
                                'iv'                        => toNullIfEmpty($iv),
                                'signature'                 => toNullIfEmpty($signature),
                                'key_file'                  => toNullIfEmpty($key_file),
                                'created_by'                => $_SESSION['id_user']
                            ];

                            // Proses simpan data alat mesin
                            $connSafaco->table('alat_mesin')->insert($data_alat_mesin);
                           
                            // Proses simpan data gambar
                            $connSafaco->table('alat_mesin_gambar')->insert($data_gambar_alat_mesin);

                            // Commit transaksi
                            $connSafaco->commit();

                            // Unset sesi setelah validasi
                            unset($_SESSION['csrf_token']);

                            echo json_encode([
                                'status' => 'success',
                                'message' => 'Data berhasil disimpan.',
                                'redirect_url' => 'perawatan-alat-mesin.php?action=list-alat-mesin'
                            ]);
                            exit;
                        } catch (\Exception $e) {
                            $connSafaco->rollBack();

                            $errorMessage = 'Gagal kirim data ke server.';
                            $responseBody = null;

                            // Pastikan hanya akses hasResponse() jika $e adalah RequestException
                            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                                $responseBody = $e->getResponse()->getBody()->getContents();
                            }

                            if ($responseBody) {
                                $decoded = json_decode($responseBody, true);

                                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                                    // Ambil pesan dari API
                                    $errorMessage .= ' ' . $decoded['message'];
                                } else {
                                    // Jika bukan JSON valid, tampilkan sebagian isi respons
                                    $errorMessage .= ' Response: ' . substr($responseBody, 0, 200) . '...';
                                }
                            } else {
                                // Fallback ke pesan error biasa
                                $errorMessage .= ' ' . $e->getMessage();
                            }

                            echo json_encode([
                                'status' => 'error',
                                'message' => $errorMessage
                            ]);
                            exit;
                        }

                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'API enkripsi tidak mengembalikan data lengkap.',
                            'api_response' => $result
                        ]);
                        exit;
                    }

                } catch (RequestException $e) {
                    $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
                    $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Gagal memanggil API enkripsi.',
                        'http_status' => $statusCode,
                        'error_response' => $errorBody,
                        'exception' => $e->getMessage()
                    ]);
                    exit;
                } catch (\Exception $e) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Terjadi kesalahan tidak terduga saat enkripsi.',
                        'exception' => $e->getMessage()
                    ]);  
                    exit;
                }
            }
        }

        // Proses simpan data alat mesin
        $connSafaco->table('alat_mesin')->insert($data_alat_mesin);

        $connSafaco->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'perawatan-alat-mesin.php?action=list-alat-mesin'
        ]);
        exit;

    } catch (\Exception $e) {
        if (isset($connSafaco)) {
            $connSafaco->rollBack();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data'
        ]);
        exit;
    }

}
?>
