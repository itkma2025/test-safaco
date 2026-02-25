<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../../config/config.php';
    require_once base_path('helpers/domain.php');
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/uuid.php');
    require_once base_path('public/function-php/csrf-token.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once base_path('public/function-php/sanitasi-input.php');
    require_once __DIR__ . "/query/kategori-penjualan.php";
    require_once __DIR__ . "/query/grade-produk.php";

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }

    // Get domain
    $domain_sso = DOMAIN_SSO;

    $id_produk              = '';
    $action                 = '';
    $label_upload           = '';
    $label_card_header      = '';
    $select_pcs             = '';
    $select_set             = '';
    $id_kategori_produk     = '';
    $id_kategori_penjualan  = '';
    $id_grade_produk        = '';
    
    if(isset($_GET['id'])){
        $action = 'edit';
        $label_card_header = 'Edit';
        $id_produk                  = $sanitasi_input->purify($_GET['id']);
        $id_produk_decrypt          = $sanitasi_input->purify(decryptId($_GET['id'], $key_akses));

        // Kondisi jika decrypt gagal atau user sengaja menghapus sebagian ID
        if (!$id_produk) {
            ?>
                <script>
                    window.location.replace('data-produk.php?action=produk&&error=id')
                </script>
            <?php
            exit;
        }

        require_once __DIR__ . '/query/detail-produk.php';
        // Kondisi untuk penanganan jika data tidak di temukan
        if (!$data_produk) {
            ?>
                <script>
                    window.location.replace('data-produk.php?action=produk&&error=id')
                </script>
            <?php
            exit;
        }
        $id_produk_master           = $data_produk->id_produk_master;
        $nama_produk_master         = $data_produk->nama_produk_master;
        $kode_produk_set            = $data_produk->kode_produk_set;
        $kode_katalog               = $data_produk->kode_katalog;
        $nama_produk_set            = $data_produk->nama_produk_set;
        $id_kategori_produk         = $data_produk->id_kategori_produk;
        $nama_kategori_produk       = $data_produk->nama_kategori_produk;
        $nama_merk                  = $data_produk->nama_merk;
        $harga                      = $data_produk->harga ?? 0;
        $id_kategori_penjualan      = $data_produk->id_kategori_penjualan;
        $id_grade_produk            = $data_produk->id_grade_produk;
        $id_lokasi                  = $data_produk->id_lokasi;
        $nama_lokasi                = $data_produk->nama_lokasi;
        $lantai                     = $data_produk->lantai;
        $area                       = $data_produk->area;
        $no_rak                     = $data_produk->no_rak;
        $deskripsi_produk           = $data_produk->deskripsi_produk;
        $filename                   = $data_produk->filename;
        $label_upload               = 'Ubah Gambar Produk';
    } else {
        $action         = 'create';
        $id_produk      = 'PRD_SET_' . uuid();
        $label_upload   = 'Upload Gambar Produk';
        $label_card_header = 'Tambah';
    }
?>
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/dropzone.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>

<!-- Plugin Selectize JS -->
<link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />
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
    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-header text-center">
            <h4><?= $label_card_header ?> Data Produk</h4>
        </div>
        <div class="card-body" style="min-height:70vh">
            <form class="row g-3 mt-0" method="POST" id="saveForm" enctype="multipart/form-data">
                <input type="hidden" class="form-control" name="id_produk_set" value="<?= $id_produk ?>">
                <input type="hidden" class="form-control" name="routes" value="produk-set">
                <input type="hidden" name="action" value="<?= encryptId($action, $key_akses); ?>">
                <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <!-- Baris 1 -->
                <div class="col-md-7">
                    <label class="form-label">Pilih Produk</label>
                    <div class="input-group" data-bs-toggle="modal" data-bs-target="#produk">
                        <input type="hidden" class="form-control" name="id_produk_master" id="idProdukMaster" value="<?= $id_produk_master ?? '' ?>" readonly>
                        <input type="text" class="form-control" id="namaProdukMaster" value="<?= $nama_produk_master ?? '' ?>" readonly>
                        <span class="input-group-text"><i class="fe fe-search"></i></span>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Kode Produk Set</label>
                    <input type="text" class="form-control" maxlength="50" name="kode_produk_set" value="<?= $kode_produk_set ?? '' ?>" required>
                </div>

                <!-- Baris 2 -->
                <div class="col-md-4">
                    <label class="form-label">Nama Produk Set</label>
                    <input type="text" class="form-control" maxlength="100" name="nama_produk_set" id="namaProduk" value="<?= $nama_produk_set ?? '' ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kategori Produk</label>
                    <input type="hidden" class="form-control" maxlength="50" name="id_kategori_produk" id="idKatProd" value="<?= $id_kategori_produk ?? '' ?>" readonly>
                    <div class="input-group" data-bs-toggle="modal" data-bs-target="#katProduk">
                        <input type="text" class="form-control" id="namaKatProd" value="<?= $nama_kategori_produk ?? '' ?>" readonly>
                        <span class="input-group-text"><i class="fe fe-search"></i></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Merk Produk</label>
                    <input type="text" class="form-control bg-light border" id="namaMerk" value="<?= $nama_merk ?? '' ?>" readonly>
                </div>

                <!-- Baris 3 -->
                <div class="col-md-4">
                    <label class="form-label">Harga Produk (Rp)</label>
                    <input type="text" class="form-control" maxlength="16" name="harga" value="<?= number_format($harga ?? 0, 0,'.','.') ?>" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
                </div>

                <div class="col-4">
                    <label class="form-label">Kategori Penjualan</label>
                    <select name="id_kategori_penjualan" class="form-select kategoriPenjualan" required>
                        <option value="">Pilih...</option>
                        <?php foreach ($kategoriPenjualan as $index => $katPenj): ?>
                            <option value="<?= $katPenj->id_kategori_penjualan ?>" <?= ($katPenj->id_kategori_penjualan == $id_kategori_penjualan) ? 'selected' : '' ?>><?= $katPenj->kategori_penjualan ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Grade Produk</label>
                    <select name="id_grade_produk" class="form-select gradeProduk" required>
                        <option value="">Pilih...</option>
                        <?php foreach ($gradeProduk as $index => $grade): ?>
                            <option value="<?= $grade->id_grade_produk ?>" <?= ($grade->id_grade_produk == $id_grade_produk) ? 'selected' : '' ?>><?= $grade->grade ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Baris 4 -->
                <div class="col-3">
                    <label  class="form-label">Lokasi Produk</label>
                    <div class="input-group" data-bs-toggle="modal" data-bs-target="#lokasiProduk">
                        <input type="hidden" class="form-control" name="id_lokasi" id="idLokasi" value="<?= $id_lokasi ?? '' ?>" readonly>
                        <input type="text" class="form-control" id="namaLokasi" value="<?= $nama_lokasi ?? '' ?>" readonly>
                        <span class="input-group-text"><i class="fe fe-search"></i></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">No. Lantai</label>
                    <input type="text" class="form-control bg-light border" id="lantai" value="<?= $lantai ?? '' ?>" readonly>
                </div>
                <div class="col-3">
                    <label class="form-label">Area Gudang</label>
                    <input type="text" class="form-control bg-light border" id="area" value="<?= $area ?? '' ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">No. Rak</label>
                    <input type="text" class="form-control bg-light border" id="noRak" value="<?= $no_rak ?? '' ?>" readonly>
                </div>

                <!-- Baris 5 -->
                <div class="col-md-12">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea class="deskripsi" name="deskripsi_produk" id="deskripsi" maxlength="2000"><?= $deskripsi_produk ?? '' ?></textarea>
                    <div id="charCount"></div>
                </div>
                
                <!-- Baris 6 -->
                <!-- Upload Container -->
                <!-- Untuk proses edit data -->
                <input type="text" name="dataFileName" class="d-none" id="dataFileName" value="<?= $filename ?>">
                <div class="custom-file-container mt-5" data-upload-id="myFirstImage">
                    <!-- Drop Zone -->
                    <div id="dropZone" class="drop-zone">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <div>Drag & Drop gambar di sini atau <strong>klik untuk pilih</strong></div>
                        <input type="file" name="fileInput" id="fileInput" class="d-none" accept=".jpg, .jpeg, .png, .webp">
                    </div>

                    <!-- Drop Zone Preview -->
                    <div id="previewZone" class="drop-zone">
                        <div id="fileInfo" class="file-preview">
                            <?php if (!empty($filename)) : ?>
                                <img src="view-img.php?id=<?= $id_produk; ?>" alt="preview">
                                <a href="view-img.php?id=<?= $id_produk; ?>" data-fancybox="preview" class="file-name">
                                    <?= htmlspecialchars(pathinfo($filename, PATHINFO_FILENAME)) ?>
                                </a>
                                <span class="remove-file">&times;</span>
                                <script>
                                    // sembunyikan dropZone jika ada file lama
                                    document.getElementById("dropZone").classList.add("d-none");
                                    // Tampilkan previewZone jika ada file lama
                                    document.getElementById("previewZone").classList.add("d-block");
                                    // tambahkan handler reset juga untuk edit
                                    document.querySelector(".remove-file").addEventListener("click", (ev) => {
                                        document.getElementById("dropZone").classList.remove("d-none");
                                        document.getElementById("previewZone").classList.remove("d-block");
                                        ev.stopPropagation();
                                        resetFileInput();
                                    });
                                </script>
                            <?php endif; ?>
                        </div>
                    </div>             
                </div>

                <!-- Honeypot Field: Tersembunyi dari Pengguna -->
                <div style="display:none;">
                    <label>Honeypot (Jangan Diisi):</label>
                    <input type="text" id="honeypot" name="honeypot">
                </div>

                <div class="col-12">
                    <div class="text-end mt-4" id="formButtons"> 
                        <button type="button" class="btn btn-success me-2" onclick="window.location.href='data-produk.php?action=produk-satuan'">
                            <i class="fe fe-x-circle me-1"></i> Tutup
                        </button>
                        <button type="submit" class="btn btn-info" id="submitBtn">
                            <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                            <span id="btnSimpanText">Simpan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

<!-- Modal produk -->
<?php  
    require_once __DIR__ . "/modal-dialog/produk-master.php";
    require_once __DIR__ . "/modal-dialog/kategori-produk.php";
    require_once __DIR__ . "/modal-dialog/lokasi-produk.php";
?>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".kategoriPenjualan, .gradeProduk").selectize();
</script>

<!-- Function JS -->
<script src="<?= vendor('CKEditor5/ckeditor.js') ?>"></script>
<script src="<?= functionJs('global/CKEditor-deskripsi.js') ?>"></script>
<script src="<?= functionJs('global/number-only.js') ?>"></script>

<!-- Feather Icon JS -->
<script src="<?= asset('js/feather.min.js') ?>"></script>

<!-- Fileupload JS -->
<script src="<?= asset('plugins/fileupload/fileupload.min.js') ?>"></script>

<!-- Custom JS -->
<script src="<?= asset('js-custom/dropzone.js') ?>"></script>

<!-- Fancybox -->
<script src="<?= vendor('fancybox/fancybox.umd.js') ?>"></script>

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
        new DataTable('#tableLokasi, #tableKatProd', {
            lengthChange: false,
        });
    });
</script>
<!-- End Datatable Bootstraps 5 -->

<script src="<?= functionJs('proses-data.js') ?>"></script>




