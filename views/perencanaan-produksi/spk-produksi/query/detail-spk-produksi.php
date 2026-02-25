<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}

require_once base_path('helpers/domain.php');
require_once base_path('config/database/database.php');
require_once base_path('public/vendor/autoload.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once base_path('public/api/get-user.php');

use Illuminate\Database\Capsule\Manager as DB;

// Ambil ID spk
$id = htmlspecialchars(decryptId($_GET['id'], $key_akses));

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');
$db_kat_prod = DB::connection('kat_produk');

// --- Ambil keyword pencarian ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Konfigurasi pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10;
$offset  = ($page - 1) * $limit;

// Menampilkan detail spk
$data_spk = $db_safaco->table('spk_produksi as sp')
    ->leftJoin('jenis_produksi as jp', 'sp.id_jenis_produksi', '=', 'jp.id_jenis_produksi')
    ->leftJoin('jenis_pengerjaan as jn', 'sp.id_jenis_pengerjaan', '=', 'jn.id_jenis_pengerjaan')
    ->select('sp.*', 'jp.nama_jenis_produksi', 'jn.nama_jenis_pengerjaan')
    ->where('sp.id_spk_produksi', '=', $id)
    ->first();


// Menampilkan detail produk
$queryDetail = $db_safaco->table('details_produksi as dp')
    ->leftJoin('produk_satuan as ps', 'dp.id_produk', '=', 'ps.id_produk')
    ->leftJoin('produk_set as pst', 'dp.id_produk', '=', 'pst.id_produk_set')
    ->selectRaw("
        dp.id_details_produksi,
        dp.id_spk_produksi,
        dp.qty_plan,
        dp.keterangan,
        COALESCE(ps.kode_produk, pst.kode_produk_set) as kode_produk,
        COALESCE(ps.nama_produk, pst.nama_produk_set) as nama_produk,
        COALESCE(ps.id_kategori_produk, pst.id_kategori_produk) as id_kategori_produk
    ")
    ->where('dp.id_spk_produksi', '=', $id);


if ($search !== '') {
    $queryDetail->where(function($q) use ($search) {
        $q->where('kode_produk', 'like', "%{$search}%")
          ->orWhere('nama_produk', 'like', "%{$search}%");
    });
}

// --- Hitung total & pages ---
$total   = $queryDetail->count();
$pages   = ceil($total / $limit);

// --- Ambil list produk untuk halaman ini ---
$produkList = $queryDetail
    ->offset($offset)
    ->limit($limit)
    ->orderBy('nama_produk', 'asc')
    ->get();

// --- Ambil relasi kategori & merk ---
$idKategori = $produkList->pluck('id_kategori_produk')->unique()->toArray();
$kategoriList = $db_kat_prod->table('tb_kat_produk as tkp')
    ->leftJoin('tb_merk as mr', 'tkp.id_merk', '=', 'mr.id_merk')
    ->whereIn('tkp.id_kat_produk', $idKategori)
    ->select('tkp.id_kat_produk', 'tkp.nama_kategori', 'tkp.no_izin_edar', 'mr.nama_merk')
    ->get()
    ->keyBy('id_kat_produk');

// --- Gabungkan produk dengan kategori ---
$data_produk = $produkList->map(function($item) use ($kategoriList) {
    $kat = $kategoriList->get($item->id_kategori_produk);

    return (object)[
        'id_details_produksi' => $item->id_details_produksi,
        'id_spk_produksi'     => $item->id_spk_produksi,
        'kode_produk'         => $item->kode_produk,
        'nama_produk'         => $item->nama_produk,
        'nama_kategori'       => $kat->nama_kategori ?? null,
        'no_izin_edar'        => $kat->no_izin_edar ?? null,
        'qty_plan'            => $item->qty_plan,
        'keterangan'          => $item->keterangan,
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
