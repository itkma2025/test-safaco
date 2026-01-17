<?php 
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once __DIR__ . '/query/data-alat-mesin.php';
?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Alat dan Mesin</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Alat dan Mesin</li>
                    <li class="breadcrumb-item active" aria-current="page">Data Alat dan Mesin</li>
                </ol>
            </nav>    
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body" style="min-height:70vh">
            <!-- Search & button -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted medium d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary btn-mobile btnAlatMesin" data-bs-toggle="modal" data-bs-target="#modalForm">
                        <i class="fe fe-plus-circle me-1"></i>Tambah data
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
                            <th class="text-center" style="min-width: 30px;">No</th>
                            <th class="text-center" style="min-width: 150px;">Kode Alat</th>
                            <th class="text-center" style="min-width: 240px;">Nama Alat</th>
                            <th class="text-center" style="min-width: 120px;">Jenis Alat</th>
                            <th class="text-center" style="min-width: 120px;">Merk</th>
                            <th class="text-center" style="min-width: 120px;">Tgl. Pembelian</th>
                            <th class="text-center" style="min-width: 240px;">Nama Vendor</th>
                            <th class="text-center" style="min-width: 150px;">Kondisi</th>
                            <th class="text-center" style="min-width: 120px;">Lokasi</th>
                            <th class="text-center" style="min-width: 100px;">Status Alat</th>
                            <th class="text-center" style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data_alat_mesin->isEmpty()) : ?>
                            <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($data_alat_mesin as $row) : ?>
                            <?php
                                $id_alat_mesin  = $row->id_alat_mesin;
                                $status_active  = $row->status_active;
                                $checked_active = $status_active == '1' ? 'checked' : '';
                                $status_expired = $row->status_active;
                                $expired_info   = $status_expired == '0' ? 'Masih Berlaku' : 'Expired';
                                $bgcolor        = $status_expired == '0' ? 'secondary' : 'danger';
                                $icon           = $status_expired == '0' ? 'fe fe-check-circle' : 'fe fe-minus-circle';
                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->kode_barang ?? '-'); ?></td>
                                    <td class="align-middle text-wrap"><?= htmlspecialchars($row->nama_barang ?? '-'); ?></td>
                                    <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->jenis_barang); ?></td>
                                    <td class="align-middle text-center"><?= htmlspecialchars($row->nama_merk ?? '-'); ?></td>
                                    <td class="align-middle text-center"><?= date('d/m/Y', strtotime($row->tgl_pembelian ?? '-')); ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($row->nama_sp ?? '-'); ?></td>
                                    <td class="align-middle text-center"><?= htmlspecialchars($row->kondisi ?? '-'); ?></td>
                                    <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->nama_lokasi ?? '-'); ?></td>
                                    <td class="align-middle text-center">
                                        <div class="form-switch">
                                            <input class="form-check-input updateStatus" type="checkbox" value="" id="checkNativeSwitch" <?= $checked_active ?> data-id="<?= encryptId($id_alat_mesin, $key_akses) ?>" data-status="<?= $status_active ?>" data-action="<?= encryptId('update_status_alat_mesin', $key_akses) ?>" data-routename="alat-mesin" switch>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <!-- Button history -->
                                        <a href="perawatan-alat-mesin.php?action=history-maintenance&id_alat=<?= encryptId($id_alat_mesin, $key_akses) ?>" class="btn btn-sm btn-secondary" title="History Maintenance">
                                            <i class="fe fe-list"></i>
                                        </a>
                                        <!-- Button edit -->
                                        <a href="#" data-id=<?= encryptId($id_alat_mesin, $key_akses) ?>" class="btn btn-sm btn-warning btnAlatMesin" data-bs-toggle="modal" data-bs-target="#modalForm" title="Edit Alat Mesin">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <!-- Button hapus -->
                                        <?php  
                                            if($_SESSION['role'] == "Super Admin"){
                                                ?>
                                                    <button class="btn btn-sm btn-danger btnHapusAlatMesin" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($id_alat_mesin, $key_akses) ?>" title="Hapus Alat & Mesin">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="11" class="text-center text-muted">Data tidak ditemukan.</td></tr>
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
    require_once __DIR__ . '/modal-dialog/form-list-alat-mesin.php';
    require_once __DIR__ . '/modal-dialog/hapus-alat-mesin.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>




