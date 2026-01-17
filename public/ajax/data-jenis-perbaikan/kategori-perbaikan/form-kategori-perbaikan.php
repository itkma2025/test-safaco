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
    $aksi = 'create';
    $label = 'Tambah';
    $nama_kategori  = '';
    $deskripsi      = '';
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_kategori_perbaikan_decrypt  = decryptId($sanitasi_post['id'], $key_akses);
        $data_kategori = DB::connection('safaco')
                            ->table('kategori_perbaikan')
                            ->where('id_kategori_perbaikan', '=', $id_kategori_perbaikan_decrypt)
                            ->first();
        $nama_kategori  = $data_kategori->nama_kategori;
        $deskripsi  = $data_kategori->deskripsi;
    }

    $id_kategori_perbaikan = $sanitasi_post['id'] ?? "KATPER_" . uuid();

   
?>
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Kategori Perbaikan</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_kategori_perbaikan" value="<?= $id_kategori_perbaikan ?>">
        <input type="hidden" class="form-control" name="routes" value="kategori-perbaikan">
        <input type="hidden" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- Nama Kategori -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Kategori Perbaikan</label>
            <div class="col-md-9">
                <input type="text" name="nama_kategori" class="form-control" maxlength="100" oninput="filterTextOnly(this)" value="<?= $nama_kategori ?>" required>
            </div>
        </div>
        <!-- Deskripsi -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Deskripsi</label>
            <div class="col-md-9">
                <textarea class="deskripsi" name="deskripsi" id="deskripsi" maxlength="2000"><?= $deskripsi ?></textarea>
                <div id="charCount"></div>
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
<script src="<?= functionJs('global/CKEditor-deskripsi.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>