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
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php'); 
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php'); 
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

use Illuminate\Database\Capsule\Manager as DB;

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');
$db_supplier = DB::connection('supplier');

// --- Ambil keyword pencarian ---
$search             = isset($_GET['search']) ? trim($_GET['search']) : '';
$id_alat            = isset($_GET['id_alat']) ? trim($_GET['id_alat']) : '';
$id_alat_decrypt    = decryptId($id_alat, $key_akses);

// --- Konfigurasi pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10;
$offset  = ($page - 1) * $limit;

// --- Query dasar produk ---
$query = $db_safaco->table('history_maintenance as hm')
                    ->leftJoin('jenis_perbaikan as jp', 'hm.id_jenis_perbaikan', '=', 'jp.id_jenis_perbaikan')
                    ->leftJoin('kategori_perbaikan as kp', 'jp.id_kategori_perbaikan', '=', 'kp.id_kategori_perbaikan')
                    ->where('id_alat_mesin', $id_alat_decrypt);

if (!empty($search)) {

    // ambil id supplier yang match search
    $supplierIds = $db_supplier->table('supplier')
        ->where('nama_sp', 'like', "%{$search}%")
        ->pluck('id_supplier')
        ->toArray();

    $query->where(function ($q) use ($search, $supplierIds) {
        $q->where('jp.nama_jenis_perbaikan', 'like', "%{$search}%")
          ->orWhere('kp.nama_kategori', 'like', "%{$search}%")
          ->orWhere('hm.petugas_pelaksana', 'like', "%{$search}%")
          ->orWhere('hm.nama_petugas', 'like', "%{$search}%");

        if (!empty($supplierIds)) {
            $q->orWhereIn('hm.id_supplier', $supplierIds);
        }
    });
}


// --- Hitung total & pages ---
$total   = $query->count();
$pages   = ceil($total / $limit);

// --- Ambil list produk untuk halaman ini ---
$history_maintenance = $query
                        ->offset($offset)
                        ->limit($limit)
                        ->orderBy('id_history_maintenance', 'asc')
                        ->get();

// --- Ambil jenis perbaikan & kategori perbaikan ---
$idJenisPerbaikan = $history_maintenance->pluck('id_jenis_perbaikan')->unique()->toArray();
$jenisPerbaikanList = $db_safaco->table('jenis_perbaikan as jp')
                                ->leftJoin('kategori_perbaikan as kp', 'jp.id_kategori_perbaikan', '=', 'kp.id_kategori_perbaikan')
                                ->whereIn('jp.id_jenis_perbaikan', $idJenisPerbaikan)
                                ->select( 'jp.id_jenis_perbaikan', 'jp.nama_jenis_perbaikan', 'kp.nama_kategori')
                                ->get()
                                ->keyBy('id_jenis_perbaikan');

// --- Ambil data supplier ---
$idSupplier = $history_maintenance->pluck('id_supplier')->unique()->toArray();
$supplierList = $db_supplier->table('supplier as sp')
                            ->whereIn('sp.id_supplier', $idSupplier)
                            ->select('sp.id_supplier', 'sp.nama_sp')
                            ->get()
                            ->keyBy('id_supplier');


// --- Gabungkan produk dengan kategori ---
$data_maintenance = $history_maintenance->map(function($item) use ($jenisPerbaikanList, $supplierList) {
    $kat        = $jenisPerbaikanList->get($item->id_jenis_perbaikan);
    $supplier   = $supplierList->get($item->id_supplier);

    return (object)[
        'id_history_maintenance' => $item->id_history_maintenance,
        'tgl_maintenance'        => $item->tgl_maintenance,
        'nama_jenis_perbaikan'   => $kat->nama_jenis_perbaikan ?? null,
        'nama_kategori'          => $kat->nama_kategori ?? null,
        'petugas_pelaksana'      => $item->petugas_pelaksana ?? null,
        'nama_sp'                => $supplier->nama_sp ?? null,
        'nama_petugas'           => $item->nama_petugas ?? null,
        'keterangan'             => $item->keterangan ?? null,
        'created_date'           => $item->created_date ?? null,
        'created_by'             => $item->created_by ?? null,
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