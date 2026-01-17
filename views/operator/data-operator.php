<?php require_once __DIR__ . '/query/data-operator.php' ?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Operator</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Operator</li>
                    <li class="breadcrumb-item active" aria-current="page">Data Operator</li>
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
                    <a href="#" class="btn btn-outline-secondary btn-mobile active">
                        <i class="fe fe-user me-1"></i>Operator
                    </a>
                    <a href="data-operator.php?action=keahlian" class="btn btn-outline-secondary btn-mobile">
                        <i class="fe fe-user me-1"></i>Keahlian 
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
                            <th class="text-center" style="min-width: 40px;">No</th>
                            <th class="text-center" style="min-width: 250px;">Nama Operator</th>
                            <th class="text-center" style="min-width: 200px;">Role</th>
                            <th class="text-center" style="min-width: 200px;">No. HP</th>
                            <th class="text-center" style="min-width: 200px;">Keahlian</th>
                            <th class="text-center" style="min-width: 300px;">Alat / Mesin</th>
                            <th class="text-center" style="min-width: 200px;">Status</th>
                            <th class="text-center" style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data_user->isEmpty()): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_user as $row): ?>
                                <?php 
                                    $id_user        = $row->id_user;
                                    $id_operator    = $row->id_operator ?? null;
                                    $status_active  = $row->status_active;
                                    $checked_active = $status_active == '1' ? 'checked' : ''; 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row->nama_user) ?></td>  
                                    <td class="text-center"><?= htmlspecialchars($row->nama_role ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row->no_hp ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row->nama_keahlian ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row->nama_barang ?? 'Tidak ada Alat / Mesin terpilih') ?></td>
                                    <td class="text-center">
                                        <?php 
                                            if($id_operator == null){
                                                echo '-';
                                            } else {
                                                ?>
                                                    <div class="form-switch">
                                                        <input class="form-check-input updateStatus" type="checkbox" id="checkNativeSwitch" <?= $checked_active ?> data-id="<?= encryptId($id_operator, $key_akses) ?>" data-status="<?= $status_active ?>" data-action="<?= encryptId('update_status_operator', $key_akses) ?>" data-routename="operator" switch>
                                                    </div>
                                                <?php
                                            }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning btnOperator" data-bs-toggle="modal" data-bs-target="#operator" data-id="<?= encryptId($id_user, $key_akses) ?>">
                                            <i class="fe fe-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data</td>
                            </tr>
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
    require_once __DIR__ . '/modal-dialog/form-operator.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>

