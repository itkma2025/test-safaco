<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<!-- Selectize -->
<link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />

<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Jenis Permintaan</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Jenis Permintaan</li>
                    <li class="breadcrumb-item active" aria-current="page">Jenis Permintaan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body">
            <?php  
                require_once __DIR__ . '/navbar.php';
            ?>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-solid mb-3">
                <li class="nav-item">
                    <a class="nav-link active" href="#pjtSafaco" data-bs-toggle="tab">
                        Persetujuan PJT Safaco
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#mr" data-bs-toggle="tab">
                        Persetujuan MR
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body" style="min-height:70vh">
            <div class="tab-content">
                <div class="tab-pane show active" id="pjtSafaco">
                    <div class="d-flex justify-content-center mb-4">
                        <div class="card border text-center p-1 mb-3" style="width: 420px;">
                            <h4 class="mb-0">Total Data Persetujuan PJT Safaco</h4>
                            <p class="fw-bold" style="font-size: 18px;"><?= $total_data_pjt_skm ?></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="tablePjtSafaco" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:30px;">No</th>
                                    <th class="text-center" style="width:120px;">No Permintaan</th>
                                    <th class="text-center" style="width:300px;">Tgl. Permintaan</th>
                                    <th class="text-center" style="width:350px;">Jenis Permintaan</th>
                                    <th class="text-center" style="width:350px;">Jumlah Produk</th>
                                    <th class="text-center" style="width:350px;">Petugas Permintaan</th>
                                    <th class="text-center" style="width:350px;">Created Date</th>
                                    <th class="text-center" style="width:100px;">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane" id="mr">
                    <div class="d-flex justify-content-center mb-4">
                        <div class="card border text-center p-1 mb-3" style="width: 420px;">
                            <h4 class="mb-0">Total Data Persetujuan MR</h4>
                            <p class="fw-bold" style="font-size: 18px;"><?= $total_data_mr ?></p>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered" id="tableMr" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">No Permintaan</th>
                                <th class="text-center" style="width:300px;">Tgl. Permintaan</th>
                                <th class="text-center" style="width:350px;">Jenis Permintaan</th>
                                <th class="text-center" style="width:350px;">Jumlah Produk</th>
                                <th class="text-center" style="width:350px;">Petugas Permintaan</th>
                                <th class="text-center" style="width:350px;">Created Date</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ob_end_flush(); ?>

<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

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
        new DataTable('#tablePjtSafaco', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=persetujuan-pjt-safaco',
                type: 'POST',
            },
            drawCallback: function () {
                cekProdukTerpilih();
            }
        });
    });

     $(document).ready(function () {
        new DataTable('#tableMr', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=persetujuan-mr',
                type: 'POST',
            },
            drawCallback: function () {
                cekProdukTerpilih();
            }
        });
    });
</script>

