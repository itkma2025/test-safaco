<?php  
require_once __DIR__ . '/../config/config.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');

use GuzzleHttp\Client;

$client = new Client();
$token = $_COOKIE['jwt_token_test'] ?? '';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$currentUrl = $protocol . $_SERVER['HTTP_HOST']; // Bisa disesuaikan
$redirect_url = $protocol . $_SERVER['HTTP_HOST'] . '/login.php'; // Bisa disesuaikan
$domain_sso = DOMAIN_SSO;

if (!$token) {
    header("Location:$redirect_url");
    exit;
}

try {
    $response = $client->post($domain_sso . 'api/logout.php', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token
        ],
        'form_params' => [
            'url_asal' => $currentUrl
        ]
    ]);

    $body = $response->getBody()->getContents();
    $data = json_decode($body, true);

    // Untuk debuging
    // echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";


    if ($data['success']) {
        // Hapus session
        session_destroy(); 

        // // Gunakan redirect_url dari API jika tersedia
        header("Location: $redirect_url");
        exit;
    } else {
        header("Location: $redirect_url");
        exit;
    }
} catch (Exception $e) {
     // Tampilkan error body dari server (jika ada)
    if ($e->hasResponse()) {
        $errorBody = $e->getResponse()->getBody()->getContents();
        // echo "<pre>HTTP ERROR:\n" . htmlspecialchars($errorBody) . "</pre>";
        header("Location: $redirect_url");
        exit;
    } else {
        // echo "<pre>Guzzle Error:\n" . htmlspecialchars($e->getMessage()) . "</pre>";
        header("Location: $redirect_url");
        exit;
    }
}
