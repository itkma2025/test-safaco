<?php  
ob_start(); // Tangkap semua output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}
require_once __DIR__ . '/../../../helpers/basepath.php';
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/sanitasi-input.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once __DIR__ . '/log-data.php';

// Library validasi input
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

// Load koneksi database (akan tampil debug jika aktif)
$connect = require_once base_path('config/database/database.php');

// Setup validator
$loader = new ArrayLoader();
$translator = new Translator($loader, 'en');
$validatorFactory = new Factory($translator);

// Kode utuk sanitasi input
$sanitasi_post = sanitizeInput($_POST);

try {
    $conn = $connect->getConnection('safaco'); // koneksi safaco
    $conn->beginTransaction();

    $id_spk_produksi    = decryptId($sanitasi_post['id_spk_produksi'], $key_akses);
    $referer            = $sanitasi_post['referer']; 

    $valStatus = '';

    if($referer == 'draft'){
        $valStatus = 'Belum Dimulai';
    } else if ($referer == 'belum-dimulai') {
        $valStatus = 'Sudah Dimulai';
    } else if ($referer == 'sudah-dimulai') {
        $valStatus = 'Sudah Selesai';
    }

    // Proses data grade
    $data_spk = [
        'id_spk_produksi'     => $id_spk_produksi,
        'status_spk' => $valStatus,
        'updated_by' => $_SESSION['id_user']
    ];
    // Proses update status active grade
    $conn->table('spk_produksi')
            ->where('id_spk_produksi', $id_spk_produksi)
            ->update($data_spk);

    $conn->commit();

    echo json_encode([
        'status'        => 'success',
        'message'       => 'Status berhasil update.',
        'redirect_url'  => 'perencanaan-produksi.php?action=detail&action=spk-produksi&status=' . $referer 
    ]);
    exit;

} catch (\Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan data:' . $e->getMessage()
    ]);
    exit;
}

?>
