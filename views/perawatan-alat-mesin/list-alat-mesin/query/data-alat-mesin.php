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

// Koneksi database
require_once base_path('config/database/database.php');

// --- Load dependencies ---
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

use Illuminate\Database\Capsule\Manager as DB;

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');
$db_kat_prod = DB::connection('kat_produk');
$db_supplier = DB::connection('supplier');

// --- Ambil keyword pencarian ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Konfigurasi pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
// Validasi: wajib angka positif
if (!ctype_digit((string) $page) || (int)$page < 1) {
    $page = 1; // fallback ke halaman 1
} else {
    $page = (int) $page;
}
$limit   = 10;
$offset  = ($page - 1) * $limit;

// --- Query dasar alat dan mesin ---
$queryAlatMesin = $db_safaco->table('alat_mesin as am')
                             ->join('produk_lokasi as pl', 'am.id_lokasi', '=', 'pl.id_lokasi')
                             ->select(
                                'am.id_alat_mesin', 
                                'am.kode_barang', 
                                'am.nama_barang', 
                                'am.jenis_barang',
                                'am.tgl_pembelian',
                                'am.kondisi',
                                'am.status_active',
                                'am.id_merk',
                                'am.id_supplier',
                                'pl.nama_lokasi'
                            )
                             ->orderBy('am.nama_barang', 'asc');
// --- Filter pencarian ---
if ($search !== '') {
    $queryAlatMesin->where(function($q) use ($search) {
        $q->where('am.kode_barang', 'like', "%{$search}%")
          ->orWhere('am.nama_barang', 'like', "%{$search}%");
    });
}

// --- Hitung total & pages ---
$total   = $queryAlatMesin->count();
$pages   = ceil($total / $limit);

// --- Ambil list alat dan mesin untuk halaman ini ---
$alatMesinList = $queryAlatMesin
                ->offset($offset)
                ->limit($limit)
                ->orderBy('nama_barang', 'asc')
                ->get();

// --- Ambil relasi merk ---
$idMerk = $alatMesinList->pluck('id_merk')->unique()->toArray();
$merkList = $db_kat_prod->table('tb_merk as mr')
                            ->whereIn('mr.id_merk', $idMerk)
                            ->select('mr.id_merk', 'mr.nama_merk')
                            ->get()
                            ->keyBy('id_merk');


// --- Ambil relasi supplier ---
$idSupplier = $alatMesinList->pluck('id_supplier')->unique()->toArray();
$supplierList = $db_supplier->table('supplier as s')
                            ->whereIn('s.id_supplier', $idSupplier)
                            ->select('s.id_supplier', 's.nama_sp')
                            ->get()
                            ->keyBy('id_supplier');

// --- Gabungkan produk dengan kategori ---
$data_alat_mesin = $alatMesinList->map(function($item) use ($merkList, $supplierList) {
    $merk = $merkList->get($item->id_merk);
    $supplier = $supplierList->get($item->id_supplier);

    return (object)[
        'id_alat_mesin'  => $item->id_alat_mesin,
        'kode_barang'    => $item->kode_barang,
        'nama_barang'    => $item->nama_barang,
        'jenis_barang'   => $item->jenis_barang,
        'tgl_pembelian'  => $item->tgl_pembelian,
        'kondisi'        => $item->kondisi,
        'nama_lokasi'    => $item->nama_lokasi,
        'status_active'  => $item->status_active,
        'nama_merk'      => $merk->nama_merk ?? null,
        'nama_sp'        => $supplier->nama_sp ?? null,
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
