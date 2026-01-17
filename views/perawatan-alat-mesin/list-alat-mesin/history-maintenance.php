<?php 
    require_once __DIR__ . '/query/data-history-maintenance.php';
?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<!-- Plugin Selectize JS -->
<link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">History Maintenance</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Perawatan Alat & Mesin</li>
                    <li class="breadcrumb-item active" aria-current="page">List Alat & Mesin</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body" style="min-height:70vh">
            <button class="btn btn-primary btn-mobile btnForm" data-bs-toggle="modal" data-bs-target="#modalForm" data-id-alat="<?= isset($_GET['id_alat']) ? htmlspecialchars($_GET['id_alat']) : '' ?>">
                <i class="fe fe-plus-circle me-1"></i>Tambah data perbaikan
            </button>
            <!-- Search & button -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted medium">
                    <a href="perawatan-alat-mesin.php?action=list-alat-mesin" class="btn btn-secondary btn-mobile">
                        <i class="fe fe-arrow-left-circle me-1"></i> Data Alat & Mesin
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
                            <th class="text-center p-3" style="min-width: 40px;">No</th>
                            <th class="text-center p-3" style="min-width: 80px;">Tgl. Maintenance</th>
                            <th class="text-center p-3" style="min-width: 200px;">Jenis Perbaikan</th>
                            <th class="text-center p-3" style="min-width: 200px;">Kategori Perbaikan</th>
                            <th class="text-center p-3" style="min-width: 250px;">Nama Vendor</th>
                            <th class="text-center p-3" style="min-width: 200px;">Petugas Perbaikan</th>
                            <th class="text-center p-3" style="min-width: 280px;">Keterangan</th>
                            <th class="text-center p-3" style="min-width: 150px;">Created Date</th>
                            <th class="text-center p-3" style="min-width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$data_maintenance->isEmpty()) : ?>
                        <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                        <?php foreach ($data_maintenance as $row) : ?>
                            <tr>
                                <td class="align-middle text-center"><?= $no++ ?></td>
                                <td class="align-middle text-center"><?= date('d/m/Y', strtotime($row->tgl_maintenance)) ?></td>
                                <td class="align-middle text-wrap"><?= $row->nama_jenis_perbaikan ?? '-' ?></td>
                                <td class="align-middle text-wrap"><?= $row->nama_kategori ?? '-' ?></td>
                                <td class="align-middle text-wrap"><?= $row->nama_sp ?? '-' ?></td>
                                <td class="align-middle text-wrap text-center">
                                    <div><?= $row->petugas_pelaksana ?? '-' ?></div>
                                    <div>(<?= $row->nama_petugas ?? '-' ?>)</div>
                                </td>
                                <td class="align-middle text-wrap"><?= $row->keterangan ?? '-' ?></td>
                                <td class="align-middle text-center">
                                    <div><?= date('d/m/Y', strtotime($row->created_date)) ?></div>
                                    <div><?= $userMap[$row->created_by] ?? '-' ?></div>
                                </td>
                                <td class="align-middle text-center">
                                    <button class="btn btn-sm btn-warning btnForm" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= encryptId($row->id_history_maintenance, $key_akses) ?>">
                                        <i class="fe fe-edit"></i>
                                    </button>
                                    <?php  
                                        if($_SESSION['role'] == "Super Admin"){
                                            ?>
                                                <button class="btn btn-sm btn-danger btnHapusHistory" data-bs-toggle="modal" data-bs-target="#hapusData" data-id="<?= encryptId($row->id_history_maintenance, $key_akses) ?>">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            <?php
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination['total'] > 0): ?>
                <?php
                    $start = ($pagination['page'] - 1) * $pagination['limit'] + 1;
                    $end   = min($pagination['page'] * $pagination['limit'], $pagination['total']);
                ?>
                <div class="text-muted medium mt-2">
                    Showing <?= $start ?> to <?= $end ?> of <?= $pagination['total'] ?> entries
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <div class="mt-1">
                <?php if ($pagination['pages'] > 1): ?>
                    <?php
                        $visible_pages = 5;
                        $current_page  = $pagination['page'];
                        $total_pages   = $pagination['pages'];

                        $block = floor(($current_page - 1) / $visible_pages);
                        $start_page = $block * $visible_pages + 1;
                        $end_page   = min($start_page + $visible_pages - 1, $total_pages);

                        $base_url = '?action=grade';
                        if (!empty($search)) {
                            $base_url .= '&search=' . urlencode($search);
                        }
                    ?>
                    <nav>
                        <ul class="pagination justify-content-end">
                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $base_url ?>&page=<?= max(1, $current_page - 1) ?>">&laquo;</a>
                            </li>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $base_url ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $base_url ?>&page=<?= min($total_pages, $current_page + 1) ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php ob_end_flush(); ?>
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<?php 
    require_once __DIR__ . '/modal-dialog/form-maintenance.php'; 
    require_once __DIR__ . '/modal-dialog/hapus-maintenance.php';
?>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<script>
    $(document).ready(function () {
        $(document).on('click', '.updateStatus', function() {
            var id = $(this).data("id");
            var status = $(this).data("status");
            var action = $(this).data("action");

            // console.log(id);
            // console.log(status);

            $.ajax({
                url: "routes/kategori-perbaikan.php", 
                type: "POST",
                data: 
                    { 
                        id: id,
                        status: status,
                        action: action
                    },
                success: function (response) {
                    // console.log("Server response:", response);
                    // Jika response JSON string, parse dulu
                    let res = typeof response === 'string' ? JSON.parse(response) : response;

                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: true,
                            allowOutsideClick: false
                        }).then(() => {
                            location.reload(); // reload halaman
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.message || 'Terjadi kesalahan.',
                        }).then(() => {
                            location.reload(); // reload halaman
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal update status, silahkan refresh browser.',
                    }).then(() => {
                        location.reload(); // reload halaman
                    });
                }
            });
        });
    });
</script>

<!-- DataTables Bootstrap 5 -->
<script src="<?= vendor('dataTables/js/dataTables.js') ?>"></script>
<script src="<?= vendor('dataTables/js/dataTables.bootstrap5.js') ?>"></script>
<script src="<?= vendor('dataTables/js/dataTables.buttons.js') ?>"></script>
<script src="<?= vendor('dataTables/js/buttons.bootstrap5.js') ?>"></script>
<script src="<?= vendor('dataTables/js/jszip.min.js') ?>"></script>
<script src="<?= vendor('dataTables/js/pdfmake.min.js') ?>"></script>
<script src="<?= vendor('dataTables/js/vfs_fonts.js') ?>"></script>
<script src="<?= vendor('dataTables/js/buttons.html5.min.js') ?>"></script>
<script src="<?= vendor('dataTables/js/buttons.print.min.js') ?>"></script>
<script src="<?= vendor('dataTables/js/buttons.colVis.min.js') ?>"></script>
<script>
    $(document).ready(function () {
        new DataTable('#tableSupplier', {
            lengthChange: false,
        });
    });
</script>
<!-- End Datatable Bootstraps 5 -->

