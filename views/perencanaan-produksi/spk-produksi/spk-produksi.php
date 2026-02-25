<?php 
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once __DIR__ . '/query/data-spk-produksi.php';
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
            <h2 class="mb-1">Data SPK Produksi</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data SPK Produksi</li>
                    <li class="breadcrumb-item active" aria-current="page">Data SPK Produksi</li>
                </ol>
            </nav>    
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body">
            <nav class="nav nav-style-6 nav-pills mb-3" role="tablist">
                <a class="nav-link <?= $activeDraft ?>" href="perencanaan-produksi.php?action=spk-produksi&status=draft">
                    Draft
                    <span class="badge bg-secondary ms-1 rounded-pill"><?= $badge->draft ?></span>
                </a>

                <a class="nav-link <?= $activeBelumDimulai ?>" href="perencanaan-produksi.php?action=spk-produksi&status=belum-dimulai">
                    Belum Dimulai
                    <span class="badge bg-secondary ms-1 rounded-pill"><?= $badge->belum_dimulai ?></span>
                </a>

                <a class="nav-link <?= $activeSudahDimulai ?>" href="perencanaan-produksi.php?action=spk-produksi&status=sudah-dimulai">
                    Sudah Dimulai
                    <span class="badge bg-secondary ms-1 rounded-pill"><?= $badge->sudah_dimulai ?></span>
                </a>

                <a class="nav-link <?= $activeSudahSelesai ?>" href="perencanaan-produksi.php?action=spk-produksi&status=sudah-selesai">
                    Sudah Selesai
                    <span class="badge bg-secondary ms-1 rounded-pill"><?= $badge->sudah_selesai ?></span>
                </a>

                <a class="nav-link <?= $activeBatal ?>" href="perencanaan-produksi.php?action=spk-produksi&status=batal">
                    Batal
                    <span class="badge bg-secondary ms-1 rounded-pill"><?= $badge->batal ?></span>
                </a>
            </nav>
            <div style="min-height:70vh">
                <!-- Search & button -->
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="text-muted medium d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary btn-mobile btnForm" data-bs-toggle="modal" data-bs-target="#modalForm">
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
                                <th class="text-center" style="min-width: 150px;">No SPK</th>
                                <th class="text-center" style="min-width: 240px;">Nama SPK</th>
                                <th class="text-center" style="min-width: 100px;">Tgl SPK</th>
                                <th class="text-center" style="min-width: 120px;">Jenis Pengerjaan</th>
                                <th class="text-center" style="min-width: 120px;">Tgl. Mulai</th>
                                <th class="text-center" style="min-width: 120px;">Tgl. Akhir</th>
                                <th class="text-center" style="min-width: 150px;">Catatan</th>
                                <th class="text-center" style="min-width: 120px;">Jenis Produksi</th>
                                <th class="text-center" style="min-width: 120px;">Prioritas Produksi</th>
                                <th class="text-center" style="min-width: 100px;">Status Produksi</th>
                                <th class="text-center" style="min-width: 100px;">Created By</th>
                                <th class="text-center" style="min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $status = htmlspecialchars($_GET['status'] ?? 'draft'); ?>

                            <?php if (!$data_spk->isEmpty()) : ?>
                                <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                                <?php foreach ($data_spk as $row) : ?>
                                <?php
                                    $id_spk_produksi  = $row->id_spk_produksi;
                                ?>
                                    <tr>
                                        <td class="align-middle text-center"><?= $no++ ?></td>
                                        <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->no_spk ?? '-'); ?></td>
                                        <td class="align-middle text-wrap"><?= htmlspecialchars($row->nama_spk ?? '-'); ?></td>
                                        <td class="align-middle text-center text-wrap"><?= date('d/m/Y', strtotime($row->tgl_spk ?? '-')); ?></td>
                                        <td class="align-middle text-center"><?= htmlspecialchars($row->nama_jenis_pengerjaan ?? '-'); ?></td>
                                        <td class="align-middle text-center"><?= date('d/m/Y', strtotime($row->tgl_mulai ?? '-')); ?></td>
                                        <td class="align-middle text-center"><?= date('d/m/Y', strtotime($row->tgl_akhir ?? '-')); ?></td>
                                        <td class="align-middle text-center"><?= htmlspecialchars($row->catatan ?? '-'); ?></td>
                                        <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->nama_jenis_produksi ?? '-'); ?></td>
                                        <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->prioritas_produksi ?? '-'); ?></td>
                                        <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row->status_spk ?? '-'); ?></td>
                                        <td class="align-middle text-center text-wrap"><?= date('d/m/Y H:i:s', strtotime($row->created_date ?? '-')); ?></td>
                                        <td class="align-middle text-center">
                                            <!-- Button history -->
                                            <a href="perencanaan-produksi.php?action=detail&status=<?= $status ?>&id=<?= encryptId($id_spk_produksi, $key_akses) ?>" class="btn btn-sm btn-secondary" title="Details">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <!-- Button edit -->
                                            <a href="#" data-id="<?= encryptId($id_spk_produksi, $key_akses) ?>" class="btn btn-sm btn-warning btnAlatMesin btnForm" data-bs-toggle="modal" data-bs-target="#modalForm" title="Edit SPK Produksi">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <!-- Button hapus -->
                                            <?php  
                                                if($_SESSION['role'] == "Super Admin"){
                                                    ?>
                                                        <button class="btn btn-sm btn-danger btnHapusSpkProduksi" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($id_spk_produksi, $key_akses) ?>" title="Hapus SPK Produksi">
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
        <div>
    </div>
</div>
<?php ob_end_flush(); ?>
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<?php 
    require_once __DIR__ . '/modal-dialog/form-spk-produksi.php';
    // require_once __DIR__ . '/modal-dialog/hapus-alat-mesin.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>




