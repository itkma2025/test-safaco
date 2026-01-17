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
        $id_lokasi = decryptId($sanitasi_post['id'], $key_akses);
        $lokasi  = DB::connection('safaco')->table('produk_lokasi')
                    ->select(
                        'id_lokasi',
                        'nama_lokasi',
                        'lantai',
                        'area',
                        'no_rak'
                    )
                    ->where('id_lokasi', $id_lokasi)
                    ->first();
        // Header
        ?>
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Edit Data Lokasi</h5>
            </div>
        <?php
        if ($lokasi) {
           ?>
                <form method="POST" id="saveForm">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="id_lokasi" value="<?= $sanitasi_post['id']; ?>">
                        <input type="hidden" class="form-control" name="routes" value="lokasi">
                        <input type="hidden" class="form-control" name="action" value="<?= encryptId('edit', $key_akses); ?>">
                        <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Nama Lokasi</label>
                            <div class="col-md-9">
                                <input type="text" name="nama_lokasi" class="form-control" maxlength="30" oninput="filterTextOnly(this)" value="<?= $lokasi->nama_lokasi ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Lantai</label>
                            <div class="col-md-9">
                                <input type="text" name="lantai" class="form-control" maxlength="2" oninput="filterNonNumeric(this)" value="<?= $lokasi->lantai ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Area</label>
                            <div class="col-md-9">
                                <input type="text" name="area" class="form-control" maxlength="20" value="<?= $lokasi->area ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="form-label col-md-3">Nomor Rak</label>
                            <div class="col-md-9">
                                <input type="text" name="no_rak" class="form-control" maxlength="10" value="<?= $lokasi->no_rak ?>" required>
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
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<!-- Function JS -->
<script src="<?= functionJs('global/number-only.js') ?>"></script>
<script src="<?= functionJs('global/text-only.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>
