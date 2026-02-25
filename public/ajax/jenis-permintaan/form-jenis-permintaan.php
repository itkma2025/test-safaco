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
    $nama_kategori  = '';
    $deskripsi      = '';
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_jenis_permintaan_decrypt  = decryptId($sanitasi_post['id'], $key_akses);
        $data_jenis_permintaan = DB::connection('safaco')
                            ->table('jenis_permintaan')
                            ->where('id_jenis_permintaan', '=', $id_jenis_permintaan_decrypt)
                            ->first();
        $nama_kategori  = $data_jenis_permintaan->nama_jenis_permintaan;
    }

    $id_jenis_permintaan = $sanitasi_post['id'] ?? "JEN_PERMINTAAN_" . uuid();
   
?>
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Jenis Permintaan</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_jenis_permintaan" value="<?= $id_jenis_permintaan ?>">
        <input type="hidden" class="form-control" name="routes" value="jenis-permintaan">
        <input type="hidden" class="form-control" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="form-control" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- Nama Kategori -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Jenis Permintaan</label>
            <div class="col-md-9">
                <input type="text" name="nama_jenis_permintaan" class="form-control" maxlength="100" oninput="filterTextOnly(this)" value="<?= $nama_kategori ?>" required>
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
                <span id="btnSimpanText">Simpan</span>
            </button>
        </div>
    </div>
</form>
<script src="<?= functionJs('proses-data.js') ?>"></script>