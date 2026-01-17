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
    $aksi                   = 'create';
    $label                  = 'Tambah';
    $id_kategori_perbaikan  = '';
    $nama_jenis_perbaikan   = '';
    $deskripsi      = '';
    if (!empty($sanitasi_post['id'])) {
        $aksi = 'edit';
        $label = 'Edit';
        $id_jenis_perbaikan_decrypt  = decryptId($sanitasi_post['id'], $key_akses);
        $data_jenis = DB::connection('safaco')
                            ->table('jenis_perbaikan')
                            ->select(
                                'id_jenis_perbaikan',
                                'nama_jenis_perbaikan',
                                'id_kategori_perbaikan'
                            
                            )
                            ->where('id_jenis_perbaikan', '=', $id_jenis_perbaikan_decrypt)
                            ->first();
        $nama_jenis_perbaikan   = $data_jenis->nama_jenis_perbaikan;
        $id_kategori_perbaikan  = $data_jenis->id_kategori_perbaikan;
    }

    $id_jenis_perbaikan = $sanitasi_post['id'] ?? "JENPER_" . uuid(); 

   
?>
<!-- Plugin Selectize JS -->
<link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />
<div class="modal-header">
    <h5 class="modal-title w-100 text-center" id="staticBackdropLabel"><?= $label ?> Jenis Perbaikan</h5>
</div>
<form method="POST" id="saveForm">
    <div class="modal-body">
        <input type="hidden" class="form-control" name="id_jenis_perbaikan" value="<?= $id_jenis_perbaikan ?>">
        <input type="hidden" class="form-control" name="routes" value="jenis-perbaikan">
        <input type="hidden" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
        <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <!-- Nama Jenis -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Jenis Perbaikan</label>
            <div class="col-md-9">
                <input type="text" name="nama_jenis_perbaikan" class="form-control" maxlength="100" oninput="filterTextOnly(this)" value="<?= $nama_jenis_perbaikan ?>" required>
            </div>
        </div>
        <!-- Deskripsi -->
        <div class="mb-3 row">
            <label class="form-label col-md-3">Nama Kategori Perbaikan</label>
            <div class="col-md-9">
                <select name="id_kategori_perbaikan" class="form-select kategori">
                    <option value="">Pilih...</option>
                    <?php  
                        $data_kategori = DB::connection('safaco')
                                            ->table('kategori_perbaikan')
                                            ->select(
                                                'id_kategori_perbaikan',
                                                'nama_kategori'
                                            )
                                            ->get();
                    ?>
                    <?php foreach($data_kategori as $kat): ?>
                        <?php  
                            $id_kategori = $kat->id_kategori_perbaikan;
                            $nama_kategori = $kat->nama_kategori;    
                        ?>
                        <option value="<?= $id_kategori ?>" <?= ($id_kategori == $id_kategori_perbaikan) ? 'selected' : '' ?>>
                            <?= $nama_kategori ?>
                        </option>
                    <?php endforeach ?>
                </select>
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
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<!-- Function JS -->
<script src="<?= functionJs('global/text-only.js') ?>"></script>
<script src="<?= functionJs('proses-data.js') ?>"></script>

<script src="<?= functionJs('global/number-only.js') ?>"></script>

<!-- Selectize JS -->
<script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

<script>
    // Inisialisasi Selectize untuk Social Media dan Marketplace
    $(".kategori").selectize();
</script>