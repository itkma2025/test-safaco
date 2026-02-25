<?php  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['id_user'])) {
        header("location: 404.php");
        exit;
    }

    require_once __DIR__ . '/../../../config/config.php';
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/csrf-token.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php'); 
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
    require_once base_path('config/database/database.php');
    require_once base_path('public/function-php/uuid.php');

    // Library database
    use Illuminate\Database\Capsule\Manager as DB;

    // Library sanitasi input data
    require_once base_path('public/function-php/sanitasi-input.php');
    $sanitasi_post = sanitizeInput($_POST);

    // --- Koneksi DB ---
    $db_safaco   = DB::connection('safaco');
    $db_kat_prod = DB::connection('kat_produk');

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }


    // Definisi variabel agar tidak error saat mode create isi
    $id_produk       = '';
    $nama_produk      = '';
    $merk             = '';
    $qty              = '';

    if (isset($sanitasi_post['id'])) {
        $id_produk_set      = decryptId($sanitasi_post['idset'], $key_akses);
        $id_isi_produk_set  = decryptId($sanitasi_post['id'], $key_akses);
        $action             = $sanitasi_post['action'] ?? '';

        // Query untuk menampilkan aata
        $data_isi_set = $db_safaco->table('isi_produk_set as ips')
                            ->leftJoin('produk_satuan as ps', 'ips.id_produk', '=', 'ps.id_produk')
                            ->where('ips.id_isi_produk_set', $id_isi_produk_set)
                            ->first();

        $idKategori = $data_isi_set->id_kategori_produk;

        // Menampilkan merk berdasarkan kategori
        $kategoriList = $db_kat_prod->table('tb_kat_produk as tkp')
                            ->leftJoin('tb_merk as mr', 'tkp.id_merk', '=', 'mr.id_merk')
                            ->where('tkp.id_kat_produk', $idKategori)
                            ->select('tkp.id_kat_produk', 'mr.nama_merk')
                            ->first();

        $id_produk        = $data_isi_set->id_produk;
        $nama_produk      = $data_isi_set->nama_produk;
        $merk             = $kategoriList->nama_merk;
        $qty              = $data_isi_set->qty;
       
    } else {
        $id_produk_set          = decryptId($sanitasi_post['idset'], $key_akses);
        $id_isi_produk_set      = 'ISI_SET_' . uuid();
        $action                 = $sanitasi_post['action'] ?? '';
    }

    $label = ($action === 'edit-isi') ? 'Edit' : 'Tambah';
?>
<!-- FancyBox CSS -->
<link rel="stylesheet" href="<?= vendor('fancybox/fancybox.css') ?>">
<style>
    table{
        width: 100% !important;
    }

    .img-thumbnail {
        max-width: 80px;
        max-height: 80px;
    }

    div.dt-container div.dt-paging ul.pagination {
        display: flex;
    }

    .offcanvas {
        inset: 10% !important; /* top right bottom left */
        height: auto !important;
    }

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
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Data Isi Produk Set</h5>
</div>

<form method="POST" id="saveForm">
    <div class="modal-body">
        <div class="mb-3">
            <input type="hidden" class="form-control" name="id_produk_set" value="<?= encryptId($id_produk_set, $key_akses); ?>">
            <input type="hidden" class="form-control" name="id_isi_produk_set" value="<?= encryptId($id_isi_produk_set, $key_akses); ?>">
            <input type="hidden" class="form-control" name="routes" value="produk-set">
            <input type="hidden" class="form-control" name="action" value="<?= encryptId($action, $key_akses); ?>">
            <input type="hidden" class="form-control" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        </div>

        <!-- Nama Produk -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Produk</label>
            <div class="col-md-9">
                <div class="input-group" data-bs-toggle="offcanvas" data-bs-target="#produk">
                    <input type="hidden" class="form-control bg-light border" name="id_produk" id="idProduk" value="<?= encryptId($id_produk, $key_akses) ?>" readonly>
                    <input type="text" class="form-control bg-light border" id="namaProduk" value="<?= $nama_produk ?>" readonly>
                    <span class="input-group-text"><i class="fe fe-search"></i></span>
                </div>
            </div>
        </div>

        <!-- Merk -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Merk</label>
            <div class="col-md-9">
                <input type="text" class="form-control bg-light border" id="merk" value="<?= $merk ?>" readonly>
            </div>
        </div>
                    
        <!-- Qty -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Qty</label>
            <div class="col-md-9">
                <input type="text" class="form-control border" name="qty" value="<?= $qty ?>" oninput="preventLeadingZero(this)">
            </div>
        </div>
    </div>

    <!-- Honeypot Field: Tersembunyi dari Pengguna -->
    <div style="display:none;">
        <label for="honeypot">Honeypot (Jangan Diisi):</label>
        <input type="text" id="honeypot" name="honeypot">
    </div>

    <div class="modal-footer">
        <div class="text-right mt-4" id="formButtons"> 
            <button type="button" class="btn btn-secondary me-2" onclick="location.reload()">
                <i class="fe fe-x-circle me-1"></i>Cancel
            </button>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                <span id="btnSimpanText">Simpan Data</span>
            </button>
        </div>
    </div>
</form>

<!-- Modal Produk Satuan -->
<div class="offcanvas offcanvas-top" id="produk" data-bs-backdrop="static" tabindex="-1" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Data Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="tableProdSatuan">
                <thead>
                    <tr>
                        <th class="text-center" style="min-width:30px;">No</th>
                        <th class="text-center" style="min-width:100px;">Gambar Produk</th>
                        <th class="text-center" style="min-width:200px;">Kode Produk</th>
                        <th class="text-center" style="min-width:450px;">Nama Produk</th>
                        <th class="text-center" style="min-width:100px;">Merk</th>
                        <th class="text-center" style="min-width:80px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div> 
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<!-- Fancybox -->
<script src="<?= vendor('fancybox/fancybox.umd.js') ?>"></script>
<!-- Bootstrap Core JS -->
<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
<!-- Function number only -->
<script src="<?= functionJs('global/number-only.js') ?>"></script>


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
        new DataTable('#tableProdSatuan', {
            processing: false,
            serverSide: true,
            lengthChange: false,
            pageLength: 6,
            ajax: {
                url: 'data-produk.php?action=produk-satuan-set',
                type: 'POST'
            },
            columnDefs: [
                { targets: [0,1,4], orderable: false }
            ]
        });
    });
</script>

<script>
   // select Produk Satuan
    $(document).on("click", ".selectProduk", function () {

        // ========================
        // 1. Data produk
        // ========================
        const idProduk   = $(this).data("id-produk");
        const namaProduk = $(this).data("nama-produk");
        const merk  = $(this).data("merk");

        $("#idProduk").val(idProduk);
        $("#namaProduk").val(namaProduk);

        // ========================
        // 2. Merk
        // ========================
        $("#merk").val(merk);

        // ========================
        // 3. Preview gambar (SIMPLE) di gunakan jika ingin menampilkan preview gambar produk master di form produk
        // ========================
        const imgSrc = $(this).data("img-src");
        $("#previewProdukLink").attr("href", imgSrc);

        if (imgSrc) {
            $("#previewProduk")
                .attr("src", imgSrc)
                .removeClass("d-none");
        }

        // Tutup modal
        $("#produk").offcanvas("hide");
    });
</script>
<script src="<?= functionJs('proses-data.js') ?>"></script>