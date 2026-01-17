<?php
// Fungsi default untuk kunci enkripsi (harus 32 byte untuk AES-256)
function getEncryptionKey() {
    return 'T3amITKaRS@fAc0B3rk@huTamAm3dik4';
}

// Base64 URL-safe encode
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Base64 URL-safe decode
function base64url_decode($data) {
    $padding = 4 - (strlen($data) % 4);
    if ($padding < 4) {
        $data .= str_repeat('=', $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

$key_akses = $_SESSION['key'];
