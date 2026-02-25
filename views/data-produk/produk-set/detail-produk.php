<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../../config/config.php';
    require_once base_path('helpers/domain.php');
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/uuid.php');
    require_once base_path('public/function-php/csrf-token.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once base_path('public/function-php/sanitasi-input.php');
    require_once __DIR__ . "/query/kategori-penjualan.php";
    require_once __DIR__ . "/query/grade-produk.php";

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }

    // Get domain
    $domain_sso = DOMAIN_SSO;
    
    if(isset($_GET['id'])){
        $action = 'edit';
        $id_produk_decrypt  = $sanitasi_input->purify(decryptId($_GET['id'], $key_akses));

        // Kondisi jika decrypt gagal atau user sengaja menghapus sebagian ID
        if (!$id_produk_decrypt) {
            ?>
                <script>
                    window.location.replace('data-produk.php?action=produk&&error=id')
                </script>
            <?php
            exit;
        }

        require_once __DIR__ . '/query/detail-produk.php';
        // Kondisi untuk penanganan jika data tidak di temukan
        if (!$data_produk) {
            ?>
                <script>
                    window.location.replace('data-produk.php?action=produk&&error=id')
                </script>
            <?php
            exit;
        }
        $id_produk_master           = $data_produk->id_produk_master;
        $nama_produk_master         = $data_produk->nama_produk_master;
        $kode_produk_set            = $data_produk->kode_produk_set;
        $kode_katalog               = $data_produk->kode_katalog;
        $nama_produk_set            = $data_produk->nama_produk_set;
        $id_kategori_produk         = $data_produk->id_kategori_produk;
        $nama_kategori_produk       = $data_produk->nama_kategori_produk;
        $nama_merk                  = $data_produk->nama_merk;
        $harga                      = $data_produk->harga ?? 0;
        $grade                      = $data_produk->grade;
        $nie                        = $data_produk->no_izin_edar;
        $kategori_penjualan         = $data_produk->kategori_penjualan;
        $nama_lokasi                = $data_produk->nama_lokasi;
        $lantai                     = $data_produk->lantai;
        $area                       = $data_produk->area;
        $no_rak                     = $data_produk->no_rak;
        $deskripsi_produk           = $data_produk->deskripsi_produk;
        $filename                   = $data_produk->filename;
        $status_active              = $data_produk->status_active;
        $label_status               = '';
        $bg_color       = '';
        if($status_active == '1'){
            $label_status   = 'Aktif';
            $bg_color       = 'btn btn-secondary';
        } else {
            $label_status = 'Non Aktif';
            $bg_color       = 'btn btn-primary';
        }
    } else {
        ?>
            <script>
                window.location.replace('data-produk.php?action=produk&&error=id')
            </script>
        <?php
        exit;
    }
?>
<!-- FancyBox CSS -->
<link rel="stylesheet" href="<?= vendor('fancybox/fancybox.css') ?>">
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<style>
    .detail-produk dt {
        display: flex;
        justify-content: space-between; /* label di kiri, titik dua di kanan */
        position: relative;
    }

    .detail-produk dt::after {
        content: ":";
        margin-left: 5px;
    }

    @media (max-width: 576px) {
        .detail-produk .row dt,
        .detail-produk .row dd {
            flex: 1 1 100%;
        }

        /* Hilangkan titik dua di mobile */
        .detail-produk dt::after {
            content: ""; /* kosongkan titik dua */
            margin-left: 0; /* hapus jarak yang tadi */
        }
    }
</style>
<div class="content" style="min-height:90vh">
    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-12 col-sm-4">
                    <!-- Content kosong atau info lain -->
                </div>
                <div class="col-12 col-sm-4 text-center">
                    <h4>Detail Data Produk</h4>
                </div>
                <div class="col-12 col-sm-4 text-sm-end text-center mt-2 mt-sm-0">
                    <button type="button" class="<?= $bg_color ?> btn-md"> 
                        <?= $label_status ?> 
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="d-flex justify-content-center">
                        <div class="card p-3 text-center" style="max-width: 200px">
                            <div class="fw-bold">Total Stock Barang</div>
                            <div class="mt-2">1.000</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="card text-center">
                            <a href="view-img.php?id=<?= encryptId($id_produk_decrypt, $key_akses); ?>" data-fancybox="preview" class="file-name">
                                <img src="view-img.php?id=<?= encryptId($id_produk_decrypt, $key_akses); ?>" alt="preview" class="img-fluid" style="max-height:400px;">
                            </a>
                        </div>
                    </div>
                    <!-- Border -->
                    <div class="border-bottom border-3"></div>
                    <!-- End Border -->
                    <div class="col-md-12 mt-3">
                        <dl class="row mb-0 detail-produk">
                            <div class="row mb-1">
                                <dt class="col-sm-4">Created By</dt>
                                <dd class="col-sm-8">
                                    <?= $userMap[$data_produk->created_by] . ' (' . date('d/m/Y H:i:s', strtotime($data_produk->created_date)) . ')' ?>
                                </dd>
                            </div>
                            <div class="row mb-1">
                                <dt class="col-sm-4">Updated By</dt>
                                <dd class="col-sm-8">
                                    <?= 
                                        ($userMap[$data_produk->updated_by] ?? '-') .
                                        (
                                            !empty($data_produk->updated_date)
                                            ? ' (' . date('d/m/Y H:i:s', strtotime($data_produk->updated_date)) . ')'
                                            : ''
                                        )
                                    ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="d-flex justify-content-start flex-wrap gap-2">
                        <a href="#" class="btn btn-secondary btn-mobile">
                            <i class="fe fe-plus-circle me-1"></i>Lihat Kartu Stock
                        </a>
                        <a href="data-produk.php?action=edit-produk-set&&id=<?= encryptId($id_produk_decrypt, $key_akses) ?>" class="btn btn-secondary btn-mobile">
                            <i class="fe fe-edit me-1"></i>Edit Data Produk
                        </a>
                    </div>
                    <div class="col-md-12 mt-3">
                        <?php if($grade): ?>
                            <span class="badge badge-soft-secondary d-inline-flex align-items-center p-2" style="font-size: 15px !important;">
                                <?= $grade ?>
                            </span>
                        <?php endif; ?>

                        <?php if($nama_kategori_produk): ?>
                            <span class="badge badge-soft-primary d-inline-flex align-items-center p-2" style="font-size: 15px !important;">
                                <?= $nama_kategori_produk ?>
                            </span>
                        <?php endif; ?>

                        <?php if($kode_produk_set): ?>
                        <span class="badge badge-soft-info d-inline-flex align-items-center p-2" style="font-size: 15px !important;">
                            <?= $kode_produk_set ?>
                        </span>
                        <?php endif; ?>
                        <br><br>

                        <?php if($nama_merk): ?>
                        <span class="badge badge-soft-secondary d-inline-flex align-items-center p-2" style="font-size: 15px !important;">
                            <?= $nama_merk ?>
                        </span>
                        <?php endif; ?>

                        <?php if($nama_produk_set): ?>
                        <span class="badge badge-soft-primary d-inline-flex align-items-center p-2" style="font-size: 15px !important;">
                            <?= $nama_produk_set ?>
                        </span>
                        <?php endif; ?>

                        <?php if($nie): ?>
                            <span class="badge badge-soft-info d-inline-flex align-items-center p-2" style="font-size: 15px !important;">
                                <?= $nie ?>
                            </span>
                        <?php endif; ?>
                        <br><br>
                       
                        <span class="fw-bold">Kode Katalog : <?= $kode_katalog ?? '-' ?></span><br><br>
                        <span class="fw-bold">Harga Produk : <?= number_format($harga ?? 0, 0,'.','.') ?></span><br><br>
                        <span class="fw-bold">Deskripsi Produk : </span><br><br>
                        <div class="card border-2 mb-2 p-2" style="min-height: 100px;">
                            <?= $deskripsi_produk ?? '' ?>
                        </div>
                        <span class="fw-bold">Lokasi Produk : </span><br>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Lokasi</th>
                                    <th class="text-center">Lantai</th>
                                    <th class="text-center">Area</th>
                                    <th class="text-center">No. Rak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><?= $nama_lokasi ?></td>
                                    <td class="text-center"><?= $lantai ?></td>
                                    <td class="text-center"><?= $area ?></td>
                                    <td class="text-center"><?= $no_rak ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fancybox -->
<script src="<?= vendor('fancybox/fancybox.umd.js') ?>"></script>