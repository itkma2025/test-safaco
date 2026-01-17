<?php require_once __DIR__ . '/query/data-produk.php'; ?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
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
            <h2 class="mb-1">Data produk Produk</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Produk</li>
                    <li class="breadcrumb-item active" aria-current="page">Data produk Produk</li>
                </ol>
            </nav>
        </div>
        <div class="d-block d-md-flex w-custom my-xl-auto right-content align-items-center flex-wrap">
            <div class="me-2 mb-2 w-custom w-md-auto">
                <div class="dropdown w-custom">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-white w-custom d-inline-flex align-items-center justify-content-center" data-bs-toggle="dropdown">
                        <i class="ti ti-file-export me-1"></i>File Export
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-3">
                        <li>
                            <a href="data-produk.php?action=export-pdf-produk" target='blank' class="dropdown-item rounded-1">
                                <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                            </a>
                        </li>
                        <li>
                            <a href="data-produk.php?action=export-excel-produk" class="dropdown-item rounded-1">
                                <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body" style="min-height:70vh">
            <!-- Search & button -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted medium">
                    <a href="data-produk.php?action=add-produk" class="btn btn-primary btn-mobile">
                        <i class="fe fe-plus-circle me-1"></i>Tambah data produk
                    </a>
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
                            <th class="text-center" style="width: 100px;">Satuan</th>
                            <th class="text-center" style="width: 100px;">Merk</th>
                            <th class="text-center" style="width: 150px;">Stock Total</th>
                            <th class="text-center" style="width: 150px;">Harga</th>
                            <th class="text-center" style="width: 150px;">Status AKL</th>
                            <th class="text-center" style="width: 150px;">Status Produk</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data_produk->isEmpty()) : ?>
                            <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($data_produk as $row) : ?>
                            <?php
                                $id_produk      = $row->id_produk;
                                $status_active  = $row->status_active;
                                $checked_active = $status_active == '1' ? 'checked' : '';
                                $status_expired = $row->status_active;
                                $expired_info   = $status_expired == '0' ? 'Masih Berlaku' : 'Expired';
                                $bgcolor        = $status_expired == '0' ? 'secondary' : 'danger';
                                $icon           = $status_expired == '0' ? 'fe fe-check-circle' : 'fe fe-minus-circle';
                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle text-center"><?= $row->kode_produk; ?></td>
                                    <td class="align-middle"><?= $row->nama_produk; ?></td>
                                    <td class="align-middle text-center"><?= $row->satuan_produk; ?></td>
                                    <td class="align-middle text-center"><?= $row->nama_merk ?? '-'; ?></td>
                                    <td class="align-middle text-end"><?= '0'; ?></td>
                                    <td class="align-middle text-end" ><?= number_format($row->harga, 0,'.', '.' ?? '0'); ?></td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-soft-<?= $bgcolor ?> d-inline-flex align-items-center" style="font-size: 12px !important;">
                                            <i class="<?= $icon ?> me-1"></i><?= $expired_info ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-switch">
                                            <input class="form-check-input updateStatusProduk" type="checkbox" value="" id="checkNativeSwitch" <?= $checked_active ?> data-id="<?= encryptId($id_produk, $key_akses) ?>" data-status="<?= $status_active ?>" data-action="<?= encryptId('update_status_produk', $key_akses) ?>" data-routename="produk" switch>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <!-- Button detail -->
                                        <a href="data-produk.php?action=detail-produk&&id=<?= encryptId($id_produk, $key_akses) ?>" class="btn btn-sm btn-secondary" title="Detail Produk">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <!-- Button edit -->
                                        <a href="data-produk.php?action=edit-produk&&id=<?= encryptId($id_produk, $key_akses) ?>" class="btn btn-sm btn-warning" title="Edit Produk">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <!-- Button hapus -->
                                        <?php  
                                            if($_SESSION['role'] == "Super Admin"){
                                                ?>
                                                    <button class="btn btn-sm btn-danger btnHapusProduk" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($id_produk, $key_akses) ?>" title="Hapus Produk">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php
                                            }
                                        ?>
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
    require_once __DIR__ . '/modal-dialog/hapus-produk.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>
