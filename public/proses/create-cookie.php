<?php
// Ambil token dari query string
$token = $_GET['token'] ?? null;

if (!$token) {
    header("Location:{$sso_url}logout.php?url={$_SESSION['current_url']}");
    exit;
}

// Set cookie JWT (akan tersimpan di browser karena ini direct response)
setcookie('jwt_token_test', $token, [
    'expires' => time() + 14400, // 4 jam dari sekarang
    'path' => '/',
    'domain' => 'localhost', // Ganti dengan domain yang sesuai
    'secure' => true,      // true jika HTTPS
    'httponly' => true,     // tidak bisa diakses JS
    'samesite' => 'none'     // atau 'None' jika cross-domain
]);
header("Location:../dashboard.php");
exit;
