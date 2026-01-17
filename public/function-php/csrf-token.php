<?php  
    function generate_csrf_token() {
        return bin2hex(random_bytes(16)); // menghasilkan 32 karakter hex (128-bit)
    }
?>