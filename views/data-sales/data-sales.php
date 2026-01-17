<?php
    require_once base_path('helpers/domain.php');
    require_once base_path('public/vendor/autoload.php');

    use GuzzleHttp\Client;

    $client = new Client();
    $data_sales = [];
    $tokenJwt = require base_path('helpers/jwt-token.php');
    $domain_instansi = DOMAIN_INSTANSI;

    // Ambil parameter dari URL
    $search = $_GET['search'] ?? '';
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit  = 10;

    $pagination = [
        'total' => 0,
        'page' => $page,
        'pages' => 1
    ];



    try {
        if ($tokenJwt) {
            $response = $client->get($domain_instansi . 'api/data-sales.php', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenJwt
                ],

                'query' => [
                    'page'      => $page,
                    'search'    => $search
                ],

                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            // Debug sementara:
            // echo "<pre>" . htmlspecialchars($body) . "</pre>";

            if ($statusCode === 200 && isset($result['data']) && is_array($result['data'])) {
                $data_sales = $result['data'];
                if (isset($result['pagination'])) {
                    $pagination = $result['pagination'];
                }
            } elseif ($statusCode === 401) {
                echo "<div class='alert alert-warning'>Token tidak valid atau sudah expired. Silakan login ulang.</div>";
            } else {
                echo "<div class='alert alert-danger'>Gagal mengambil data instansi. Status: $statusCode</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Token tidak ditemukan. Silakan login terlebih dahulu.</div>";
        }

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "<div class='alert alert-danger'>Gagal terhubung ke API. Error: " . $e->getMessage() . "</div>";
    }
?>
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Sales</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Sales</li>
                </ol>
            </nav>
        </div>
        <div class="d-block d-md-flex w-custom my-xl-auto right-content align-items-center flex-wrap">
            <div class="me-2 mb-2 w-custom w-md-auto">
                <div class="dropdown w-custom">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-white w-custom d-inline-flex align-items-center justify-content-center" data-bs-toggle="dropdown">
                        <i class="ti ti-file-export me-1"></i>File Export
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-3">
                        <li>
                            <a href="data-sales.php?action=export-pdf&search=<?= urlencode($search) ?>" target='blank' class="dropdown-item rounded-1">
                                <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                            </a>
                        </li>
                        <li>
                            <a href="data-sales.php?action=export-excel&search=<?= urlencode($search) ?>" class="dropdown-item rounded-1">
                                <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body" style="min-height:70vh">
            <!-- Search & Export -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted small">
                    <!-- Content -->
                </div>
                <div class="card w-auto">
                    <div class="input-container position-relative">
                        <div class="input-group">
                            <input type="text" class="form-control pe-5" placeholder="Cari Data"
                                id="cari-data" value="<?= htmlspecialchars($search) ?>"
                                style="border-right: none !important;">
                            
                            <!-- Tombol X (hapus) -->
                            <div class="input-group-text" id="btn-clear" style="border-left: none !important; cursor: pointer; display: none;">
                                <i class="fe fe-x"></i>
                            </div>

                            <!-- Tombol Search -->
                            <div class="input-group-text" id="btn-search" style="border-left: none !important;">
                                <i class="fe fe-search"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Custom botton export and search -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="tableNoExportNew">
                    <thead>
                        <tr>
                            <th class="text-center" style="min-width: 40px;">No</th>
                            <th class="text-center" style="min-width: 200px;">Nama Sales</th>
                            <th class="text-center" style="min-width: 250px;">Nama Instansi</th>
                            <th class="text-center" style="min-width: 180px;">Wilayah</th>
                            <th class="text-center" style="min-width: 130px;">Telepon</th>
                            <th class="text-center" style="min-width: 200px;">Email</th>
                            <th class="text-center" style="min-width: 120px;">Jenis Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data_sales)) : ?>
                            <?php $no = 1; foreach ($data_sales as $row) : ?>
                                <tr>
                                    <td class="align-middle text-center text-nowrap"><?= $no++ ?></td>
                                    <td class="align-middle text-wrap"><?= htmlspecialchars($row['nama_sales']) ?></td>
                                    <td class="align-middle text-wrap"><?= htmlspecialchars($row['nama_instansi']) ?></td>
                                    <td class="align-middle text-wrap text-center"><?= htmlspecialchars($row['nama_provinsi']) ?></td>
                                    <td class="align-middle text-wrap text-center"><?= htmlspecialchars($row['no_telp']) ?></td>
                                    <td class="align-middle text-wrap text-center"><?= htmlspecialchars($row['email_sales']) ?></td>
                                    <td class="align-middle text-nowrap text-center"><?= htmlspecialchars($row['jenis_sales']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Untuk menampilkan pagination -->
            <?php require_once base_path('public/function-php/pagination.php'); ?>
        </div>
    </div>
</div>
<!-- Function untuk search data -->
<script src="<?= functionJs('global/search-data.js') ?>"></script>

