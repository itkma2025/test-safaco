<?php  
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('config/database/database.php');
    use Illuminate\Database\Capsule\Manager as DB;

    // Query untuk menampilkan data permintaan produk yang menunggu persetujuan PJT Safaco
    $total_data_pjt_skm = DB::connection('safaco')
        ->table('permintaan_barang_karsa as pbk')
        ->leftJoin('jenis_permintaan as jp', 'pbk.id_jenis_permintaan', '=', 'jp.id_jenis_permintaan')
        ->where('pbk.status_permintaan', 'Permohonan Baru')
        ->where('pbk.persetujuan_pjt_safaco', '0')
        ->count();

    // Query untuk menampilkan data permintaan produk yang menunggu persetujuan PJT MR
    $total_data_mr = DB::connection('safaco')
        ->table('permintaan_barang_karsa as pbk')
        ->leftJoin('jenis_permintaan as jp', 'pbk.id_jenis_permintaan', '=', 'jp.id_jenis_permintaan')
        ->where('pbk.status_permintaan', 'Permohonan Baru')
        ->where('pbk.persetujuan_pjt_safaco', '1')
        ->where('pbk.persetujuan_mr', '0')
        ->count();

    $total_data = $total_data_pjt_skm + $total_data_mr;
?>
<nav class="nav nav-style-6 nav-pills mb-3 border-bottom" role="tablist">
    <a class="nav-link <?= ($_GET['action'] ?? '') === 'karsa-permohonan-baru' ? 'active' : '' ?>" href="produk-masuk.php?action=karsa-permohonan-baru">
        Permohonan Baru
        <span class="badge bg-secondary ms-1 rounded-pill"></span>
    </a>

    <a class="nav-link <?= ($_GET['action'] ?? '') === 'karsa-menunggu-persetujuan' ? 'active' : '' ?> <?= ($_GET['action'] ?? '') === 'details-permintaan-produk-karsa' ? 'active' : '' ?>" href="produk-masuk.php?action=karsa-menunggu-persetujuan">
        Menunggu Persetujuan
        <span class="badge bg-secondary ms-1 rounded-pill"><?= $total_data ?></span>
    </a>

    <a class="nav-link" href="#">
        Pengembalian Barang
        <span class="badge bg-secondary ms-1 rounded-pill">3</span>
    </a>

    <a class="nav-link" href="#">
        Selesai
        <span class="badge bg-secondary ms-1 rounded-pill">4</span>
    </a>

    <a class="nav-link" href="#">
        Batal
        <span class="badge bg-secondary ms-1 rounded-pill">5</span>
    </a>
</nav>