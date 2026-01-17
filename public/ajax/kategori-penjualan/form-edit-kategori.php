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

    // Library database
    use Illuminate\Database\Capsule\Manager as DB;

    // Library sanitasi input data
    require_once base_path('public/function-php/sanitasi-input.php');
    $sanitasi_post = sanitizeInput($_POST);

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }

    if (isset($sanitasi_post['id'])) {
        $id_kategori = decryptId($sanitasi_post['id'], $key_akses);
        $kategori  = DB::connection('safaco')->table('kategori_penjualan')
                    ->select(
                        'id_kategori_penjualan',
                        'kategori_penjualan',
                        'min_stock',
                        'max_stock',
                        'min_stock_ready',
                        'max_stock_ready'
                    )
                    ->where('id_kategori_penjualan', $id_kategori)
                    ->first();
        // Header
        ?>
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Edit Data Grade</h5>
            </div>
        <?php
        if ($kategori) {
           ?>
                <form method="POST" id="saveForm">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="id_kategori_penjualan" value="<?= $sanitasi_post['id']; ?>">
                        <input type="hidden" class="form-control" name="routes" value="kategori-penjualan">
                        <input type="hidden" class="form-control" name="action" value="<?= encryptId('edit', $key_akses); ?>">
                        <input type="hidden" class="form-control" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Kategori Penjualan</label>
                            <div class="col-md-9">
                                <input type="text" name="kategori_penjualan" class="form-control" maxlength="30" oninput="filterTextOnly(this)" value="<?= $kategori->kategori_penjualan ?>" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Min Stock</label>
                            <div class="col-md-9">
                                <input type="text" name="min_stock" class="form-control" maxlength="9" value="<?= number_format($kategori->min_stock, 0,'.','.') ?>" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Max Stock</label>
                            <div class="col-md-9">
                                <input type="text" name="max_stock" class="form-control" maxlength="9" value="<?= number_format($kategori->max_stock, 0,'.','.') ?>" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Min Stock Ready</label>
                            <div class="col-md-9">
                                <input type="text" name="min_stock_ready" class="form-control" maxlength="9" value="<?= number_format($kategori->min_stock_ready, 0,'.','.') ?>" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Max Stock Ready</label>
                            <div class="col-md-9">
                                <input type="text" name="max_stock_ready" class="form-control" maxlength="9" value="<?= number_format($kategori->max_stock_ready, 0,'.','.') ?>" oninput="filterNonNumeric(this); preventLeadingZero(this); formatRibuan(this);" required>
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
                                <span id="btnSimpanText">Simpan Perubahan</span>
                            </button>
                        </div>
                    </div>
                </form>
           <?php
        } else {
             echo '<h4 class="text-danger p-5 text-center">Data tidak ditemukan.</h4>';
        }
    } else {
        echo '<p class="text-danger">Invalid ID.</p>';
    }
?>

<!-- Function JS -->
<script src="<?= functionJs('global/number-only.js') ?>"></script>
<script src="<?= functionJs('global/text-only.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>
