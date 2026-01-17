<?php
require_once __DIR__ . '/../../../helpers/basepath.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/encrypt-decrypt/aes-support.php');

use phpseclib3\Crypt\AES;

function decryptId($encrypted, $key = null)
{
    // Ambil key enkripsi jika tidak diberikan
    $key = getEncryptionKey();

    $aes = new AES('cbc');
    $aes->setKey($key);

    $data = base64url_decode($encrypted);

    // Pastikan data cukup panjang untuk IV + cipher
    if (strlen($data) < 17) {
        return false;
    }

    $iv = substr($data, 0, 16);
    $cipher = substr($data, 16);

    // PENTING: cek panjang cipher sebelum decrypt
    if (strlen($cipher) === 0 || strlen($cipher) % 16 !== 0) {
        return false;
    }

    $aes->setIV($iv);

    return $aes->decrypt($cipher);
}
?>
