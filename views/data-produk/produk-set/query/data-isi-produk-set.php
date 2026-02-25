<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Cek login ---
if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}

// --- Load dependencies ---
require_once base_path('helpers/domain.php');
require_once base_path('config/database/database.php');
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php'); 
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

use Illuminate\Database\Capsule\Manager as DB;

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');
$db_kat_prod = DB::connection('kat_produk');

// --- Ambil keyword pencarian ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Konfigurasi pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10;
$offset  = ($page - 1) * $limit;

// --- Query dasar produk ---
$queryProduk = $db_safaco->table('isi_produk_set as ips')
                ->leftJoin('produk_satuan as ps', 'ips.id_produk', '=', 'ps.id_produk')
                ->select('ips.id_isi_produk_set', 'ips.id_produk_set', 'ps.kode_produk', 'ps.nama_produk', 'ips.qty', 'ps.id_kategori_produk', 'ps.harga');
if ($search !== '') {
    $queryProduk->where(function($q) use ($search) {
        $q->where('ps.kode_produk', 'like', "%{$search}%")
          ->orWhere('ps.nama_produk', 'like', "%{$search}%");
    });
}

// --- Hitung total & pages ---
$total   = $queryProduk->count();
$pages   = ceil($total / $limit);

// --- Ambil list produk untuk halaman ini ---
$produkList = $queryProduk
                ->offset($offset)
                ->limit($limit)
                ->orderBy('ps.nama_produk', 'asc')
                ->get();

// --- Ambil relasi kategori & merk ---
$idKategori = $produkList->pluck('id_kategori_produk')->unique()->toArray();
$kategoriList = $db_kat_prod->table('tb_kat_produk as tkp')
                            ->leftJoin('tb_merk as mr', 'tkp.id_merk', '=', 'mr.id_merk')
                            ->whereIn('tkp.id_kat_produk', $idKategori)
                            ->select('tkp.id_kat_produk', 'tkp.no_izin_edar', 'tkp.jenis_nie', 'mr.nama_merk')
                            ->get()
                            ->keyBy('id_kat_produk');

// --- Gabungkan produk dengan kategori ---
$data_produk = $produkList->map(function($item) use ($kategoriList) {
    $kat = $kategoriList->get($item->id_kategori_produk);

    return (object)[
        'id_isi_produk_set'     => $item->id_isi_produk_set,
        'id_produk_set'         => $item->id_produk_set,
        'kode_produk'           => $item->kode_produk,
        'nama_produk'           => $item->nama_produk,
        'jenis_nie'             => $kat->jenis_nie ?? null,
        'no_izin_edar'          => $kat->no_izin_edar ?? null,
        'nama_merk'             => $kat->nama_merk ?? null,
        'harga'                 => $item->harga,
        'qty'                   => $item->qty,
    ];
});

// --- Info pagination untuk view ---
$pagination = [
    'page'  => $page,
    'limit' => $limit,
    'total' => $total,
    'pages' => $pages
];
?>
