<?php  
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../helpers/domain.php';
use GuzzleHttp\Client;
$client = new Client();
$domain_sso = DOMAIN_SSO;
// Tentukan token dan key
$key = 'RahasiaSaya123';
$url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];


$response = $client->post($domain_sso . 'api/aes-encryption.php', [
    'headers' => [
        'Authorization' => 'Bearer ' . $url
    ],
    'json' => [
        'data' => $url,
        'key' => $key
    ]
]);

$data = json_decode($response->getBody(), true);

if ($data['success']) {
    // Clear session data and destroy the session
    session_unset(); 
    session_destroy(); 

    setcookie('jwt_token_test', '', [
        'expires' => time() - 14400,
        'path' => '/',
        'domain' => 'localhost', // Ganti dengan domain yang sesuai
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    $encrypted_url = $data['encrypted'];
    $encoded_url = urlencode($encrypted_url);

    // Redirect setelah logout
    header("Location: $domain_sso?url=$encoded_url");
    exit;
} else {
    echo "Gagal enkripsi: " . $data['message'];
}

?>