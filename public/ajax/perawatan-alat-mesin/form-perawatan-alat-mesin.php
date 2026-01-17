<?php  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../../config/config.php';
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/uuid.php');
    require_once base_path('public/function-php/csrf-token.php');
    require_once base_path('public/function-php/sanitasi-input.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');
    require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');
    require_once base_path('config/database/database.php');
    use Illuminate\Database\Capsule\Manager as DB;
    // Library sanitasi input data
    $sanitasi_post = sanitizeInput($_POST);

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }
    // Default aksi
    $aksi = 'create';
    $label = 'Tambah';
    $kode_barang     = '';
    $nama_barang     = '';
    $jenis_barang    = '';
    $kondisi         = '';
    $tgl_pembelian   = '';
    $nama_supplier   = '';
    $kalibrasi       = '';
    $status_merk     = '';
    $id_merk         = '';
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_alat_mesin_decrypt  = decryptId($sanitasi_post['id'], $key_akses);
        $data_alat_mesin        = DB::connection('safaco')->table('alat_mesin as am')
                                                    ->leftJoin('produk_lokasi as pl', 'am.id_lokasi', '=', 'pl.id_lokasi')
                                                    ->leftJoin('alat_mesin_gambar as amg', 'am.id_alat_mesin', '=', 'amg.id_alat_mesin')
                                                    ->select(
                                                        'am.id_alat_mesin', 
                                                        'am.kode_barang', 
                                                        'am.nama_barang', 
                                                        'am.jenis_barang',
                                                        'am.tgl_pembelian',
                                                        'am.kalibrasi',
                                                        'am.status_merk',
                                                        'am.kondisi',
                                                        'am.status_active',
                                                        'am.id_merk',
                                                        'am.id_lokasi',
                                                        'am.id_supplier',
                                                        'pl.nama_lokasi',
                                                        'amg.filename'
                                                    )
                                                    ->where('am.id_alat_mesin', $id_alat_mesin_decrypt)
                                                    ->orderBy('am.nama_barang', 'asc')
                                                    ->first();
        $kode_barang            = $data_alat_mesin->kode_barang;
        $nama_barang            = $data_alat_mesin->nama_barang;
        $jenis_barang           = $data_alat_mesin->jenis_barang;
        $kalibrasi              = $data_alat_mesin->kalibrasi;
        $status_merk            = $data_alat_mesin->status_merk;
        $kondisi                = $data_alat_mesin->kondisi;
        $id_lokasi              = $data_alat_mesin->id_lokasi;
        $nama_lokasi            = $data_alat_mesin->nama_lokasi;
        $tgl_pembelian          = $data_alat_mesin->tgl_pembelian ? date('Y-m-d', strtotime($data_alat_mesin->tgl_pembelian)) : '';
        $id_supplier            = $data_alat_mesin->id_supplier;
        $id_merk                = $data_alat_mesin->id_merk;
        $filename               = $data_alat_mesin->filename;
        // cari nama supplier
        $supplier = DB::connection('supplier')->table('supplier')->where('id_supplier', $id_supplier)->first();
        $nama_supplier = $supplier ? $supplier->nama_sp : '';
    }

    $id_alat_mesin = $sanitasi_post['id'] ?? "ALMES_" . uuid();

   
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
    table {
        width: 100% !important;
        border-collapse: collapse;
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

    div.dt-container div.dt-paging ul.pagination {
        display: flex;
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
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Alat dan Mesin</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_alat_mesin" value="<?= $id_alat_mesin ?>">
        <input type="hidden" class="form-control" name="routes" value="alat-mesin">
        <input type="hidden" class="form-control" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="form-control" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- Kode Barang -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Kode Barang</label>
            <div class="col-md-9">
                <input type="text" name="kode_barang" class="form-control" maxlength="50" value="<?= $kode_barang ?>" required>
            </div>
        </div>

        <!-- Nama Barang -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Barang</label>
            <div class="col-md-9">
                <input type="text" name="nama_barang" class="form-control" maxlength="100" value="<?= $nama_barang ?>" oninput="filterTextOnly(this)" required>
            </div>
        </div>

        <!-- Perlu Kalibrasi -->
        <div class="mb-3">
            <label class="form-label col-md-3">Apakah Perlu Kalibrasi?</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="kalibrasi" value="1" <?= ($kalibrasi == '1') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="statusAda">Perlu</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="kalibrasi" value="0" <?= ($kalibrasi == '0') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="statusTidakAda">Tidak Perlu</label>
            </div>
        </div>

        <!-- Jenis Barang -->
        <div class="mb-3">
            <label class="form-label col-md-3">Jenis Barang</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="jenis_barang" value="Alat" <?= ($jenis_barang == 'Alat') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="statusAda">Alat</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="jenis_barang" value="Mesin" <?= ($jenis_barang == 'Mesin') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="statusTidakAda">Mesin</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="jenis_barang" value="Operasional" <?= ($jenis_barang == 'Operasional') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="statusTidakAda">Operasional</label>
            </div>
        </div>

        <!-- Status Merk -->
        <div class="mb-3">
            <label class="form-label col-md-3">Status Merk</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status_merk" id="merkAda" value="1" <?= ($status_merk == '1') ? 'checked' : '' ?> required>
                <label class="form-check-label">Ada</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status_merk" id="merkTidakAda" value="0" <?= ($status_merk == '0') ? 'checked' : '' ?> required>
                <label class="form-check-label">Tidak Ada</label>
            </div>
        </div>

        <!-- Merk Barang -->
        <div class="mb-3 row d-none" id="merkRow">
            <label class="form-label col-md-3">Merk Barang</label>
            <div class="col-md-9">
               <select name="id_merk" id="idMerk" class="form-control merk">
                    <option value="">Pilih Merk</option>
                    <?php
                        $merk = DB::connection('kat_produk')
                                ->table('tb_merk')
                                ->select(
                                    'id_merk',
                                    'nama_merk'
                                )
                                ->where('status', '1')
                                ->orderBy('nama_merk', 'asc')
                                ->get();
                    ?>
                    <?php foreach ($merk as $mr) : ?>
                        <option value="<?= $mr->id_merk ?>" 
                            <?= ($mr->id_merk == $id_merk) ? 'selected' : '' ?>>
                            <?= $mr->nama_merk ?>
                        </option>
                    <?php endforeach; ?>
               </select>
            </div>
        </div>

        <!-- Tanggal Pembelian -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Tanggal Pembelian</label>
            <div class="col-md-9">
                <input type="date" name="tgl_pembelian" class="form-control" value="<?= $tgl_pembelian ?>" required>
            </div>                                                                  
        </div>

        <!-- Kondisi -->
        <div class="mb-3">
            <label class="form-label col-md-3">Kondisi</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="kondisi" value="Baru" id="kondisiBaru" <?= ($kondisi == 'Baru') ? 'checked' : '' ?>>
                <label class="form-check-label" for="kondisiBaru">Baru</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="kondisi" value="Bekas" id="kondisiBekas" <?= ($kondisi == 'Bekas') ? 'checked' : '' ?>>
                <label class="form-check-label" for="kondisiBekas">Bekas</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="kondisi" value="Custom Sendiri (DIY)" id="kondisiCustom" <?= ($kondisi == 'Custom Sendiri (DIY)') ? 'checked' : '' ?>>
                <label class="form-check-label" for="kondisiCustom">Custom Sendiri (DIY)</label>
            </div>
        </div>

        <!-- Pilih Vendor -->
        <div class="mb-3 row d-none" id="vendorRow">
            <label class="form-label col-md-3">Pilih Vendor</label>
            <div class="col-md-9">
                <div class="input-group" id="modalSupplier" style="cursor: pointer;">
                    <input type="hidden" class="form-control" name="id_supplier" id="idSupplier" value="<?= $id_supplier ?? '' ?>" readonly>
                    <input type="text" class="form-control" id="namaSupplier" value="<?= $nama_supplier ?? '' ?>" readonly>
                    <span class="input-group-text"><i class="fe fe-search"></i></span>
                </div>
            </div>
        </div>
        
        <!-- Lokasi -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Pilih Lokasi</label>
            <div class="col-md-9">
                <div class="input-group" id="modalLokasi" style="cursor: pointer;">
                    <input type="hidden" class="form-control" name="id_lokasi" id="idLokasi" value="<?= $id_lokasi ?? '' ?>" readonly>
                    <input type="text" class="form-control" id="namaLokasi" value="<?= $nama_lokasi ?? '' ?>" readonly>
                    <span class="input-group-text"><i class="fe fe-search"></i></span>
                </div>
            </div>
        </div> 
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
                        <img src="view-img.php?id=<?= encryptId($id_alat_mesin_decrypt, $key_akses); ?>" alt="preview">
                        <a href="view-img.php?id=<?= encryptId($id_alat_mesin_decrypt, $key_akses); ?>" data-fancybox="preview" class="file-name">
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
    </div>
    <!-- Honeypot Field: Tersembunyi dari Pengguna -->
    <div style="display:none;">
        <label for="honeypot">Honeypot (Jangan Diisi):</label>
        <input type="text" id="honeypot" name="honeypot">
    </div>
    <div class="modal-footer">
        <div class="text-right mt-4" id="formButtons"> 
            <button type="button" class="btn btn-success me-2" onclick="location.reload()">
                <i class="fe fe-x-circle me-1"></i>Tutup
            </button>
            <button type="submit" class="btn btn-info" id="submitBtn">
                <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                <span id="btnSimpanText">Simpan</span>
            </button>
        </div>
    </div>
</form>

<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>

<!-- Function JS -->
<script src="<?= functionJs('global/text-only.js') ?>"></script>

<!-- Fileupload JS -->
<script src="<?= asset('plugins/fileupload/fileupload.min.js') ?>"></script>

<!-- Custom JS -->
<script src="<?= asset('js-custom/dropzone.js') ?>"></script>

<!-- Fancybox -->
<script src="<?= vendor('fancybox/fancybox.umd.js') ?>"></script>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".merk").selectize();
</script>

<!-- Kode untuk status merk -->
<script>
    const radiosMerk    = document.querySelectorAll('input[name="status_merk"]');
    const merkRow       = document.getElementById("merkRow");
    const idMerkSelect  = document.getElementById("idMerk-selectized"); // <select> asli
    const idMerk        = $("#idMerk").selectize()[0].selectize; // instance selectize

    function toggleMerkRow(selectedMerkId) {
        if (selectedMerkId === "merkAda") {
            merkRow.classList.remove("d-none");
        } else {
            merkRow.classList.add("d-none");
            idMerk.clear(); // reset selectize value
        }
    }

    // Event listener untuk radio
    radiosMerk.forEach(radio => {
        radio.addEventListener("change", function() {
            toggleMerkRow(this.id);
        });
    });

    // Jalankan sekali saat halaman load (untuk kondisi edit data)
    const checkedRadioMerk = document.querySelector('input[name="status_merk"]:checked');
    if (checkedRadioMerk) {
        toggleMerkRow(checkedRadioMerk.id);
    }
</script>

<!-- Kode untuk hide and show bagian kondisi -->
<script>
    const radios        = document.querySelectorAll('input[name="kondisi"]');
    const vendorRow     = document.getElementById("vendorRow");
    const idSupplier    = document.getElementById("idSupplier");
    const namaSupplier  = document.getElementById("namaSupplier");

    function toggleVendorRow(selectedId) {
        if (selectedId === "kondisiBaru" || selectedId === "kondisiBekas") {
            vendorRow.classList.remove("d-none");
            idSupplier.value    = '<?= $id_supplier ?? '' ?>';
        } else {
            vendorRow.classList.add("d-none");
            idSupplier.value        = '';
             namaSupplier.value     = '';
        }
    }

    // Event listener
    radios.forEach(radio => {
        radio.addEventListener("change", function() {
            toggleVendorRow(this.id);
        });
    });

    // Jalankan sekali saat halaman load (cek kondisi awal)
    const checkedRadio = document.querySelector('input[name="kondisi"]:checked');
    if (checkedRadio) {
        toggleVendorRow(checkedRadio.id);
    }
</script>

<!-- Modal Lokasi -->
<div class="modal fade" id="lokasi" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Lokasi Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tableLokasi">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Nama Lokasi</th>
                                <th class="text-center" style="width:300px;">Lantai</th>
                                <th class="text-center" style="width:350px;">Area</th>
                                <th class="text-center" style="width:350px;">No. Rak</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                require_once base_path('public/vendor/autoload.php');
                                require_once base_path('config/database/database.php');

                                $lokasi_produk = DB::connection('safaco')
                                                    ->table('produk_lokasi')
                                                    ->select(
                                                        'id_lokasi',
                                                        'nama_lokasi',
                                                        'lantai',
                                                        'area',
                                                        'no_rak'
                                                    )
                                                    ->where('status_active', '1')
                                                    ->orderBy('nama_lokasi', 'asc')
                                                    ->get();
                            ?>
                            <?php if (!$lokasi_produk->isEmpty()) : ?>
                                <?php $no = 1;  foreach ($lokasi_produk as $lokasi) : ?>
                                <?php
                                    $id_lokasi      = $lokasi->id_lokasi;
                                    $nama_lokasi    = $lokasi->nama_lokasi;
                                    $lantai         = $lokasi->lantai;     // Nama file terenkripsi (tanpa .enc)
                                    $area           = $lokasi->area;
                                    $no_rak         = $lokasi->no_rak;  // Nama folder
                                ?>
                                    <tr>
                                        <td class="align-middle text-center"><?= $no++ ?></td>
                                        <td class="align-middle text-center"><?= $nama_lokasi ?></td>
                                        <td class="align-middle"><?= $lantai ?></td>
                                        <td class="align-middle"><?= $area ?></td>
                                        <td class="align-middle"><?= $no_rak ?></td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn btn-primary btn-sm selectLokasiProduk"
                                            data-id-lokasi="<?= $id_lokasi; ?>" 
                                            data-nama-lokasi="<?= $nama_lokasi . '/' . $lantai . '/' . $area . '/' . $no_rak; ?>" 
                                            data-bs-dismiss="modal">
                                            Pilih
                                        </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="7" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal kedua manual pakai JS
    const lokasi = new bootstrap.Modal(document.getElementById('lokasi'), {
        backdrop: false // agar tidak menutupi modal utama
    });

    document.getElementById('modalLokasi').addEventListener('click', () => {
        lokasi.show();
    });

     // select Produk Mater
    $(document).on("click", ".selectLokasiProduk", function () {
        $("#idLokasi").val($(this).data("id-lokasi"));
        $("#namaLokasi").val($(this).data("nama-lokasi"));
    });
</script>



<!-- Modal supplier -->
<div class="modal fade" id="supplier" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tableSupplier">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Nama Vendor</th>
                                <th class="text-center" style="width:300px;">Alamat</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                require_once base_path('public/vendor/autoload.php');
                                require_once base_path('config/database/database.php');

                                $vendor = DB::connection('supplier')
                                                    ->table('supplier')
                                                    ->select(
                                                        'id_supplier',
                                                        'nama_sp',
                                                        'alamat'
                                                    )
                                                    ->where('status_active', '1')
                                                    ->orderBy('nama_sp', 'asc')
                                                    ->get();
                            ?>
                            <?php if (!$vendor->isEmpty()) : ?>
                                <?php $no = 1;  foreach ($vendor as $vendor) : ?>
                                <?php
                                    $id_supplier      = $vendor->id_supplier;
                                    $nama_supplier    = $vendor->nama_sp;
                                    $alamat           = $vendor->alamat;
                                ?>
                                    <tr>
                                        <td class="align-middle text-center"><?= $no++ ?></td>
                                        <td class="align-middle"><?= $nama_supplier ?></td>
                                        <td class="align-middle text-wrap"><?= $alamat ?></td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn btn-primary btn-sm selectSupplier"
                                            data-id-supplier="<?= $id_supplier; ?>" 
                                            data-nama-supplier="<?= $nama_supplier; ?>" 
                                            data-bs-dismiss="modal">
                                            Pilih
                                        </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="7" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal supplier manual pakai JS
    const supplier = new bootstrap.Modal(document.getElementById('supplier'), {
        backdrop: false // agar tidak menutupi modal utama
    });

    document.getElementById('modalSupplier').addEventListener('click', () => {
        supplier.show();
    });

     // select Produk Mater
    $(document).on("click", ".selectSupplier", function () {
        $("#idSupplier").val($(this).data("id-supplier"));
        $("#namaSupplier").val($(this).data("nama-supplier"));
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
        new DataTable('#tableLokasi, #tableSupplier', {
            lengthChange: false,
            paging: true
        });
    });
</script>
<!-- End Datatable Bootstraps 5 -->



