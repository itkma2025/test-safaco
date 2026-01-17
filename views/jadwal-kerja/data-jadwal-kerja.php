<?php require_once __DIR__ . '/query/data-jadwal-kerja.php' ?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Jadwal Kerja</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Jadwal Kerja</li>
                    <li class="breadcrumb-item active" aria-current="page">Data Jadwal Kerja</li>
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
                            <a href="data-jadwal-kerja.php?action=export-excel-jadwal-kerja" class="dropdown-item rounded-1">
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
                    <button class="btn btn-primary btn-mobile btnJadwalKerja" data-bs-toggle="modal" data-bs-target="#jadwalKerja">
                        <i class="fe fe-plus-circle me-1"></i>Tambah data jadwal kerja
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
                            <th class="text-center" style="min-width: 400px;">Nama Jam Kerja</th>
                            <th class="text-center" style="min-width: 200px;">Jam Mulai</th>
                            <th class="text-center" style="min-width: 200px;">Jam Akhir</th>
                            <th class="text-center" style="min-width: 200px;">Tipe Jam Kerja</th>
                            <th class="text-center" style="min-width: 200px;">Created Date</th>
                            <th class="text-center" style="min-width: 200px;">Status</th>
                            <th class="text-center" style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data_jam_kerja->isEmpty()) : ?>
                            <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($data_jam_kerja as $row) : ?>
                            <?php
                                $id_jadwal_kerja = $row->id_jadwal_kerja;
                                $status_active = $row->status_active;
                                $checked_active = $status_active == '1' ? 'checked' : '';
                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($row->nama_jam_kerja) ?></td>
                                    <td class="align-middle text-center"><?= date('H:i', strtotime($row->jam_mulai)) ?></td>
                                    <td class="align-middle text-center"><?= date('H:i', strtotime($row->jam_akhir)) ?></td>
                                    <td class="align-middle text-center"><?= htmlspecialchars($row->tipe_jam_kerja) ?></td>
                                    <td class="align-middle text-center">
                                        <div>
                                           <?= date('d/m/Y H:i:s', strtotime($row->created_date)) ?>
                                        </div>
                                        <div>
                                            <?= $userMap[$row->created_by] ?? '-' ?>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-switch">
                                            <input class="form-check-input updateStatus" type="checkbox" value="" id="checkNativeSwitch" <?= $checked_active ?> data-id="<?= encryptId($id_jadwal_kerja, $key_akses) ?>" data-status="<?= $status_active ?>" data-action="<?= encryptId('update_status_jadwal_kerja', $key_akses) ?>" data-routename="jadwal-kerja" switch>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-warning btnJadwalKerja" data-bs-toggle="modal" data-bs-target="#jadwalKerja" data-id="<?= encryptId($id_jadwal_kerja, $key_akses) ?>">
                                            <i class="fe fe-edit"></i>
                                        </button>
                                        <?php  
                                            if($_SESSION['role'] == "Super Admin"){
                                                ?>
                                                    <button class="btn btn-sm btn-danger btnHapusJadwalKerja" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($id_jadwal_kerja, $key_akses) ?>">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                <?php
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="8" class="text-center text-muted">Data tidak ditemukan.</td></tr>
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
    require_once __DIR__ . '/modal-dialog/form-jadwal-kerja.php'; 
    require_once __DIR__ . '/modal-dialog/hapus-jadwal-kerja.php';
?>

<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>

