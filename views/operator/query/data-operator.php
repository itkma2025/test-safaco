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
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

use Illuminate\Database\Capsule\Manager as DB;

// --- Koneksi DB ---
$db_user   = DB::connection('user');
$db_safaco = DB::connection('safaco');

// --- Ambil keyword pencarian ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Konfigurasi pagination ---
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit   = 10;
$offset  = ($page - 1) * $limit;

// --- Query dasar user ---
$queryUser = $db_user->table('user as us')
    ->leftJoin('user_role as ur', 'us.id_user_role', '=', 'ur.id_user_role')
    ->whereIn('ur.nama_role', [
        'PJT Produksi',
        'PJT Distribusi',
        'Kepala Produksi',
        'Operator Produksi'
    ]);

if ($search !== '') {
    $queryUser->where('us.nama_user', 'like', "%{$search}%");
}

// --- Hitung total & pages ---
$total = $queryUser->count();
$pages = ceil($total / $limit);

// --- Ambil list user sesuai halaman ini ---
$userList = $queryUser
    ->select('us.id_user', 'us.nama_user', 'us.no_hp', 'ur.nama_role')
    ->orderBy('us.nama_user', 'asc')
    ->offset($offset)
    ->limit($limit)
    ->get();

// --- Ambil relasi keahlian ---
$idUsers = $userList->pluck('id_user')->unique()->toArray();

$keahlianList = $db_safaco->table('operator as op')
    ->leftJoin('keahlian as kh', 'op.id_keahlian', '=', 'kh.id_keahlian')
    ->leftJoin('keahlian_alat_mesin as kam', 'kh.id_keahlian', '=', 'kam.id_keahlian')
    ->leftJoin('alat_mesin as am', 'kam.id_alat_mesin', '=', 'am.id_alat_mesin')
    ->whereIn('op.id_user', $idUsers)
    ->select(
        'op.id_user',
        'op.id_operator',
        'kh.nama_keahlian',
        $db_safaco->raw("GROUP_CONCAT(am.nama_barang ORDER BY am.nama_barang SEPARATOR ', ') as nama_barang"),
        'op.status_active'
    )
    ->groupBy('op.id_user', 'op.id_operator', 'kh.nama_keahlian', 'op.status_active')
    ->get()
    ->groupBy('id_user');


// --- Gabungkan user dengan keahlian ---
$data_user = $userList->map(function($item) use ($keahlianList) {
    $keahlian = $keahlianList->get($item->id_user);

    // ambil satu record keahlian (mirip kategori di produk)
    $k = $keahlian ? $keahlian->first() : null;

    return (object)[
        'id_user'       => $item->id_user,
        'nama_user'     => $item->nama_user,
        'no_hp'         => $item->no_hp,
        'nama_role'     => $item->nama_role,
        'id_operator'   => $k->id_operator   ?? null,
        'nama_keahlian' => $k->nama_keahlian ?? null,
        'nama_barang'   => $k->nama_barang ?? null,
        'status_active' => $k->status_active ?? null,
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
