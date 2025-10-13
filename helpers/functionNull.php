<?php  
    // Kode agar data tersimpan null di database
    function toNullIfEmpty($val) {
    if (is_string($val)) {
        $val = trim($val); // Hapus spasi jika string
    }

    return $val === '' ? null : $val;
}

?>