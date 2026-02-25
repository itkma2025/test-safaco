<?php  
   if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../../../config/config.php';
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
    $aksi                = 'create';
    $label               = 'Tambah';
    $nama_spk            = '';
    $deskripsi           = '';
    $id_jenis_produksi   = '';
    $id_jenis_pengerjaan = '';
    $prioritas_produksi  = '';
    $catatan             = '';

    $data_jenis_produksi    = DB::connection('safaco')->table('jenis_produksi')->get();
    $data_jenis_pengerjaan  = DB::connection('safaco')->table('jenis_pengerjaan')->get();
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_spk_produksi_decrypt  = decryptId($sanitasi_post['id'], $key_akses);
        $data_spk = DB::connection('safaco')
                        ->table('spk_produksi as sp')
                        ->leftJoin('jenis_produksi as jp', 'sp.id_jenis_produksi', '=', 'jp.id_jenis_produksi')
                        ->leftJoin('jenis_pengerjaan as jn', 'sp.id_jenis_pengerjaan', '=', 'jn.id_jenis_pengerjaan')
                        ->select('sp.*', 'jp.nama_jenis_produksi', 'jn.nama_jenis_pengerjaan')
                        ->where('sp.id_spk_produksi', '=', $id_spk_produksi_decrypt)
                        ->orderBy('sp.nama_spk', 'asc')
                        ->first();
        $no_spk                     = $data_spk->no_spk;
        $nama_spk                   = $data_spk->nama_spk;
        $tgl_spk                    = $data_spk->tgl_spk;
        $tgl_mulai                  = $data_spk->tgl_mulai;
        $tgl_akhir                  = $data_spk->tgl_akhir;
        $id_jenis_produksi          = $data_spk->id_jenis_produksi;
        $id_jenis_pengerjaan        = $data_spk->id_jenis_pengerjaan;
        $prioritas_produksi         = $data_spk->prioritas_produksi;
        $catatan                    = $data_spk->catatan;

        // Enkripsi ID
        $id_spk_produksi            = encryptId($data_spk->id_spk_produksi, $key_akses);

    } else {
        $id_spk_produksi = $sanitasi_post['id'] ?? "SPK_" . uuid();

        $data_spk    = DB::connection('safaco')->table('spk_produksi')->whereYear('tgl_spk', date('Y'))->count();
        // Kode otomatis no SPK
        $array_bln = array(1 => "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
        $bln = $array_bln[date('n')];
        $format1 = "/SPK/SKM-PJT/";
        $format2 = "/";
        $format3 = date("Y");
        $urutkan = $data_spk; // Mengambil nilai maksimum langsung dari hasil query
        $urutkan++;
        $no_spk = sprintf("%03s", $urutkan) . $format1 . $bln . $format2 . $format3;
    }
?>
<link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Data SPK Produksi</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_spk_produksi" value="<?= $id_spk_produksi ?>">
        <input type="hidden" class="form-control" name="routes" value="spk-produksi">
        <input type="hidden" class="form-control" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="form-control" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- No SPK -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">No SPK</label>
            <div class="col-md-9">
                <input type="text" name="no_spk" class="form-control bg-light" value="<?= $no_spk ?>" readonly>
            </div>
        </div>
        <!-- No SPK -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama SPK</label>
            <div class="col-md-9">
                <input type="text" name="nama_spk" class="form-control" maxlength="100" value="<?= $nama_spk ?>" required>
            </div>
        </div>
        <!-- Tgl SPK -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Tgl SPK</label>
            <div class="col-md-9">
                <input type="date" name="tgl_spk" class="form-control" maxlength="100" value="<?= $tgl_spk ?>" required>
            </div>
        </div>
        <label class="form-label">Periode Waktu Produksi</label>
        <!-- Tgl Mulai -->
        <div class="mb-3 row">
            <label class="col-md-3">Tgl Mulai</label>
            <div class="col-md-9">
                <input type="datetime-local" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>" required>
            </div>
        </div>
        <!-- Tgl Selesai -->
        <div class="mb-3 row">
            <label class="col-md-3">Tgl Akhir</label>
            <div class="col-md-9">
                <input type="datetime-local" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>" required>
            </div>
        </div>

        <!-- Jenis Produksi -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Jenis Produksi</label>
            <div class="col-md-9">
                <select name="id_jenis_produksi" class="form-control jenis_produksi" required>
                    <option value="">Pilih...</option>
                    <?php foreach ($data_jenis_produksi as $row) : ?>
                        <option value="<?= $row->id_jenis_produksi ?>" <?= ($row->id_jenis_produksi == $id_jenis_produksi) ? 'selected' : '' ?>><?= $row->nama_jenis_produksi ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Jenis Produksi -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Jenis Pengerjaan</label>
            <div class="col-md-9">
                <select name="id_jenis_pengerjaan" class="form-control jenis_pengerjaan" required>
                    <option value="">Pilih...</option>
                    <?php foreach ($data_jenis_pengerjaan as $row) : ?>
                        <option value="<?= $row->id_jenis_pengerjaan ?>" <?= ($row->id_jenis_pengerjaan == $id_jenis_pengerjaan) ? 'selected' : '' ?>><?= $row->nama_jenis_pengerjaan ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Prioritas Produksi -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Prioritas Produksi</label>
            <div class="col-md-9">
                <select name="prioritas_produksi" class="form-control" required>
                    <option value="">Pilih...</option>
                    <option value="Low" <?= ($prioritas_produksi == 'Low') ? 'selected' : '' ?>>Low</option>
                    <option value="Normal" <?= ($prioritas_produksi == 'Normal') ? 'selected' : '' ?>>Normal</option>
                    <option value="High" <?= ($prioritas_produksi == 'High') ? 'selected' : '' ?>>High</option>
                    <option value="Urgent" <?= ($prioritas_produksi == 'Urgent') ? 'selected' : '' ?>>Urgent</option>
                </select>
            </div>
        </div>

        <div class="form-label mb-3 row">
            <label class="col-md-3">Catatan</label>
            <div class="col-md-9">
                <textarea class="form-control" name="catatan"></textarea>
            </div>
        </div>

    </div>
    <!-- Honeypot Field: Tersembunyi dari Pengguna -->
    <div style="display:none;">
        <label for="honeypot">Honeypot (Jangan Diisi):</label>
        <input type="text" id="honeypot" name="honeypot">
    </div>
    <?php  
        if($aksi != 'edit'){
            ?>
                <input type="hidden" name="status_spk" id="statusField" value="Belum Dimulai">
                <div class="modal-footer justify-content-center">
                    <div class="mt-4" id="formButtons"> 
                        <button type="submit" class="btn btn-primary" id="submitBtn" data-status="Belum Dimulai">
                            <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                            <span id="btnSimpanText">Simpan Data</span>
                        </button>
                        <button type="submit" class="btn btn-info" id="submitBtnDraft" data-status="Draft">
                            <i class="fe fe-save me-1" id="btnSimpanIconDraft"></i>
                            <span id="btnSimpanTextDraft">Simpan Draft</span>
                        </button>
                        <button type="button" class="btn btn-success me-2" onclick="location.reload()">
                            <i class="fe fe-x-circle me-1"></i>Tutup
                        </button>
                    </div>
                </div>
            <?php
        } else {
            ?>
                <div class="modal-footer justify-content-center">
                    <div class="mt-4" id="formButtons">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                            <span id="btnSimpanText">Edit Data</span>
                        </button>
                        <button type="button" class="btn btn-success me-2" onclick="location.reload()">
                            <i class="fe fe-x-circle me-1"></i>Tutup
                        </button>
                    </div>
                </div>
            <?php
        }
    ?> 
</form>
 <!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".jenis_produksi, .jenis_pengerjaan").selectize();
</script>
<script src="<?= functionJs('proses-data.js') ?>"></script>
<script>
$(document).ready(function () {

    console.log('Form status handler loaded');

    $('#formButtons button[type="submit"]').on('click', function () {
        const status = $(this).data('status');

        // console.log('Button diklik');
        // console.log('Data-status dari button:', status);

        $('#statusField').val(status);

        // console.log('Value hidden statusField sekarang:', $('#statusField').val());
    });

});
</script>
