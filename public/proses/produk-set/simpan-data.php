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
require_once base_path('public/function-php/uuid.php');
require_once base_path('public/function-php/compress-file.php');
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
// Security API
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
            'id_produk_set'         => 'required|string|max:50',
            'id_produk_master'      => 'required|string|max:50',
            'kode_produk_set'       => 'required|string|max:50',
            'nama_produk_set'       => 'required|string|max:100',
            'id_kategori_produk'    => 'required|string|max:50',
            'harga'                 => 'required|string|max:16|regex:/^[0-9.]+$/',
            'id_kategori_penjualan' => 'required|string|max:50',
            'id_grade_produk'       => 'required|string|max:50',
            'id_lokasi'             => 'required|string|max:50',
            'deskripsi_produk'      => 'required|string|max:2000',
        ],
        // Custom Messages
        [   
            // Custom error messages id produk
            'id_produk_set.required'  => 'ID produk wajib diisi.',
            'id_produk_set.max'       => 'ID produk maksimal 50 karakter.',

            // Custom error messages id produk master
            'id_produk_master.required'  => 'ID produk master wajib diisi.',
            'id_produk_master.max'       => 'ID produk master maksimal 50 karakter.',
            
            // Custom error messages kode produk
            'kode_produk_set.required'  => 'Kode produk wajib diisi.',
            'kode_produk_set.max'       => 'Kode produk maksimal 50 karakter.',

            // Custom error messages nama produk
            'nama_produk_set.required'  => 'Nama produk wajib diisi.',
            'nama_produk_set.max'       => 'Nama produk maksimal 100 karakter.',

            // Custom error messages kategori produk
            'id_kategori_produk.required'  => 'Kategori produk wajib diisi.',
            'id_kategori_produk.max'       => 'Kategori produk maksimal 50 karakter.',

            // Custom error messages kategori penjualan
            'id_kategori_penjualan.required'  => 'Kategori penjualan produk wajib diisi.',
            'id_kategori_penjualan.max'       => 'Kategori penjualan maksimal 50 karakter.',

            // Custom error messages grade produk
            'id_grade_produk.required'  => 'Grade produk wajib diisi.',
            'id_grade_produk.max'       => 'Grade maksimal 50 karakter.',

            // Custom error messages lokasi produk
            'id_lokasi.required'  => 'Lokasi produk wajib diisi.',
            'id_lokasi.max'       => 'Lokasi produk maksimal 50 karakter.',

            // Custom error messages deskripsi
            'deskripsi_produk.required'  => 'Deskripsi produk wajib diisi.',
            'deskripsi_produk.max'       => 'Deskripsi produk maksimal 2000 karakter.',
        ]
    );

    // Jika Validasi Gagal
    if ($validator->fails()) {
        // Ambil file log yang sedang dipakai
        $logDir = __DIR__ . '/logs'; 
        $logFile = $logDir . '/add_produk_set_form_' . date('Y-m-d') . '.log';

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
        if (ob_get_length()) ob_clean();

        $allErrors = implode(" ", $validator->errors()->all());
        echo json_encode([
            'status' => 'error',
            'message' => $allErrors
        ]);

        exit;
    }

    try {
        $connSafaco         = $connect->getConnection('safaco');
        $connProdukMaster   = $connect->getConnection('produk_master');
        $connKatProduk      = $connect->getConnection('kat_produk');
        
        // array semua koneksi
        $connections = [$connSafaco, $connProdukMaster, $connKatProduk];

        // Mulai transaksi di semua koneksi
        foreach ($connections as $conn) {
            $conn->beginTransaction();
        }

        // Start cek data pada database safaco
        // ====================================================================
        // Cek kategori penjualan
        $check_kat_penjualan = [
            'id_kategori_penjualan'  => $sanitasi_post['id_kategori_penjualan'],
        ];

        $exists_kat_penjualan = $connSafaco->table('kategori_penjualan')
            ->where($check_kat_penjualan) // langsung pakai array semua kondisi
            ->exists();


        // Cek grade produk
        $check_grade = [
            'id_grade_produk'  => $sanitasi_post['id_grade_produk'],
        ];

        $exists_grade = $connSafaco->table('produk_grade')
            ->where($check_grade) // langsung pakai array semua kondisi
            ->exists();

        
        // Cek lokasi produk
        $check_lokasi = [
            'id_lokasi'  => $sanitasi_post['id_lokasi'],
        ];

        $exists_lokasi = $connSafaco->table('produk_lokasi')
            ->where($check_lokasi) // langsung pakai array semua kondisi
            ->exists();
        // ====================================================================
        // End cek data pada database safaco

        
        // Start cek data pada database produk master
        // ====================================================================
        // Cek produk master
        $check_produk_master = [
            'id_produk_master'  => $sanitasi_post['id_produk_master'],
        ];

        $exists_produk_master = $connProdukMaster->table('tb_produk_set')
            ->where($check_produk_master) // langsung pakai array semua kondisi
            ->exists();
        // ====================================================================
        // End cek data pada database produk master


        // Start cek data pada database kategori produk
        // ====================================================================
        // Cek kategori produk
        $check_kat_produk = [
            'id_kat_produk'  => $sanitasi_post['id_kategori_produk'],
        ];

        $exists_kat_produk = $connKatProduk->table('tb_kat_produk')
            ->where($check_kat_produk) // langsung pakai array semua kondisi
            ->exists();
        // ====================================================================
        // End cek data pada database kategori produk
        
        // Kondisi untuk pengecekan seluruh data di database berbeda
        if (!$exists_kat_penjualan && !$exists_grade && !$exists_lokasi && !$exists_produk_master && !$exists_kat_produk) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Gagal menyimpan data, Data tidak valid"
            ]);
            exit;
        }

        // Cek id dan kode produk
        $check_produk = [
            'id_produk_set'     => $sanitasi_post['id_produk_set'],
            'kode_produk_set'   => $sanitasi_post['kode_produk_set'],
        ];

        $labels = [
            'id_produk_set'     => 'ID produk',
            'kode_produk_set'   => 'Kode produk'
        ];

        foreach ($check_produk as $field => $value) {
            if ($connSafaco->table('produk_set')->where($field, $value)->exists()) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Gagal menyimpan data, {$labels[$field]} sudah ada"
                ]);
                exit;
            }
        }

        // Cek nama produk dan kategori produk
        $exists = $connSafaco->table('produk_set')
            ->where('nama_produk_set', $sanitasi_post['nama_produk_set'])
            ->where('id_kategori_produk', $sanitasi_post['id_kategori_produk'])
            ->exists();

        if ($exists) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menyimpan data, produk dengan nama dan kategori yang sama sudah ada'
            ]);
            exit;
        }

        // Bersihkan pemisah ribuan
        $harga = (int) str_replace('.', '', $sanitasi_post['harga']);

        // Proses data cs
        $data_produk = [
            'id_produk_set'         => $sanitasi_post['id_produk_set'],
            'id_produk_master'      => toNullIfEmpty($sanitasi_post['id_produk_master']),
            'kode_produk_set'       => toNullIfEmpty($sanitasi_post['kode_produk_set']),
            'nama_produk_set'       => toNullIfEmpty($sanitasi_post['nama_produk_set']),
            'harga'                 => $harga,
            'id_kategori_penjualan' => toNullIfEmpty($sanitasi_post['id_kategori_penjualan']),
            'id_lokasi'             => toNullIfEmpty($sanitasi_post['id_lokasi']),
            'id_kategori_produk'    => toNullIfEmpty($sanitasi_post['id_kategori_produk']),
            'id_grade_produk'       => toNullIfEmpty($sanitasi_post['id_grade_produk']),
            'deskripsi_produk'      => toNullIfEmpty($sanitasi_post['deskripsi_produk']),
            'created_by'            => $_SESSION['id_user']
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
                $file_path  = 'file-safaco/produk-set/';

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
                        $id_gambar_produk = "PRD_FILE_" . uuid();


                        try {

                            $data_gambar_produk = [
                                'id_gambar_produk_set'  => $id_gambar_produk,
                                'id_produk_set'         => $sanitasi_post['id_produk_set'],
                                'filename'              => toNullIfEmpty($filename),
                                'mime_type'             => toNullIfEmpty($mime_type),
                                'file_path'             => toNullIfEmpty($file_path),
                                'iv'                    => toNullIfEmpty($iv),
                                'signature'             => toNullIfEmpty($signature),
                                'key_file'              => toNullIfEmpty($key_file),
                                'created_by'            => $_SESSION['id_user']
                            ];

                            // Proses simpan data produk
                            $connSafaco->table('produk_set')->insert($data_produk);
                           
                            // Proses simpan data gambar
                            $connSafaco->table('produk_gambar_set')->insert($data_gambar_produk);
                            
                            // Commit transaksi
                            $connSafaco->commit();
                            echo json_encode([
                                'status' => 'success',
                                'message' => 'Data berhasil disimpan.',
                                'redirect_url' => 'data-produk.php?action=produk-set'
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
        // Proses simpan data produk
        $connSafaco->table('produk_set')->insert($data_produk);
        // Commit transaksi
        $connSafaco->commit();

        // Unset sesi setelah validasi
        unset($_SESSION['csrf_token']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'redirect_url' => 'data-produk.php?action=produk-set'
        ]);
        exit;

    } catch (\Exception $e) {
        // Kalau ada error di salah satu → rollback semua koneksi
        $connSafaco->rollBack();

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data' . $e->getMessage()
        ]);
        exit;
    }
}
?>
