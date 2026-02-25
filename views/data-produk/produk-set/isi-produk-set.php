<?php require_once __DIR__ . '/query/data-isi-produk-set.php'; ?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<style>
    .detail-set {
        display: grid;
        grid-template-columns: 220px 1fr;
        row-gap: 8px;
    }

    .detail-set dt {
        font-weight: 600;
        position: relative;
    }

    .detail-set dd::before {
        content: ": ";
    }

    .detail-set dd {
        margin: 0;
    }

    /* =======================
    MOBILE VIEW
    ======================= */
    @media (max-width: 576px) {
        .detail-set {
            grid-template-columns: 1fr;
            row-gap: 4px;
        }

        .detail-set dt {
            font-size: 14px;
            color: #666;
        }

        .detail-set dd {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        /* Hilangkan ':' di mobile */
        .detail-set dt::after {
            content: " :";
        }

        .detail-set dd::before {
            content: "";
        }
    }
</style>
<div class="content">
    <!-- Alert -->
    <?php 
        require_once __DIR__ . '/../../../config/config.php'; 
        require_once base_path('public/function-php/sanitasi-input.php');
        $error = isset($_GET['error']) ? $sanitasi_input->purify($_GET['error']) : '';
        if($error === 'id'){
            ?>
                <script>
                   Swal.fire({
                        icon: "error",
                        title: "Error 404",
                        text: "ID tidak ditemukan",
                        allowOutsideClick: false
                    }).then(() => {
                        // Ambil URL saat ini
                        let url = new URL(window.location.href);

                        // Hapus parameter 'error'
                        url.searchParams.delete('error');

                        // Update URL di address bar tanpa reload
                        window.history.replaceState({}, document.title, url.href);
                    });
                </script>
            <?php
        }  
    ?>
    <!-- End Alert -->
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Isi Produk Set</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Isi Produk Set</li>
                    <li class="breadcrumb-item active" aria-current="page">Data Produk Set</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <?php  
            $queryProduk = $db_safaco->table('produk_set as ps')
                            ->leftJoin('produk_lokasi as pl', 'ps.id_lokasi', '=', 'pl.id_lokasi')
                            ->where('ps.id_produk_set', '=', decryptId($_GET['id'], $key_akses))
                            ->first();
        
        ?>
        <div class="card-body">
            <dl class="detail-set">
                <dt>Kode Set</dt>
                <dd><?= $queryProduk->kode_produk_set ?></dd>

                <dt>Nama Set</dt>
                <dd><?= $queryProduk->nama_produk_set ?></dd>

                <dt>Harga (Rp)</dt>
                <dd><?= number_format($queryProduk->harga, 0, ',', '.') ?></dd>

                <dt>Lokasi</dt>
                <dd><?= $queryProduk->nama_lokasi ?> / <?= $queryProduk->lantai ?> / <?= $queryProduk->area ?> / <?= $queryProduk->no_rak ?></dd>
            </dl>
        </div>
        <div class="card-body" style="min-height:70vh">
            <!-- Search & button -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted medium">
                    <button class="btn btn-primary btn-mobile btnIsiSet" data-bs-toggle="modal" data-bs-target="#formSet" data-idset="<?= $_GET['id'] ?>" data-action="create-isi" title="Tambah Data">
                        <i class="fe fe-plus-circle me-1"></i>Tambah data produk
                    </button>
                </div>
                <div class="card w-auto mt-3">
                    <div class="input-container position-relative">
                        <div class="input-group">
                            <input type="text" class="form-control pe-5" placeholder="Cari Data" id="cari-data" value="<?= htmlspecialchars($search) ?>" aria-label="Cari Data" style="border-right: none !important;">
                            
                            <!-- Tombol X (hapus) -->
                            <div class="input-group-text" id="btn-clear" style="border-left: none !important; cursor: pointer; display: none;">
                                <i class="fe fe-x"></i>
                            </div>

                            <!-- Tombol Search -->
                            <div class="input-group-text" id="btn-search" style="border-left: none !important;">
                                <i class="fe fe-search"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th class="text-center" style="width: 200px;">Kode Produk</th>
                            <th class="text-center" style="width: 400px;">Nama Produk</th>
                            <th class="text-center" style="width: 100px;">Merk</th>
                            <th class="text-center" style="width: 150px;">No. AKL</th>
                            <th class="text-center" style="width: 150px;">Qty</th>
                            <th class="text-center" style="width: 150px;">Harga Satuan (Rp)</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data_produk->isEmpty()) : ?>
                            <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($data_produk as $row) : ?>
                            <?php
                                $id_isi_produk_set  = $row->id_isi_produk_set;
                                $id_produk_set      = $row->id_produk_set;
                                $jenis_nie          = $row->jenis_nie;
                                $label_nie          = ($jenis_nie === 'Lokal') ? 'AKD' : 'AKL';
                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle text-center"><?= $row->kode_produk; ?></td>
                                    <td class="align-middle"><?= $row->nama_produk; ?></td>
                                    <td class="align-middle text-center"><?= $row->nama_merk ?? '-'; ?></td>
                                    <td class="align-middle text-end"><?=  $label_nie . ' ' . $row->no_izin_edar ?? '-'; ?></td>
                                    <td class="align-middle text-end" ><?= $row->qty ?? '-'; ?></td>
                                    <td class="align-middle text-end" ><?= number_format($row->harga, 0,'.', '.' ?? '0'); ?></td>
                                    <td class="align-middle text-center">
                                        <!-- Button edit -->
                                        <button class="btn btn-sm btn-warning btnIsiSet" data-bs-toggle="modal" data-bs-target="#formSet" data-id="<?= encryptId($id_isi_produk_set, $key_akses) ?>" data-idset="<?= $_GET['id'] ?>" data-action="edit-isi" title="Edit Data">
                                            <i class="fe fe-edit"></i></i>
                                        </button>
                                        
                                        <!-- Button hapus -->
                                        <button class="btn btn-sm btn-danger btnHapusProduk" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($id_isi_produk_set, $key_akses) ?>" title="Hapus Produk">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="10" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Untuk menampilkan pagination -->
            <?php require_once base_path('public/function-php/pagination.php'); ?>
        </div>
    </div>
</div>
<?php ob_end_flush(); ?>
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<?php 
    require_once __DIR__ . '/modal-dialog/form-isi-produk-set.php'; 
    require_once __DIR__ . '/modal-dialog/hapus-isi-produk-set.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>
