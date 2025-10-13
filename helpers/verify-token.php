<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');
require_once base_path('public/vendor/autoload.php');
$domain_sso = DOMAIN_SSO;

// Untuk memeriksa domain asal
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST']; // akan memberi 'localhost:8084'

$currentUrl = $protocol . $host;
$fromUrl = $_SESSION['from_url'] ?? $currentUrl;

use GuzzleHttp\Client;

$token = $_COOKIE['jwt_token_test'] ?? '';
$response_data = ['success' => false, 'message' => 'Token tidak ditemukan'];

if ($token) {
    try {
        $client = new Client();

        // Kirim current URL sebagai parameter ?url= untuk pengecekan token
        $response = $client->request('GET', $domain_sso . 'api/cek-token.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ],
            'query' => [
                'url' => $currentUrl,
                 'fromUrl' => $fromUrl
            ]
        ]);

        $body = $response->getBody()->getContents();
        $response_data = json_decode($body, true);


        // Kirim encrypted id user status ke API decrypt
        $encrypted_id_user_status = $response_data['user']['id_user_status'] ?? '';
        // Decrypt id user status
        $decrypt_response_user_status = $client->request('POST', $domain_sso . 'api/decrypt.php', [
            'json' => ['data' => $encrypted_id_user_status]
        ]);
        $id_user_status = json_decode($decrypt_response_user_status->getBody(), true)['decrypted'] ?? '';

        // Kirim encrypted id user ke API decrypt
        $encrypted_id_user = $response_data['user']['id_user'] ?? '';
        // Decrypt id user
        $decrypt_response_user = $client->request('POST', $domain_sso . 'api/decrypt.php', [
            'json' => ['data' => $encrypted_id_user]
        ]);
        $id_user = json_decode($decrypt_response_user->getBody(), true)['decrypted'] ?? '';

        // Kirim encrypted key ke API decrypt
        $encrypted_key = $response_data['user']['key'] ?? '';
        // Decrypt key
        $decrypt_response_key = $client->request('POST', $domain_sso . 'api/decrypt.php', [
            'json' => ['data' => $encrypted_key]
        ]);
        $key = json_decode($decrypt_response_key->getBody(), true)['decrypted'] ?? '';

        
        // Kirim encrypted id domain ke API decrypt
        $encrypted_url_website = $response_data['user']['url_website'] ?? '';

        // Decrypt id user
        $decrypt_response_url_website = $client->request('POST', $domain_sso . 'api/decrypt.php', [
            'json' => ['data' => $encrypted_url_website]
        ]);
        $url_website = json_decode($decrypt_response_url_website->getBody(), true)['decrypted'] ?? '';

        // Get data nama user, nama role, status perangkat, expired token dan status klik aktive
        $nama_user = $response_data['user']['nama_user'] ?? '-';
        $role = $response_data['user']['role'] ?? '-';
        $status_perangkat = $response_data['user']['status_perangkat'] ?? '-';
        $expired_token = $response_data['user']['expired_token'] ?? '-';
        $status_klik_active = $response_data['user']['status_klik_active'] ?? '-';

        // Simpan Sesi
        $_SESSION['id_user'] = $id_user;
        $_SESSION['nama_user'] = $nama_user;
        $_SESSION['role'] = $role;
        $_SESSION['key'] = $key;
        $_SESSION['current_url'] = $currentUrl;
        $_SESSION['url_website'] = $url_website;


        // Tampilkan sesi
        // echo "<pre>";
        // print_r($_SESSION);
        // echo "</pre>";
        
        if( $_SESSION['id_user'] == null){
            header("Location:{$domain_sso}logout.php?url={$currentUrl}");
        }

    } catch (\Exception $e) {
        echo "Terjadi error: " . $e->getMessage();
    }
} else {
    header("Location:{$domain_sso}logout.php?url={$currentUrl}");
}
?>
<script>
    window.onload = function () {
        // Ambil semua elemen dengan class "countdown"
        var countdownElements = document.querySelectorAll(".countdown");

        if (countdownElements.length > 0) {
            var countDownDate = new Date().getTime() + (30 * 60 * 1000); // 30 menit

            var x = setInterval(function () {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                // Update semua elemen countdown
                countdownElements.forEach(function (el) {
                    el.innerHTML = minutes + ":" + seconds;
                });

                if (distance < 0) {
                    clearInterval(x);
                    window.location.href = '<?= $domain_sso ?>logout.php?url=<?= $_SESSION['current_url'] ?>';
                }
            }, 1000);
        } else {
            console.log("Tidak ada elemen dengan class 'countdown'.");
        }
    };
</script>
