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
    $id_jadwal_kerja = null;
    $nama_jam_kerja  = null;
    $jam_mulai       = null;
    $jam_akhir       = null;
    $tipe_jam_kerja  = null;
    $jamker_normal   = null;
    $jamker_lembur   = null;
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_jadwal_kerja = decryptId($sanitasi_post['id'], $key_akses);
        $data_jam_kerja  = DB::connection('safaco')->table('jadwal_kerja')->where('id_jadwal_kerja', $id_jadwal_kerja)->first();
        $nama_jam_kerja  = $data_jam_kerja->nama_jam_kerja ?? '';
        $jam_mulai       = date('H:i', strtotime($data_jam_kerja->jam_mulai ?? ''));
        $jam_akhir       = date('H:i', strtotime($data_jam_kerja->jam_akhir ?? ''));
        $tipe_jam_kerja  = $data_jam_kerja->tipe_jam_kerja ?? '';
        $jamker_normal   = ($tipe_jam_kerja == 'normal') ? 'selected' : '';
        $jamker_lembur   = ($tipe_jam_kerja == 'lembur') ? 'selected' : '';

    }
    $id_jadwal_kerja = $sanitasi_post['id'] ?? "JK_" . uuid();

   
?>
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Jadwal Kerja</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_jadwal_kerja" value="<?= $id_jadwal_kerja ?>">
        <input type="hidden" class="form-control" name="routes" value="jadwal-kerja">
        <input type="hidden" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Jam Kerja</label>
            <div class="col-md-9">
                <input type="text" name="nama_jam_kerja" class="form-control" maxlength="100" value="<?= $nama_jam_kerja ?>" oninput="filterTextOnly(this)" required>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="form-label col-md-3">Jam Mulai</label>
            <div class="col-md-9">
                <input type="time" class="form-control" name="jam_mulai" value="<?= $jam_mulai ?>" required>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="form-label col-md-3">Jam Akhir</label>
            <div class="col-md-9">
                <input type="time" class="form-control" name="jam_akhir" value="<?= $jam_akhir ?>" required>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="form-label col-md-3">Tipe Jam Kerja</label>
            <div class="col-md-9">
                <select name="tipe_jam_kerja" class="form-select" required>
                    <option value="">Pilih...</option>
                    <option value="normal" <?= $jamker_normal ?>>Normal</option>
                    <option value="lembur" <?= $jamker_lembur ?>>Lembur</option>
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
            <button type="submit" class="btn btn-info" id="submitBtnvv">
                <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                <span id="btnSimpanText">&nbsp; Simpan</span>
            </button>
        </div>
    </div>
</form>

<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

<script src="<?= functionJs('proses-data.js') ?>"></script>
<!-- Function JS -->
<script src="<?= functionJs('global/text-only.js') ?>"></script>




