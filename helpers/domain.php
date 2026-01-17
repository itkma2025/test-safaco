<?php  
// Hosting - pakai define agar global dan tidak tergantung scope
// if (!defined('DOMAIN_SSO')) {
//     define('DOMAIN_SSO', 'https://test-sso.mandirialkesindo.co.id/');
//     define('DOMAIN_INSTANSI', 'https://system-instansi.mandirialkesindo.co.id/');
//     define('DOMAIN_WILAYAH', 'https://data-wilayah.mandirialkesindo.co.id/');
//     define('DOMAIN_CUSTOMER', 'https://test-customer.mandirialkesindo.co.id/');
//     define('DOMAIN_SUPPLIER', 'https://test-supplier.mandirialkesindo.co.id/');
//     define('DOMAIN_KATEGORI_PRODUK', 'https://test-katprod.mandirialkesindo.co.id/');
//     define('DOMAIN_INVENTORY_KMA', 'https://test-inventory.mandirialkesindo.co.id/');
//     define('DOMAIN_STICKER', 'https://test-sticker.mandirialkesindo.co.id/');
//     define('JWT_TOKEN', $_COOKIE['jwt_token_test'] ?? '');
//     define('VERIFY_API', true);
// }

// Localhost - pakai define agar global dan tidak tergantung scope
if (!defined('DOMAIN_SSO')) {
    define('DOMAIN_SSO', 'http://localhost:8081/');
    define('DOMAIN_INSTANSI', 'http://localhost:8082/');
    define('DOMAIN_WILAYAH', 'http://localhost:8085/');
    define('DOMAIN_CUSTOMER', 'http://localhost:8086/');
    define('DOMAIN_SUPPLIER', 'http://localhost:8087/');
    define('DOMAIN_KATEGORI_PRODUK', 'http://localhost:8089/');
    define('DOMAIN_INVENTORY_KMA', 'http://localhost:8080/');
    define('DOMAIN_STICKER', 'http://localhost:8090/');
    define('JWT_TOKEN', $_COOKIE['jwt_token_test'] ?? '');
    define('VERIFY_API', false);
}

?>