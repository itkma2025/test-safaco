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

    // --- Koneksi DB Safaco---
    $db_safaco   = DB::connection('safaco');
    // Default aksi
    if (!empty($sanitasi_post['id'])) {
        $id_user = decryptId($sanitasi_post['id'], $key_akses);
        $data_user  = DB::connection('user')
                            ->table('user as us')
                            ->leftJoin('user_role as ur', 'us.id_user_role', '=', 'ur.id_user_role')
                            ->select('us.id_user', 'us.nama_user', 'us.no_hp', 'ur.nama_role')
                            ->where('us.id_user', $id_user)
                            ->first();
        $nama_operator  = $data_user->nama_user ?? '';
        $nama_role  = $data_user->nama_role   ?? '';
        $no_hp  = $data_user->no_hp ?? '';

        // Menampilkan data keahlian operator
        $data_operator = $db_safaco->table('operator')->where('id_user', $id_user)->first();
        $id_operator = $data_operator->id_operator ?? '';
        $id_keahlian = $data_operator->id_keahlian ?? '';
        $aksi = $data_operator ? 'edit' : 'create';
        $id_operator_new = $id_operator ? encryptId($id_operator, $key_akses) : 'OPR_' . uuid();

        ?>
            <!-- Plugin Selectize JS -->
            <link href="<?= vendor('selectize-js/dist/css/selectize.bootstrap5.css') ?>" rel="stylesheet" />
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Edit Data Operator</h5>  
            </div>
            <form method="POST" id="saveForm">
                <div class="modal-body">
                    <input type="hidden" class="form-control" name="id_operator" value="<?= $id_operator_new ?>">
                    <input type="hidden" class="form-control" name="id_user" value="<?= $id_user ?>">
                    <input type="hidden" class="form-control" name="routes" value="operator">
                    <input type="hidden" name="action" value="<?= encryptId($aksi, $key_akses); ?>">
                    <input type="hidden" class="w-full max-w-full input border mt-2" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <!-- Nama Operator -->
                    <div class="mb-3 row">
                        <label class="form-label col-md-3">Nama Operator</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control bg-light border" maxlength="100" value="<?= $nama_operator ?>" oninput="filterTextOnly(this)" readonly>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="form-label col-md-3">Role Operator</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control bg-light border" maxlength="100" value="<?= $nama_role ?>" oninput="filterTextOnly(this)" readonly>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="form-label col-md-3">No. HP</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control bg-light border" maxlength="100" value="<?= $no_hp ?>" oninput="filterTextOnly(this)" readonly>
                        </div>
                    </div>
                    <hr>
                    <!-- Pilihan Keahlian (Tampil jika "Ada" dipilih) -->
                    <div class="mb-3 row" id="mesinSelect">
                        <label class="form-label col-md-3">Keahlian</label>
                        <div class="col-md-9">
                            <select name="id_keahlian" class="form-select keahlian">
                                <option value="" disabled selected>Pilih Keahlian...</option>
                                <?php  
                                    $data_keahlian = $db_safaco->table('keahlian')
                                                                ->where('status_active', '1')
                                                                ->orderBy('nama_keahlian', 'asc')
                                                                ->get();
                                ?>
                                <?php foreach ($data_keahlian as $keahlian): ?>
                                    <option value="<?= $keahlian->id_keahlian ?>" <?= ($keahlian->id_keahlian == $id_keahlian) ? 'selected' : '' ?>><?= $keahlian->nama_keahlian ?></option>
                                <?php endforeach; ?>
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
                        <button type="submit" class="btn btn-info" id="submitBtn">
                            <i class="fe fe-save me-1" id="btnSimpanIcon"></i>
                            <span id="btnSimpanText">Simpan</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- jQuery -->
            <script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

            <!-- Selectize JS -->
            <script src="<?= vendor('selectize-js/dist/js/selectize.min.js') ?>"></script>

            <script>
                // Inisialisasi Selectize untuk Social Media dan Marketplace
                $(".keahlian").selectize();
            </script>

            <!-- Function JS -->
            <script src="<?= functionJs('global/text-only.js') ?>"></script>
            <script src="<?= functionJs('proses-data.js') ?>"></script>
        <?php
    } else {
        ?>
            <div>
                <h4>Data tidak ditemukan</h4>
            </div>
        <?php
    }
?>





