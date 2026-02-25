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

    $id_permintaan_barang = "PERM_BRG_" . uuid();

    $data_permintaan    = DB::connection('safaco')->table('permintaan_barang_karsa')->whereYear('tgl_permintaan', date('Y'))->count();
    // Kode otomatis no SPK
    $array_bln = array(1 => "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
    $bln = $array_bln[date('n')];
    $format1 = "/SPm/SFC/";
    $format2 = "/";
    $format3 = date("Y");
    $urutkan = $data_permintaan; // Mengambil nilai maksimum langsung dari hasil query
    $urutkan++;
    $no_permintaan = sprintf("%03s", $urutkan) . $format1 . $bln . $format2 . $format3;


    // Query untuk menampilkan data jenis permintaan
    $data_jenis_permintaan = DB::connection('safaco')->table('jenis_permintaan')->get();

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
            <nav class="nav nav-style-6 nav-pills mb-3 border-bottom" role="tablist">
                <a class="nav-link" href="#">
                    Permohonan Baru
                    <span class="badge bg-secondary ms-1 rounded-pill">1</span>
                </a>

                <a class="nav-link" href="#">
                    Menunggu Persetujuan
                    <span class="badge bg-secondary ms-1 rounded-pill">2</span>
                </a>

                <a class="nav-link" href="#">
                    Pengembalian Barang
                    <span class="badge bg-secondary ms-1 rounded-pill">3</span>
                </a>

                <a class="nav-link" href="#">
                    Selesai
                    <span class="badge bg-secondary ms-1 rounded-pill">4</span>
                </a>

                <a class="nav-link" href="#">
                    Batal
                    <span class="badge bg-secondary ms-1 rounded-pill">5</span>
                </a>
            </nav>
        </div>
        <div class="card-body">
            <h4 class="mb-3">Detail Permintaan Barang Internal</h4>
            <!-- Form Input -->
            <input type="hidden" class="form-control" id="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" class="form-control" id="id_permintaan_barang" value="<?= $id_permintaan_barang ?>" readonly>
            <div class="mb-3 row">
                <label class="form-label col-md-3">No Permintaan Barang</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="no_permintaan" value="<?= $no_permintaan ?>" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-md-3">Tgl. Permintaan</label>
                <div class="col-md-9">
                    <input type="date" class="form-control" id="tgl_permintaan" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-md-3">Jenis Permintaan</label>
                <div class="col-md-9">
                    <select name="jenis_permintaan" id="jenis_permintaan" class="form-control jenis_permintaan" required>
                        <option value="">Pilih Jenis Permintaan</option>
                        <?php foreach ($data_jenis_permintaan as $jenis) { ?>
                            <option value="<?= $jenis->id_jenis_permintaan ?>"><?= $jenis->nama_jenis_permintaan ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-md-3">Catatan</label>
                <div class="col-md-9">
                    <textarea class="form-control" id="catatan" required></textarea>
                </div>
            </div>
            <!-- Honeypot Field: Tersembunyi dari Pengguna -->
            <div style="display:none;">
                <label>Honeypot (Jangan Diisi):</label>
                <input type="text" id="honeypot" name="honeypot">
            </div>
            <!-- End Form Input -->
        </div>
        <div class="card-body" style="min-height:70vh">
            <!-- Search & button -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted medium">
                    <button class="btn btn-primary btn-mobile btnForm" data-bs-toggle="modal" data-bs-target="#produk">
                        <i class="fe fe-plus-circle me-1"></i>Tambah produk
                    </button>
                    <button class="btn btn-secondary btn-mobile" id="btnProses">
                        <i class="fe fe-refresh-cw me-1"></i>Proses Permintaan
                    </button>
                    <button class="btn btn-danger btn-mobile" id="batalPermintaan" disabled>
                        <i class="fe fe-x-circle me-1"></i>Batal Permintaan
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
                            <th class="text-center" style="min-width: 150px;">Kode Produk</th>
                            <th class="text-center" style="min-width: 250px;">Nama Produk</th>
                            <th class="text-center" style="min-width: 150px;">Kategori Produk</th>
                            <th class="text-center" style="min-width: 100px;">Merk</th>
                            <th class="text-center" style="min-width: 100px;">Grade</th>
                            <th class="text-center" style="min-width: 100px;">Qty Request</th>
                            <th class="text-center" style="min-width: 100px;">Satuan</th>
                            <th class="text-center" style="min-width: 100px;">Qty Saat Ini</th>
                            <th class="text-center" style="min-width: 100px;">Aksi</th>
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
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

<!-- Function untuk proses update status aktif / no aktif -->
<script src="<?= functionJs('proses-status-active.js') ?>"></script>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".jenis_permintaan").selectize();
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

<?php 
    require_once __DIR__ . '/modal-dialog/add-produk.php'; 
?>

<!-- Custom JS -->
<script src="<?= functionJs('produk-masuk/permohonan-baru.js') ?>"></script>
<script src="<?= functionJs('global/number-only.js') ?>"></script>

<script>

</script>

<!-- Kode untuk batalkan permintaan -->
<script>
    
</script>

