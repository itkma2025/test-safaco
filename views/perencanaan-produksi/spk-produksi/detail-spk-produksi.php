<?php 
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once __DIR__ . '/query/detail-spk-produksi.php';
?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>

<!-- FancyBox CSS -->
<link rel="stylesheet" href="<?= vendor('fancybox/fancybox.css') ?>">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    #imgWrapper img {
        max-width: 150px;
        margin: 5px;
        cursor: pointer;
        border-radius: 5px;
        transition: transform 0.2s;
    }
    #imgWrapper img:hover {
        transform: scale(1.05);
    }

    /* Atur ukuran maksimal untuk Fancybox */
    .fancybox__container {
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important; /* Fancybox selalu di depan */
    }

    .img-preview {
        height: 300px !important; /* Sesuaikan tinggi sesuai kebutuhan */
        width: 300px !important; /* Sesuaikan lebar sesuai kebutuhan */
    }
</style>
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
        <div class="row align-items-center text-center text-md-start p-3">
            <!-- Status -->
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <button class="btn btn-success btn-mobile w-md-auto">
                    <?= $data_spk->status_spk ?>
                </button>
            </div>

            <!-- Title -->
            <div class="col-12 col-md-4 mb-2 mb-md-0 text-center">
                <h5 class="mb-0">Details Data SPK Produksi</h5>
            </div>

            <!-- Jenis & Prioritas -->
            <div class="col-12 col-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2">
                    <button class="btn btn-success px-4">
                        <?= $data_spk->nama_jenis_produksi ?>
                    </button>

                    <button class="btn btn-success px-4">
                        <?= $data_spk->prioritas_produksi ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="row p-3">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label class="form-label col-md-3">No SPK</label>
                    <div class="col-md-9">
                        <input type="text"  class="form-control" value="<?= $data_spk->no_spk ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                     <label class="form-label col-md-3">Nama SPK</label>
                    <div class="col-md-9">
                        <input type="text"  class="form-control" value="<?= $data_spk->nama_spk ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="form-label col-md-3">Tgl SPK</label>
                    <div class="col-md-9">
                        <input type="text"  class="form-control" value="<?= date('d-m-Y', strtotime($data_spk->tgl_spk)) ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                     <label class="form-label col-md-3">Tgl Mulai Produksi</label>
                    <div class="col-md-9">
                        <input type="text"  class="form-control" value="<?= date('d-m-Y H:i:s', strtotime($data_spk->tgl_mulai)) ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                     <label class="form-label col-md-3">Tgl Akhir Produksi</label>
                    <div class="col-md-9">
                        <input type="text"  class="form-control" value="<?= date('d-m-Y H:i:s', strtotime($data_spk->tgl_akhir)) ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                     <label class="form-label col-md-3">Catatan Produksi</label>
                    <div class="col-md-9">
                        <textarea type="text"  class="form-control" readonly><?= $data_spk->catatan ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="card-body">
            <div style="min-height:70vh">
                <!-- Search & button -->
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="text-muted medium d-flex gap-2 flex-wrap">
                        <a href="perencanaan-produksi.php?action=spk-produksi&status=<?= htmlspecialchars($_GET['status']) ?>" class="btn btn-info btn-mobile">
                            <i class="fe fe-plus-circle me-1"></i>Halaman Sebelumnya
                        </a>
                        <button class="btn btn-primary btn-mobile btnForm" data-bs-toggle="modal" data-bs-target="#produk">
                            <i class="fe fe-plus-circle me-1"></i>Tambah data barang
                        </button>
                        <button class="btn btn-secondary btn-mobile btnProses" 
                            data-id-spk="<?= htmlspecialchars($_GET['id']); ?>" 
                            data-no-spk="<?= $data_spk->no_spk; ?>"
                            data-referer="<?= $_GET['status']; ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalProses"
                        >
                            <i class="fe fe-refresh-ccw me-1"></i>Proses Produksi
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
                                <th class="text-center" style="min-width: 150px;">Kode Produk</th>
                                <th class="text-center" style="min-width: 240px;">Nama Produk</th>
                                <th class="text-center" style="min-width: 100px;">Kategori Produk</th>
                                <th class="text-center" style="min-width: 120px;">NIE</th>
                                <th class="text-center" style="min-width: 120px;">Current Qty</th>
                                <th class="text-center" style="min-width: 120px;">Qty Plan</th>
                                <th class="text-center" style="min-width: 150px;">Keterangan</th>
                                <th class="text-center" style="min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$data_produk->isEmpty()) : ?>
                                <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                                <?php foreach ($data_produk as $row) : ?>
                                <?php
                                    $id_details_produksi    = $row->id_details_produksi;
                                    $kode_produk            = $row->kode_produk;
                                    $nama_produk            = $row->nama_produk;
                                    $nama_kategori          = $row->nama_kategori;
                                    $no_izin_edar           = $row->no_izin_edar;
                                    $qty_plan               = $row->qty_plan;
                                    $keterangan             = $row->keterangan;

                                ?>
                                    <tr>
                                        <td class="align-middle text-center"><?= $no++; ?></td>
                                        <td class="align-middle text-center text-wrap"><?= $kode_produk; ?></td>
                                        <td class="align-middle text-wrap"><?= $nama_produk; ?></td>
                                        <td class="align-middle text-center text-wrap"><?= $nama_kategori; ?></td>
                                        <td class="align-middle text-center"><?= $no_izin_edar; ?></td>
                                        <td class="align-middle text-end"></td>
                                        <td class="align-middle text-end"><?= $qty_plan; ?></td>
                                        <td class="align-middle text-center"><?= $keterangan; ?></td>
                                        <td class="align-middle text-center">
                                            <!-- Button history -->
                                            <button class="btn btn-sm btn-warning btnEdit" data-bs-toggle="modal" data-bs-target="#editData" data-id-details="<?= encryptId($id_details_produksi, $key_akses) ?>" data-id-spk="<?= htmlspecialchars($_GET['id']); ?>" data-kode-produk="<?= $kode_produk; ?>" data-nama-produk="<?= $nama_produk; ?>" data-qty-plan="<?= $qty_plan; ?>" data-referer="<?= $_GET['status']; ?>" title="Edit Produk">
                                                <i class="fe fe-edit"></i>
                                            </button>
                                            <!-- Button edit -->
                                            <a href="#" class="btn btn-sm btn-secondary btnForm" title="Input Actual">
                                                <i class="fe fe-clipboard"></i>
                                            </a>
                                            <!-- Button hapus -->
                                            <?php  
                                                if($_SESSION['role'] == "Super Admin"){
                                                    ?>
                                                        <button class="btn btn-sm btn-danger btnHapus" data-bs-toggle="modal" data-bs-target="#hapusData" data-id-details="<?= encryptId($id_details_produksi, $key_akses) ?>" data-id-spk="<?= htmlspecialchars($_GET['id']); ?>" data-nama-produk="<?= $nama_produk; ?>" data-referer="<?= $_GET['status']; ?>" title="Hapus SPK Produksi">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    <?php
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="9" class="text-center text-muted">Data tidak ditemukan.</td></tr>
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
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

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

<?php 
    require_once __DIR__ . '/modal-dialog/form-spk-produksi.php';
    require_once __DIR__ . '/modal-dialog/add-produk.php';
    require_once __DIR__ . '/modal-dialog/edit-produk.php';
    require_once __DIR__ . '/modal-dialog/hapus-produk.php';
    require_once __DIR__ . '/modal-dialog/proses-produksi.php';
?>

<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>





