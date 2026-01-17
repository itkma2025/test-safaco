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
    require_once base_path('public/api/get-user.php');

    $userMap = getUserMap();

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
                    ->select('*')
                    ->where('id_lokasi', $id_lokasi)
                    ->first();
        // Header
        ?>
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Detail Lokasi</h5>
            </div>
        <?php
        if ($lokasi) {
            ?>
                <style>
                    .detail-lokasi dt {
                        display: flex;
                        justify-content: space-between; /* label di kiri, titik dua di kanan */
                        position: relative;
                    }

                    .detail-lokasi dt::after {
                        content: ":";
                        margin-left: 5px;
                    }

                    @media (max-width: 576px) {
                        .detail-lokasi .row dt,
                        .detail-lokasi .row dd {
                            flex: 1 1 100%;
                        }

                        /* Hilangkan titik dua di mobile */
                        .detail-lokasi dt::after {
                            content: ""; /* kosongkan titik dua */
                            margin-left: 0; /* hapus jarak yang tadi */
                        }
                    }
                </style>
                <div class="modal-body">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <dl class="row mb-0 detail-lokasi">
                                <div class="row mb-1">
                                    <dt class="col-sm-4">Nama Lokasi</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($lokasi->nama_lokasi) ?></dd>
                                </div>
                                <div class="row mb-1">
                                    <dt class="col-sm-4">Lantai</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($lokasi->lantai) ?></dd>
                                </div>
                                <div class="row mb-1">
                                    <dt class="col-sm-4">Area</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($lokasi->area) ?></dd>
                                </div>
                                <div class="row mb-1">
                                    <dt class="col-sm-4">Nomor Rak</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($lokasi->no_rak) ?></dd>
                                </div>
                                <div class="row mb-1">
                                    <dt class="col-sm-4">Created Date</dt>
                                    <dd class="col-sm-8"><?= date('d/m/Y', strtotime($lokasi->created_date)) ?></dd>
                                </div>

                                <div class="row mb-1">
                                    <dt class="col-sm-4">Created By</dt>
                                    <dd class="col-sm-8"><?= $userMap[$lokasi->created_by] ?? '-' ?></dd>
                                </div>

                                <div class="row mb-1">
                                    <dt class="col-sm-4">Updated Date</dt>
                                    <dd class="col-sm-8">
                                        <?= !empty($lokasi->updated_date) ? date('d/m/Y', strtotime($lokasi->updated_date)) : '-' ?>
                                    </dd>
                                </div>

                                <div class="row mb-1">
                                    <dt class="col-sm-4">Updated By</dt>
                                    <dd class="col-sm-8"><?= $userMap[$lokasi->updated_by] ?? '-' ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-right mt-4" id="formButtons"> 
                        <button type="button" class="btn btn-success me-2" onclick="location.reload()">
                            <i class="fe fe-x-circle me-1"></i>Tutup
                        </button>
                    </div>
                </div>
            <?php
        } else {
             echo '<h4 class="text-danger p-5 text-center">Data tidak ditemukan.</h4>';
        }
    } else {
        echo '<p class="text-danger">Invalid ID.</p>';
    }
?>