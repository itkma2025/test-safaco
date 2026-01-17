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
    $aksi                   = 'create';
    $label                  = 'Tambah';
    $id_keahlian            = null;
    $nama_keahlian          = null;
    $status_mesin           = '';
    $id_alat_mesin_array    = [];
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_keahlian = decryptId($sanitasi_post['id'], $key_akses);
        // Query untuk menampilkan data keahlian
        $data_keahlian  = DB::connection('safaco')->table('keahlian')->where('id_keahlian', $id_keahlian)->first();
        $nama_keahlian  = $data_keahlian->nama_keahlian ?? '';
        $status_mesin  = $data_keahlian->status_mesin ?? '';
       
        // Query untuk menampilkan data alat mesin terpilih pada table keahlian alat mesin
        $id_alat_mesin_array = DB::connection('safaco')
                                ->table('keahlian_alat_mesin')
                                ->where('id_keahlian', $id_keahlian)
                                ->pluck('id_alat_mesin') // ambil hanya kolom id_alat_mesin
                                ->toArray();
        
    }
    $id_keahlian = $sanitasi_post['id'] ?? "KHL_" . uuid();

?>

<!-- Plugin Selectize JS -->
<link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Keahlian</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_keahlian" value="<?= $id_keahlian ?>">
        <input type="hidden" class="form-control" name="routes" value="keahlian">
        <input type="hidden" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- Nama Keahlian -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Keahlian</label>
            <div class="col-md-9">
                <input type="text" name="nama_keahlian" class="form-control" maxlength="100" value="<?= $nama_keahlian ?>" oninput="filterTextOnly(this)" required>
            </div>
        </div>
        <!-- Status Mesin -->
        <label class="form-label col-md-3">Status Alat / Mesin</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status_mesin" value="1" <?= $status_mesin == 1 ? 'checked' : '' ?> id="statusAda">
            <label class="form-check-label" for="statusAda">Ada</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status_mesin" value="0" <?= $status_mesin == 0 ? 'checked' : '' ?> id="statusTidakAda">
            <label class="form-check-label" for="statusTidakAda">Tidak Ada</label>
        </div>
        <!-- Pilihan Mesin (Tampil jika "Ada" dipilih) -->
        <div class="mb-3 mt-3 row d-none" id="mesinSelectDiv">
            <label class="form-label col-md-3">Mesin</label>
            <div class="col-md-9">
                <?php
                    // Ambil semua alat mesin yang aktif
                    $alat_mesin = DB::connection('safaco')
                                    ->table('alat_mesin')
                                    ->select('id_alat_mesin', 'kode_barang', 'nama_barang')
                                    ->where('status_active', '1')
                                    ->orderBy('nama_barang', 'asc')
                                    ->get();
                ?>
                <select name="id_alat_mesin[]" class="form-select alatMesin" id="mesinSelect" multiple>
                    <option value="">Pilih Mesin...</option>
                    <?php foreach ($alat_mesin as $am) : ?>
                        <option value="<?= $am->id_alat_mesin ?>" 
                            <?= in_array($am->id_alat_mesin, $id_alat_mesin_array) ? 'selected' : '' ?>>
                            <?= $am->kode_barang . ' - ' . $am->nama_barang ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<!-- Function JS -->
<script src="<?= functionJs('global/text-only.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>

<script>
    $(document).ready(function(){
        var selectize = $('#mesinSelect')[0].selectize;
        function toggleMesinSelect() {
            if ($('input[name="status_mesin"]:checked').val() == "1") {
                $('#mesinSelectDiv').removeClass('d-none');
                $('#mesinSelect #mesinSelect-selectized').attr('required', true);
            } else {
                $('#mesinSelectDiv').addClass('d-none');
                $('#mesinSelect #mesinSelect-selectized').removeAttr('required');
                selectize.clear();
            }
        }
        // Panggil saat halaman load
        toggleMesinSelect();

        // Panggil saat radio diubah
        $('input[name="status_mesin"]').on('change', function(){
            toggleMesinSelect();
        });
    });
</script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".alatMesin").selectize({
        plugins: ["remove_button"],
    });
</script>




