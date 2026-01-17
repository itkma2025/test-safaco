<!-- Custom CSS -->
<link rel="stylesheet" href="<?= asset('custom-css/custom-css.css') ?>">
<!-- Sweet Alert -->
<link rel="stylesheet" href="<?= vendor('sweet-alert/dist/sweetalert2.min.css') ?>">
<script src="<?= vendor('sweet-alert/dist/sweetalert2.all.min.js') ?>"></script>
<!-- FancyBox CSS -->
<link rel="stylesheet" href="<?= vendor('fancybox/fancybox.css') ?>">
<style>
    #imgWrapper img {
        max-width: 150px;
        margin: 5px;
        cursor: pointer;
        border-radius: 5px;
        transition: transform 0.2s;
    }
    #imgWrapper img:hover {
        transform: scale(1.05);
    }

    /* Atur ukuran maksimal untuk Fancybox */
    .fancybox__container {
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important; /* Fancybox selalu di depan */
    }

    .img-preview {
        height: 300px !important; /* Sesuaikan tinggi sesuai kebutuhan */
        width: 300px !important; /* Sesuaikan lebar sesuai kebutuhan */
    }
</style>
<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3 mt-2">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Data Stock Produk</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="dashboard.php"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Stock Produk Karsa</li>
                    <li class="breadcrumb-item active" aria-current="page">Stock Produk Satuan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <?php
            require_once base_path('helpers/domain.php');
            require_once base_path('public/vendor/autoload.php');
            use GuzzleHttp\Client; 

            $client = new Client();
            $data_produk = [];

            // Ambil token dari cookie
            $tokenJwt = require base_path('helpers/jwt-token.php');
            $domain_inventory_kma = DOMAIN_INVENTORY_KMA;

            // Ambil parameter dari URL
            $search = $_GET['search'] ?? '';
            $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit  = 10;

            $pagination = [
                'total' => 0,
                'page'  => $page,
                'limit' => $limit,
                'pages' => 1
            ];

            try {
                if ($tokenJwt) {
                    $response = $client->get($domain_inventory_kma . 'api/data-produk.php', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $tokenJwt
                        ],
                        'query' => [
                            'limit'  => $limit,
                            'page'   => $page,
                            'search' => $search
                        ],
                        'http_errors' => false
                    ]);

                    $statusCode = $response->getStatusCode();
                    $body = $response->getBody()->getContents();
                    $result = json_decode($body, true);

                    // Debug sementara:
                    // echo "<pre>" . htmlspecialchars($body) . "</pre>";

                    if ($statusCode === 200 && isset($result['data'])) {
                        $data_produk = $result['data'];
                        if (isset($result['pagination'])) {
                            $pagination = $result['pagination'];
                        }
                    } elseif ($statusCode === 401) {
                        echo "<div class='alert alert-warning'>Token tidak valid atau sudah expired. Silakan login ulang.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Gagal mengambil data produk. Status: $statusCode</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning'>Token tidak ditemukan. Silakan login terlebih dahulu.</div>";
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                echo "<div class='alert alert-danger'>Gagal terhubung ke API. Error: " . $e->getMessage() . "</div>";
            }
        ?>

        <div class="card-body" style="min-height:70vh">
            <!-- Search & button -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted medium d-flex gap-2 flex-wrap">
                    <a href="#" class="btn btn-outline-secondary btn-mobile active">
                        <i class="fe fe-box me-1"></i>Produk Satuan
                    </a>
                    <a href="stock-produk.php?action=produk-karsa-reg-set" class="btn btn-outline-secondary btn-mobile">
                        <i class="fe fe-box me-1"></i>Produk Set
                    </a>
                </div>

                <div class="card w-auto mt-3">
                    <div class="input-container position-relative">
                        <div class="input-group">
                            <input type="text" class="form-control pe-5" placeholder="Cari Data" id="cari-data" value="<?= htmlspecialchars($search) ?>" aria-label="Cari Data" style="border-right: none !important;">
                            
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

            <!-- Table Produk -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="min-width: 40px;">No</th>
                            <th class="text-center" style="min-width: 100px;">Gambar</th>
                            <th class="text-center" style="min-width: 150px;">Kode Produk</th>
                            <th class="text-center" style="min-width: 300px;">Nama Produk</th>
                            <th class="text-center" style="min-width: 200px;">Merk</th>
                            <th class="text-center" style="min-width: 200px;">Harga</th>
                            <th class="text-center" style="min-width: 120px;">Stock</th>
                            <th class="text-center" style="min-width: 120px;">Level Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php require base_path('public/function-php/level-stock.php'); ?>
                        <?php if (!empty($data_produk)) : ?>
                            <?php $no = ($pagination['page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($data_produk as $row) : ?>
                                <?php
                                    $id_produk      = $row['id_produk'];
                                    $kode_produk    = $row['kode_produk'];
                                    $nama_produk    = $row['nama_produk'];
                                    $harga          = $row['harga'];
                                    $nama_merk      = $row['nama_merk'];
                                    $stock          = $row['stock'];
                                    $min_stock      = $row['min_stock'];
                                    $max_stock      = $row['max_stock'];
                                    $gambar         = $row['gambar'];
                                    $gambarPathReg  = $domain_inventory_kma . 'gambar/upload-produk-reg/' . $gambar;
                                    $gambarPathSet  = $domain_inventory_kma . 'assets/img/no_img.jpg';

                                    // Cek apakah file reguler ada
                                    if (@getimagesize($gambarPathReg)) {
                                        $gambarUrl = $gambarPathReg;
                                    } else {
                                        $gambarUrl = $gambarPathSet;
                                    }

                                    $stockData = StockStatus::getStatus($row['stock'], $row['min_stock'], $row['max_stock']);
                                ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle text-center">
                                        <a href="<?= htmlspecialchars($gambarUrl) ?>" data-fancybox class="file-name">
                                            <img src="<?= htmlspecialchars($gambarUrl) ?>" alt="Gambar Produk" class="img-thumbnail" width="100">
                                        </a>
                                    </td>
                                    <td class="align-middle text-center"><?= htmlspecialchars($kode_produk) ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($nama_produk) ?></td>
                                    <td class="align-middle text-center"><?= htmlspecialchars($nama_merk) ?></td>
                                    <td class="align-middle text-end"><?= number_format($harga, 0, '.', '.') ?></td>
                                    <td class="align-middle text-end text-nowrap">
                                        <div class="<?= $stockData['textColor'] ?> p-2" 
                                            style="background-color: <?= $stockData['backgroundColor'] ?>;">
                                            <?= $stockData['formattedStock'] ?>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-nowrap">
                                         <?php echo "<div>" . $stockData['status'] . "</div>"; ?>
                                    </td>
                                   
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="9" class="text-center text-muted">Data produk tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination['total'] > 0): ?>
                <?php
                    $start = ($pagination['page'] - 1) * $pagination['limit'] + 1;
                    $end   = min($pagination['page'] * $pagination['limit'], $pagination['total']);
                ?>
                <div class="text-muted medium mt-2">
                    Showing <?= $start ?> to <?= $end ?> of <?= $pagination['total'] ?> entries
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <div class="mt-1">
                <?php if ($pagination['pages'] > 1): ?>
                    <?php
                        $visible_pages = 5;
                        $current_page  = $pagination['page'];
                        $total_pages   = $pagination['pages'];

                        $block = floor(($current_page - 1) / $visible_pages);
                        $start_page = $block * $visible_pages + 1;
                        $end_page   = min($start_page + $visible_pages - 1, $total_pages);

                        $base_url = '?action=produk-karsa-reg';
                        if (!empty($search)) {
                            $base_url .= '&search=' . urlencode($search);
                        }
                    ?>
                    <nav>
                        <ul class="pagination justify-content-end">
                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $base_url ?>&page=<?= max(1, $current_page - 1) ?>">&laquo;</a>
                            </li>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $base_url ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $base_url ?>&page=<?= min($total_pages, $current_page + 1) ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php ob_end_flush(); ?>
<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

<!-- Fancybox -->
<script src="<?= vendor('fancybox/fancybox.umd.js') ?>"></script>

<!-- Kode untuk search data -->
<script>
    document.getElementById('cari-data').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const keyword = this.value.trim();
            const params = new URLSearchParams(window.location.search);
            params.set('search', keyword);
            params.set('page', 1);
            window.location.href = window.location.pathname + '?' + params.toString();
        }
    });

    const input = document.getElementById('cari-data');
    const clearBtn = document.getElementById('btn-clear');
    const searchBtn = document.getElementById('btn-search');
    const searchValue = '<?php echo $search ?>';

    // Tampilkan/hilangkan tombol X saat input berubah
    input.addEventListener('input', function () {
        if (this.value.trim() !== '') {
            clearBtn.style.display = 'flex';
        } else {
            clearBtn.style.display = 'none';
        }
    });

    // Klik tombol X: kosongkan input
    clearBtn.addEventListener('click', function () {
        input.value = '';
        input.focus();
        clearBtn.style.display = 'none';
    });

    // Saat klik tombol cari
    searchBtn.addEventListener('click', function () {
        const keyword = input.value.trim(); // ambil dari input, bukan dari tombol
        const params = new URLSearchParams(window.location.search);
        params.set('search', keyword);
        params.set('page', 1);
        window.location.href = window.location.pathname + '?' + params.toString();
    });

    if(searchValue !== ''){
        clearBtn.style.display = 'flex';
        // Saat tombol X diklik
        clearBtn.addEventListener('click', function () {
            window.location.href = 'stock-produk.php?action=produk-karsa-reg';
        });
    } else {
        clearBtn.style.display = 'none';
    }
</script>


