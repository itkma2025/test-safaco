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
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Konfirmasi Hapus Data Kategori Penjualan</h5>
            </div>
        <?php
        if ($kategori) {
           ?>
                <div class="text-center p-4">
                    <h5 class="mb-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Apakah Anda yakin ingin menghapus data kategori penjualan berikut?
                    </h5>
                    <div class="card border-0 shadow-lg mx-auto" style="max-width: 400px;">
                        <div class="card-body text-start">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>Nama kategori:</strong> <?= htmlspecialchars($kategori->kategori_penjualan) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Min Stock:</strong> <?= htmlspecialchars($kategori->min_stock) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Max Stock:</strong> <?= htmlspecialchars($kategori->max_stock) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Min Stock Ready:</strong> <?= htmlspecialchars($kategori->min_stock_ready) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Max Stock Ready:</strong> <?= htmlspecialchars($kategori->max_stock_ready) ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <form method="POST" id="saveForm">
                        <div class="modal-body">
                            <input type="hidden" class="form-control" name="id_kategori_penjualan" value="<?= $sanitasi_post['id']; ?>">
                            <input type="hidden" class="form-control" name="routes" value="kategori-penjualan">
                            <input type="hidden" class="form-control" name="action" value="<?= encryptId('delete', $key_akses); ?>">
                            <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
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
                                <button type="submit" class="btn btn-danger" id="submitBtn">
                                    <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                                    <span id="btnSimpanText">Ya, Hapus Data</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
           <?php
        } else {
             echo '<h4 class="text-danger p-5 text-center">Data tidak ditemukan.</h4>';
        }
    } else {
        echo '<p class="text-danger">Invalid ID.</p>';
    }
?>

<script src="<?= functionJs('proses-data.js') ?>"></script>