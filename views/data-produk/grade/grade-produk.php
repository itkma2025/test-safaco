<?php require_once __DIR__ . '/query/data-grade-produk.php' ?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Grade Produk</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Produk</li>
                    <li class="breadcrumb-item active" aria-current="page">Data Grade Produk</li>
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
                <div class="text-muted medium">
                    <button class="btn btn-primary btn-mobile btnAddGrade" data-bs-toggle="modal" data-bs-target="#addData" data-id="add">
                        <i class="fe fe-plus-circle me-1"></i>Tambah data grade
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
                            <th class="text-center" style="min-width: 40px;">No</th>
                            <th class="text-center" style="min-width: 400px;">Grade Produk</th>
                            <th class="text-center" style="min-width: 200px;">Created Date</th>
                            <th class="text-center" style="min-width: 200px;">Updated Date</th>
                            <th class="text-center" style="min-width: 200px;">Status</th>
                            <th class="text-center" style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data_grade->isEmpty()) : ?>
                            <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($data_grade as $row) : ?>
                            <?php
                                $id_grade = $row->id_grade_produk;
                                $status_active = $row->status_active;
                                $checked_active = $status_active == '1' ? 'checked' : '';
                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($row->grade) ?></td>
                                    <td class="align-middle text-center">
                                        <div>
                                           <?= date('d/m/Y H:i:s', strtotime($row->created_date)) ?>
                                        </div>
                                        <div>
                                            <?= $userMap[$row->created_by] ?? '-' ?>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div>
                                            <?= !empty($row->updated_date) ? date('d/m/Y H:i:s', strtotime($row->updated_date)) : '' ?>
                                        </div>
                                        <div>
                                            <?= $userMap[$row->updated_by] ?? '-' ?>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-switch">
                                            <input class="form-check-input updateStatus" type="checkbox" value="" id="checkNativeSwitch" <?= $checked_active ?> data-id="<?= encryptId($id_grade, $key_akses) ?>" data-status="<?= $status_active ?>" data-action="<?= encryptId('update_status_grade', $key_akses) ?>" data-routename="grade" switch>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-warning btnEditGrade" data-bs-toggle="modal" data-bs-target="#editData" data-id="<?= encryptId($id_grade, $key_akses) ?>">
                                            <i class="fe fe-edit"></i>
                                        </button>
                                        <?php  
                                            if($_SESSION['role'] == "Super Admin"){
                                                ?>
                                                    <button class="btn btn-sm btn-danger btnHapusGrade" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($id_grade, $key_akses) ?>">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="7" class="text-center text-muted">Data tidak ditemukan.</td></tr>
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
    require_once __DIR__ . '/modal-dialog/tambah-grade.php'; 
    require_once __DIR__ . '/modal-dialog/edit-grade.php';
    require_once __DIR__ . '/modal-dialog/hapus-grade.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>

