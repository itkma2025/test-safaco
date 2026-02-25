<?php  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['id_user'])) {
        header("location: 404.php");
        exit;
    }

    require_once __DIR__ . '/../../../../config/config.php';
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

    if (isset($sanitasi_post['id_spk'])) {
        $id_spk     = $sanitasi_post['id_spk'];
        $no_spk     = $sanitasi_post['no_spk'];
        $referer    = $sanitasi_post['referer'];
        ?>
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Konfirmasi Proses Produksi</h5>
            </div>
            
            <div class="text-center p-4">
                <h5 class="mb-4 text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Apakah Anda yakin ingin memproses spk produksi <?= $no_spk ?> ?
                </h5>
                <form method="POST" id="saveForm">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name=" id_spk_produksi" value="<?= $id_spk; ?>">
                        <input type="hidden" class="form-control" name="routes" value="spk-produksi">
                        <input type="hidden" class="form-control" name="referer" value="<?= $referer ?>">
                        <input type="hidden" class="form-control" name="action" value="<?= encryptId('proses_produksi', $key_akses); ?>">
                        <input type="hidden" class="form-control" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    </div>
                    <!-- Honeypot Field: Tersembunyi dari Pengguna -->
                    <div style="display:none;">
                        <label for="honeypot">Honeypot (Jangan Diisi):</label>
                        <input type="text" id="honeypot" name="honeypot">
                        </div>
                        <div class="modal-footer">
                            <div class="text-right mt-4" id="formButtons"> 
                                <button type="button" class="btn btn-secondary me-2" onclick="location.reload()">
                                <i class="fe fe-x-circle me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                                    <span id="btnSimpanText">Ya, Proses Data</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php
    } else {
        echo '<p class="text-danger">Invalid ID.</p>';
    }
?>

<script src="<?= functionJs('proses-data.js') ?>"></script>