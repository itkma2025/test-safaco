<?php  
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once __DIR__ . '/query/data-permintaan-karsa.php';
    require_once base_path('public/function-php/uuid.php');
    require_once base_path('public/function-php/csrf-token.php');
    require_once base_path('config/database/database.php');
    use Illuminate\Database\Capsule\Manager as DB;

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }

    $id_permintaan_barang = htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8');
    $id_permintaan_barang_decrypt = decryptId($id_permintaan_barang, $key_akses);


    $data_permintaan    = DB::connection('safaco')
                            ->table('permintaan_barang_karsa')
                            ->where('id_permintaan_barang', $id_permintaan_barang_decrypt)->first();
    
    // Menampilkan data
    $no_permintaan = $data_permintaan->no_permintaan;
    $tgl_permintaan = $data_permintaan->tgl_permintaan;
    $id_jenis_permintaan = $data_permintaan->id_jenis_permintaan;
    $persetujuan_pjt_safaco = $data_permintaan->persetujuan_pjt_safaco;
    $catatan = $data_permintaan->catatan;

    $modalPersetujuan = $persetujuan_pjt_safaco == '0' ? 'persetujuanPJTSafaco' : 'persetujuanMr';

    // Query untuk menampilkan data jenis permintaan
    $data_jenis_permintaan = DB::connection('safaco')
                                ->table('jenis_permintaan')
                                ->where('id_jenis_permintaan', $id_jenis_permintaan)->first();
    $nama_jenis_permintaan = $data_jenis_permintaan->nama_jenis_permintaan;
?>
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
            <div class="card-header text-center">
                <h4 class="mb-0">Detail Permintaan Barang Internal</h4>
            </div>
            <!-- Form Input -->
            <div class="mb-3 mt-3 row">
                <label class="form-label col-md-3">No Permintaan Barang</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" value="<?= $no_permintaan ?>" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-md-3">Tgl. Permintaan</label>
                <div class="col-md-9">
                    <input class="form-control" value="<?= date('d-m-Y', strtotime($tgl_permintaan)) ?>" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-md-3">Jenis Permintaan</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" value="<?= $nama_jenis_permintaan ?>" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-md-3">Catatan</label>
                <div class="col-md-9">
                    <textarea class="form-control" readonly><?= $catatan ?></textarea>
                </div>
            </div>
        </div>

        <div class="card-body" style="min-height:70vh">
            <div class="text-muted medium">
                <button class="btn btn-secondary btn-mobile" data-bs-toggle="modal" data-bs-target="#<?= $modalPersetujuan ?>">
                    <i class="fe fe-refresh-cw me-1"></i>Ubah Status
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="tableDetail" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center" style="min-width: 40px;">No</th>
                            <th class="text-center" style="min-width: 150px;">Kode Produk</th>
                            <th class="text-center" style="min-width: 250px;">Nama Produk</th>
                            <th class="text-center" style="min-width: 150px;">Kategori Produk</th>
                            <th class="text-center" style="min-width: 100px;">Merk</th>
                            <th class="text-center" style="min-width: 100px;">Grade</th>
                            <th class="text-center" style="min-width: 100px;">Qty Request</th>
                            <th class="text-center" style="min-width: 100px;">Satuan</th>
                            <th class="text-center" style="min-width: 100px;">Qty Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProduk"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php ob_end_flush(); ?>

<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

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
        const idPerm = '<?= $id_permintaan_barang ?>';
        console.log('ID Permintaan Barang (Encrypted):', idPerm);

        new DataTable('#tableDetail', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=query-details-permintaan-produk-karsa',
                type: 'POST',
                data: {
                    id_permintaan_barang : idPerm,
                },
                // Untuk debugging response dari server
                // dataSrc: function (json) {
                //     console.log("SERVER RESPONSE:", json);
                //     return json.data; // wajib return data untuk DataTables
                // }
            },
        });
    });
</script>

<?php require_once __DIR__ . '/modal-dialog/persetujuan-pjt-safaco.php'; ?>
<?php require_once __DIR__ . '/modal-dialog/persetujuan-mr.php'; ?>

