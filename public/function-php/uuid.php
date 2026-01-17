<?php
    // Set Timezone
    date_default_timezone_set('Asia/Jakarta');
    function uuid() {
        // Timestamp dalam mikrodetik
        $timestamp = microtime(true); 
        // 16 byte acak
        $random = random_bytes(16); 
        // Gabungkan dengan mikrodetik 1e6 berarti 1 dikalikan dengan 10 pangkat 6 = 1.000.000
        $id = base64_encode($random . pack('P', (int)($timestamp * 1e6)));
        return str_replace(['+', '/', '='], '', $id); // Buat URL-friendly
    }
?>
