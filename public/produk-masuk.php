<?php
require_once __DIR__ . '/../config/config.php';

$action = htmlspecialchars($_GET['action'] ?? 'list', ENT_QUOTES, 'UTF-8');

switch ($action) {
    case 'karsa-permohonan-baru':
        $title = 'Data Produk Masuk';
        $submenu = 'produk-masuk-karsa';
        $content = 'produk-masuk/permintaan-produk-karsa/permohonan-baru';
        break;

    case 'karsa-menunggu-persetujuan':
        $title = 'Data Produk Masuk';
        $submenu = 'produk-masuk-karsa';
        $content = 'produk-masuk/permintaan-produk-karsa/menunggu-persetujuan';
        break;

    case 'details-permintaan-produk-karsa':
        $title = 'Data Produk Masuk';
        $submenu = 'produk-masuk-karsa';
        $content = 'produk-masuk/permintaan-produk-karsa/details-permintaan-produk-karsa';
        break;

    case 'simpan-permintaan-produk-karsa':
        require_once __DIR__ . '/proses/permintaan-produk-karsa/simpan-data.php';
        break;
    
    case 'update-status-pjt-safaco':
        require_once __DIR__ . '/proses/permintaan-produk-karsa/update-status-pjt-safaco.php';
        break;

    case 'update-status-mr':
        require_once __DIR__ . '/proses/permintaan-produk-karsa/update-status-mr.php';
        break;


    case 'produk-reguler':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/produk-reguler.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-ecat':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/produk-ecat.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-set-marwa':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/produk-set-marwa.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'produk-set-ecat':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/produk-set-ecat.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'persetujuan-pjt-safaco':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/persetujuan-pjt-safaco.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'persetujuan-mr':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/persetujuan-mr.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    case 'query-details-permintaan-produk-karsa':
        require_once __DIR__ . '/../views/produk-masuk/permintaan-produk-karsa/query/details-permintaan-produk-karsa.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;

    default:
        require_once __DIR__ . '/../views/layouts/404.php';
        exit; // Penting! Agar tidak lanjut ke view()
        break;
}
 
view('layouts/app', [
    'title' => $title,
    'content' => $content,
    'active_menu' => 'perawatan-alat-mesin',
    'active_submenu' => $submenu
]);
