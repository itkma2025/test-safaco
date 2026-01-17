<?php  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../../config/config.php';
    require_once base_path('public/vendor/autoload.php');
    require_once base_path('public/function-php/uuid.php');
    require_once base_path('public/function-php/csrf-token.php');
    require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }

    $id_kategori = "KAT_JUAL_" . uuid();

    if (isset($sanitasi_post['id'])) {
        if($sanitasi_post['id'] != 'add'){
            header("location: 404.php");
            exit;
        }
    }
?>
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Tambah Data Kategori Penjualan</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_kategori_penjualan" value="<?= $id_kategori ?>">
        <input type="hidden" class="form-control" name="routes" value="kategori-penjualan">
        <input type="hidden" name="action" value="<?= encryptId('create', $key_akses); ?>">
        <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Kategori</label>
            <div class="col-md-9">
                <input type="text" name="kategori_penjualan" class="form-control" maxlength="30" oninput="filterTextOnly(this)" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="form-label col-md-3">Min Stock</label>
            <div class="col-md-9">
                <input type="text" name="min_stock" class="form-control" maxlength="9" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="form-label col-md-3">Max Stock</label>
            <div class="col-md-9">
                <input type="text" name="max_stock" class="form-control" maxlength="9" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="form-label col-md-3">Min Stock Ready</label>
            <div class="col-md-9">
                <input type="text" name="min_stock_ready" class="form-control" maxlength="9" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="form-label col-md-3">Max Stock Ready</label>
            <div class="col-md-9">
                <input type="text" name="max_stock_ready" class="form-control" maxlength="9" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
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
<!-- Function JS -->
<script src="<?= functionJs('global/text-only.js') ?>"></script>
<script src="<?= functionJs('global/number-only.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>