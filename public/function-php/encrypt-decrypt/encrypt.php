<?php  
    require_once __DIR__ . '/../../../helpers/basepath.php';
    require_once base_path('public/vendor/autoload.php');   
    require_once base_path('public/function-php/encrypt-decrypt/aes-support.php');  

    use phpseclib3\Crypt\AES;

    function encryptId($id, $key = null) {
        $key = getEncryptionKey();
        $aes = new AES('cbc');
        $aes->setKey($key);

        $iv = random_bytes(16);
        $aes->setIV($iv);

        $ciphertext = $aes->encrypt((string) $id);
        $combined = $iv . $ciphertext;

        return base64url_encode($combined);
    }

?>