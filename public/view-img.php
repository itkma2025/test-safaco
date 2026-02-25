<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}
require_once __DIR__ . '/../config/config.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('config/database/database.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');

use Illuminate\Database\Capsule\Manager as DB;

use GuzzleHttp\Client;
$client = new Client();

$file_npwp = [];
$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_sso = DOMAIN_SSO;

$id = htmlspecialchars($_GET['id'] ?? null);
$id_decrypt = decryptId($id, $key_akses);
if (!$id) {
    http_response_code(400);
    exit('ID tidak valid');
}

$db_safaco = DB::connection('safaco');
// Query gambar produk satuan
$queryGambarSatuan = $db_safaco->table('produk_satuan as p')
    ->leftJoin("produk_gambar_satuan as pg", 'p.id_produk', '=', 'pg.id_produk')
    ->select(
        'p.id_produk',
        'pg.filename',
        'pg.mime_type',
        'pg.file_path',
        'pg.iv',
        'pg.signature',
        'pg.key_file'
    )
    ->where('p.id_produk', $id_decrypt);

// Query gambar produk set
$queryGambarSet = $db_safaco->table('produk_set as p')
    ->leftJoin("produk_gambar_set as pg", 'p.id_produk_set', '=', 'pg.id_produk_set')
    ->select(
        'p.id_produk_set',
        'pg.filename',
        'pg.mime_type',
        'pg.file_path',
        'pg.iv',
        'pg.signature',
        'pg.key_file'
    )
    ->where('p.id_produk_set', $id_decrypt);

// Query gambar alat mesin
$queryGambarAlatMesin = $db_safaco->table('alat_mesin as am')
    ->leftJoin("alat_mesin_gambar as amg", 'am.id_alat_mesin', '=', 'amg.id_alat_mesin')
    ->select(
        'am.id_alat_mesin',
        'amg.filename',
        'amg.mime_type',
        'amg.file_path',
        'amg.iv',
        'amg.signature',
        'amg.key_file'
    )
    ->where('am.id_alat_mesin', $id_decrypt);

// Ambil data: prioritaskan produk_gambar, kalau null ambil alat_mesin_gambar
$data_gambar = null;

// 1. Cek gambar produk satuan
$data_gambar = $queryGambarSatuan->first();

if (empty($data_gambar) || empty($data_gambar->filename)) {

    // 2. Cek gambar produk set
    $data_gambar = $queryGambarSet->first();

    if (empty($data_gambar) || empty($data_gambar->filename)) {

        // 3. Cek gambar alat mesin
        $data_gambar = $queryGambarAlatMesin->first();
    }
}

// ===============================
// FALLBACK JIKA TIDAK ADA GAMBAR
// ===============================
if (empty($data_gambar) || empty($data_gambar->filename)) {
    header('Content-Type: image/jpeg');
    readfile(base_path('public/assets/img/no_img.jpg'));
    exit;
}

try {
    $response = $client->request('POST', $domain_sso . 'api/decryptor.php', [
        'headers' => [
            'Authorization' => 'Bearer ' . $tokenJwt
        ],
        'form_params' => [
            'filename'   => $data_gambar->filename,
            'mime_type'  => $data_gambar->mime_type,
            'file_path'  => $data_gambar->file_path,
            'iv'         => base64_encode($data_gambar->iv),
            'signature'  => base64_encode($data_gambar->signature),
            'aes_key'    => $data_gambar->key_file,
        ],
        'verify' => false
    ]);

    $decryptedData = $response->getBody()->getContents();

    header("Content-Type: {$data_gambar->mime_type}");
    header("Content-Disposition: inline; filename=\"{$data_gambar->filename}\"");
    echo $decryptedData;

} catch (RequestException $e) {
    http_response_code(500);
    echo "Gagal dekripsi: " . $e->getMessage();
}
?>