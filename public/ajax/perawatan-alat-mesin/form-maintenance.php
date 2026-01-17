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
    $aksi                       = 'create';
    $label                      = 'Tambah';
    $id_history_maintenance     = '';
    $id_alat_mesin              = '';
    $tgl_maintenance            = '';
    $id_jenis_perbaikan         = '';
    $nama_jenis_perbaikan       = '';
    $nama_kategori_perbaikan    = '';
    $petugas_pelaksana          = '';
    $petugas_pelaksana_internal = '';
    $petugas_pelaksana_external = '';
    $id_supplier                = '';
    $nama_sp                    = '';
    $nama_petugas               = '';
    $keterangan                 = '';

    if (!empty($sanitasi_post['id'])) {
        $aksi   = 'edit';
        $label  = 'Edit';

        $id_history_maintenance         = $sanitasi_post['id'];
        $id_history_maintenance_decrypt = decryptId($sanitasi_post['id'], $key_akses);
      
        $data = DB::connection('safaco')
                    ->table('history_maintenance as hm')
                    ->leftJoin('jenis_perbaikan as jp', 'hm.id_jenis_perbaikan', '=', 'jp.id_jenis_perbaikan')
                    ->leftJoin('kategori_perbaikan as kp', 'jp.id_kategori_perbaikan', '=', 'kp.id_kategori_perbaikan')
                    ->where('hm.id_history_maintenance', $id_history_maintenance_decrypt)
                    ->first();
        
        $id_alat_mesin              = encryptId($data->id_alat_mesin ?? '', $key_akses);
        $tgl_maintenance            = $data->tgl_maintenance ?? '';
        $id_jenis_perbaikan         = $data->id_jenis_perbaikan ?? '';
        $nama_jenis_perbaikan       = $data->nama_jenis_perbaikan ?? '';
        $nama_kategori_perbaikan    = $data->nama_kategori ?? '';
        $petugas_pelaksana          = $data->petugas_pelaksana ?? '';
        $petugas_pelaksana_internal = ($petugas_pelaksana == 'Internal') ? 'checked' : '';
        $petugas_pelaksana_external = ($petugas_pelaksana == 'External') ? 'checked' : '';
        $id_supplier                = $data->id_supplier ?? '';
        $nama_petugas               = $data->nama_petugas ?? '';
        $keterangan                 = $data->keterangan ?? '';

        // Query untuk menampilkan data suplier
        $data_sp = DB::connection('supplier')
                    ->table('supplier')
                    ->where('id_supplier', $id_supplier)
                    ->first();

        $nama_sp = $data_sp->nama_sp ?? '';
    } else {
        $id_history_maintenance = $sanitasi_post['id'] ?? "MTNC_" . uuid(); 
        $id_alat_mesin          = $sanitasi_post['id_alat'] ?? '';
    }

   
?>
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
<?php  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

?>
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Data Maintenance</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_history_maintenance" value="<?= $id_history_maintenance ?>">
        <input type="hidden" class="form-control" name="routes" value="history-maintenance">
        <input type="hidden" class="form-control" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="form-control" name="id_alat_mesin" value="<?= $id_alat_mesin; ?>">
        <input type="hidden" class="form-control" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- Tanggal Maintenance -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Tanggal Maintenance</label>
            <div class="col-md-9">
                <input type="date" name="tgl_maintenance" class="form-control" value="<?= $tgl_maintenance ?>" required>
            </div>
        </div>

        <!-- Jenis Pekerjaan -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Jenis Perbaikan</label>
            <div class="col-md-9">
                <div class="input-group" id="modalJenisPerbaikan" style="cursor: pointer;">
                    <input type="hidden" class="form-control" name="id_jenis_perbaikan" id="idJenisPerbaikan" value="<?= $id_jenis_perbaikan ?>" readonly>
                    <input type="text" class="form-control bg-light border" id="namaJenisPerbaikan" value="<?= $nama_jenis_perbaikan ?>" readonly>
                    <span class="input-group-text"><i class="fe fe-search"></i></span>
                </div>
            </div>
        </div>

        <!-- Kategori Perbaikan -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Kategori Perbaikan</label>
            <div class="col-md-9">
                <input type="text" name="kategori_perbaikan" id="kategoriPerbaikan" class="form-control bg-light border" value="<?= $nama_kategori_perbaikan ?>" readonly>
            </div>
        </div>

        <!-- Petugas Pelaksana -->
        <div class="mb-3">
            <label class="form-label col-md-3">Petugas Pelaksana</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="petugas_pelaksana" id="internal" value="Internal" required <?= $petugas_pelaksana_internal ?>>
                <label class="form-check-label">Internal</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="petugas_pelaksana" id="external" value="External" required <?= $petugas_pelaksana_external ?>>
                <label class="form-check-label">External</label>
            </div>
        </div>

        <!-- Nama vendor -->
        <div class="mb-3 row d-none" id="vendorRow">
            <label class="form-label col-md-3">Nama Vendor</label>
            <div class="col-md-9">
                <div class="input-group" id="modalSupplier" style="cursor: pointer;">
                    <input type="hidden" class="form-control" name="id_supplier" id="idSupplier" value="<?= $id_supplier ?>" readonly>
                    <input type="text" class="form-control bg-light border" id="namaSupplier" value="<?= $nama_sp ?>" readonly>
                    <span class="input-group-text"><i class="fe fe-search"></i></span>
                </div>
            </div>
        </div>

        <!-- Nama petugas -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Petugas</label>
            <div class="col-md-9">
                <input type="text" name="nama_petugas" class="form-control" value="<?= $nama_petugas ?>" required>
            </div>
        </div>

        <!-- Keterangan Pengerjaan -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Keterangan Pengerjaan</label>
            <div class="col-md-9">
                <input type="text" name="keterangan_pengerjaan" class="form-control" value="<?= $keterangan ?>" required>
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
<!-- Function JS -->
<script src="<?= functionJs('global/text-only.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>

<script src="<?= functionJs('global/number-only.js') ?>"></script>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".kategori").selectize();
</script>

<!-- Kode untuk hide and show bagian kondisi -->
<script>
    const radios        = document.querySelectorAll('input[name="petugas_pelaksana"]');
    const vendorRow     = document.getElementById("vendorRow");
    const idSupplier    = document.getElementById("idSupplier");
    const namaSupplier  = document.getElementById("namaSupplier");

    function toggleVendorRow(selectedId) {
        if (selectedId === "external") {
            vendorRow.classList.remove("d-none");
            idSupplier.value    = '<?= $id_supplier ?? '' ?>';
        } else {
            vendorRow.classList.add("d-none");
            idSupplier.value        = '';
            namaSupplier.value      = '';
        }
    }

    // Event listener
    radios.forEach(radio => {
        radio.addEventListener("change", function() {
            toggleVendorRow(this.id);
        });
    });

    // Jalankan sekali saat halaman load (cek kondisi awal)
    const checkedRadio = document.querySelector('input[name="petugas_pelaksana"]:checked');
    if (checkedRadio) {
        toggleVendorRow(checkedRadio.id);
    }
</script>

<!-- Modal supplier -->
<div class="modal fade" id="supplier" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
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

<!-- Modal Jenis Perbaikan -->
<div class="modal fade" id="jenisPerbaikan" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Jenis Perbaikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tableJenisPerbaikan">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Nama Jenis Perbaikan</th>
                                <th class="text-center" style="width:300px;">Nama Kategori</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                require_once base_path('public/vendor/autoload.php');
                                require_once base_path('config/database/database.php');

                                $jenis_perbaikan = DB::connection('safaco')
                                                        ->table('jenis_perbaikan as jp')
                                                        ->leftJoin("kategori_perbaikan as kp", 'jp.id_kategori_perbaikan', '=', 'kp.id_kategori_perbaikan')
                                                        ->select(
                                                            'jp.id_jenis_perbaikan', 
                                                            'jp.nama_jenis_perbaikan',
                                                            'kp.nama_kategori'
                                                        )
                                                        ->where('jp.status_active', '1')
                                                        ->orderBy('jp.nama_jenis_perbaikan', 'asc')
                                                        ->get();
                            ?>
                            <?php if (!$jenis_perbaikan->isEmpty()) : ?>
                                <?php $no = 1;  foreach ($jenis_perbaikan as $jp) : ?>
                                <?php
                                    $id_jenis_perbaikan      = $jp->id_jenis_perbaikan;
                                    $nama_jenis_perbaikan    = $jp->nama_jenis_perbaikan;
                                    $nama_kategori           = $jp->nama_kategori;
                                ?>
                                    <tr>
                                        <td class="align-middle text-center"><?= $no++ ?></td>
                                        <td class="align-middle"><?= $nama_jenis_perbaikan ?></td>
                                        <td class="align-middle text-wrap"><?= $nama_kategori ?></td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn btn-primary btn-sm selectJenisPerbaikan"
                                            data-id-jp="<?= $id_jenis_perbaikan; ?>" 
                                            data-nama-jp="<?= $nama_jenis_perbaikan; ?>" 
                                            data-nama-kp="<?= $nama_kategori; ?>" 
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
    const jenisPerbaikan = new bootstrap.Modal(document.getElementById('jenisPerbaikan'), {
        backdrop: false // agar tidak menutupi modal utama
    });

    document.getElementById('modalJenisPerbaikan').addEventListener('click', () => {
        jenisPerbaikan.show();
    });

     // select Produk Mater
    $(document).on("click", ".selectJenisPerbaikan", function () {
        $("#idJenisPerbaikan").val($(this).data("id-jp"));
        $("#namaJenisPerbaikan").val($(this).data("nama-jp"));
        $("#kategoriPerbaikan").val($(this).data("nama-kp"));
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
        new DataTable('#tableSupplier, #tableJenisPerbaikan', {
            lengthChange: false
        });
    });
</script>
<!-- End Datatable Bootstraps 5 -->