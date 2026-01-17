<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Kota / Kabupaten</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Data Wilayah</li>
                    <li class="breadcrumb-item active" aria-current="page">Data Kota / Kabupaten</li>
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
                            <a href="data-wilayah.php?action=export-pdf-kotakab" target='blank' class="dropdown-item rounded-1">
                                <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                            </a>
                        </li>
                        <li>
                            <a href="data-wilayah.php?action=export-excel-kotakab" class="dropdown-item rounded-1">
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
            <?php
                require_once base_path('helpers/domain.php');
                require_once base_path('public/vendor/autoload.php');
                use GuzzleHttp\Client;

                $client = new Client();
                $data_kotakab = [];
                
                // Ambil token dari cookie
                $tokenJwt = require base_path('helpers/jwt-token.php');
                $domain_wilayah = DOMAIN_WILAYAH;

                try {
                    if ($tokenJwt) {
                        $response = $client->get($domain_wilayah . 'api/data-kotakab.php', [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $tokenJwt
                            ],
                            'http_errors' => false
                        ]);

                        $statusCode = $response->getStatusCode();
                        $body = $response->getBody()->getContents();
                        $result = json_decode($body, true);

                        // Debug sementara:
                        // echo "<pre>" . htmlspecialchars($body) . "</pre>";

                        if ($statusCode === 200 && isset($result['data']) && is_array($result['data'])) {
                            $data_kotakab = $result['data'];
                        } elseif ($statusCode === 401) {
                            echo "<div class='alert alert-warning'>Token tidak valid atau sudah expired. Silakan login ulang.</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Gagal mengambil data kotakab. Status: $statusCode</div>";
                        }
                    } else {
                        echo "<div class='alert alert-warning'>Token tidak ditemukan. Silakan login terlebih dahulu.</div>";
                    }

                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    echo "<div class='alert alert-danger'>Gagal terhubung ke API. Error: " . $e->getMessage() . "</div>";
                }
            ?>
            <!-- Custom botton export and search -->
            <div class="d-flex justify-content-end">
                <div class="card w-auto">
                    <div class="input-container">
                        <input type="text" class="form-control" placeholder="Cari Data" id="cari-data">
                        <button type="button" class="text-secondary" id="resetButton">
                            <i class="bi bi-x fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- End Custom botton export and search -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="tableNoExportNew">
                    <thead>
                        <tr>
                            <th class="text-center" style="min-width: 40px;">No</th>
                            <th class="text-center" style="min-width: 200px;">Kode Kota / Kabupaten</th>
                            <th class="text-center" style="min-width: 350px;">Nama Kota / Kabupaten</th>
                            <th class="text-center" style="min-width: 300px;">Nama Provinsi</th>
                            <th class="text-center" style="min-width: 120px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data_kotakab)) : ?>
                            <?php $no = 1; foreach ($data_kotakab as $row) : ?>
                                <tr>
                                    <td class="align-middle text-center text-nowrap"><?= $no++ ?></td>
                                    <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row['kode_kota_kab']) ?></td>
                                    <td class="align-middle text-wrap"><?= htmlspecialchars($row['nama_kota_kab']) ?></td>
                                    <td class="align-middle text-wrap text-center"><?= htmlspecialchars($row['nama_provinsi']) ?></td>
                                    <td class="align-middle text-center text-wrap"><?= htmlspecialchars($row['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-start" id="totalData"></div>
            <!-- Custom pagination -->
            <nav>
                <ul class="pagination" id="customPagination">
                <!-- Pagination items will be inserted here by JavaScript -->
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>
<?php
push_script('
    <!-- DataTables Bootstrap 5 -->
    <script src="../../vendor/dataTables/js/dataTables.js"></script>
    <script src="../../vendor/dataTables/js/dataTables.bootstrap5.js"></script>
    <script src="../../vendor/dataTables/js/dataTables.buttons.js"></script>
    <script src="../../vendor/dataTables/js/buttons.bootstrap5.js"></script>
    <script src="../../vendor/dataTables/js/jszip.min.js"></script>
    <script src="../../vendor/dataTables/js/pdfmake.min.js"></script>
    <script src="../../vendor/dataTables/js/vfs_fonts.js"></script>
    <script src="../../vendor/dataTables/js/buttons.html5.min.js"></script>
    <script src="../../vendor/dataTables/js/buttons.print.min.js"></script>
    <script src="../../vendor/dataTables/js/buttons.colVis.min.js"></script>
    <script src="../../assets/js-custom/datatable-custom.js"></script>
    <!-- End Datatable Bootstraps 5 -->
');
?>
