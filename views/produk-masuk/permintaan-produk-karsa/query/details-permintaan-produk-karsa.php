<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    $domain_sso = DOMAIN_SSO;

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST'];
    $currentUrl = $protocol . $host;

    header("Location:{$domain_sso}logout.php?url={$currentUrl}");
    exit;
}

require_once base_path('public/vendor/autoload.php');
require_once base_path('config/database/database.php');
require_once base_path('helpers/domain.php');
require_once base_path('public/function-php/encrypt-decrypt/decrypt.php');

use Illuminate\Database\Capsule\Manager as DB;

/* ===============================
   1. PARAMETER
================================ */
$draw   = intval($_POST['draw'] ?? 0);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');
$id_permintaan_barang = $_POST['id_permintaan_barang'] ?? null;
$id_permintaan_barang = $id_permintaan_barang !== null ? trim($id_permintaan_barang) : null;
$id_permintaan_barang_decrypt = decryptId($id_permintaan_barang, $key_akses);

/* ===============================
   2. CONNECTION
================================ */
$db_safaco          = DB::connection('safaco');
$db_kat_prod        = DB::connection('kat_produk');
$db_inventory_karsa = DB::connection('inventory_karsa');

/* ===============================
   3. BASE QUERY (SAFACO)
================================ */
$baseQuery = $db_safaco
    ->table('details_permintaan_barang_karsa as dp')
    ->where('dp.id_permintaan_barang', $id_permintaan_barang_decrypt);

/* ===============================
   4. TOTAL DATA
================================ */
$recordsTotal = (clone $baseQuery)->count();

/* ===============================
   5. SEARCH (optional jika mau aktif)
================================ */
// if ($search !== '') {
//     $baseQuery->where(function ($q) use ($search) {
//         $q->where('dp.some_column', 'like', "%{$search}%");
//     });
// }

$recordsFiltered = (clone $baseQuery)->count();

/* ===============================
   6. ORDER
================================ */
$baseQuery->orderBy('dp.created_date', 'asc');

/* ===============================
   7. GET DATA (SAFACO)
================================ */
$rows = $baseQuery
    ->select('dp.*')
    ->offset($start)
    ->limit($length)
    ->get();

/* ===============================
   8. AMBIL PRODUK KARSA (UNION 4 TABLE)
================================ */
$idProdukKarsa = $rows->pluck('id_produk_karsa')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

$produkKarsaMap = collect();

if (!empty($idProdukKarsa)) {

    $reguler = $db_inventory_karsa
        ->table('tb_produk_reguler')
        ->select(
            'id_produk_reg as id_produk',
            'kode_produk',
            'nama_produk',
            'satuan as satuan_produk',
            'id_kat_produk',
            'id_grade'
        )
        ->whereIn('id_produk_reg', $idProdukKarsa);

    $ecat = $db_inventory_karsa
        ->table('tb_produk_ecat')
        ->select(
            'id_produk_ecat as id_produk',
            'kode_produk',
            'nama_produk',
            'satuan as satuan_produk',
            'id_kat_produk',
            'id_grade'
        )
        ->whereIn('id_produk_ecat', $idProdukKarsa);

    $setMarwa = $db_inventory_karsa
        ->table('tb_produk_set_marwa')
        ->selectRaw("
            id_set_marwa as id_produk,
            kode_set_marwa as kode_produk,
            nama_set_marwa as nama_produk,
            'Set' as satuan_produk,
            id_kat_produk,
            id_grade
        ")
        ->whereIn('id_set_marwa', $idProdukKarsa);

    $setEcat = $db_inventory_karsa
        ->table('tb_produk_set_ecat')
        ->selectRaw("
            id_set_ecat as id_produk,
            kode_set_ecat as kode_produk,
            nama_set_ecat as nama_produk,
            'Set' as satuan_produk,
            id_kat_produk,
            id_grade
        ")
        ->whereIn('id_set_ecat', $idProdukKarsa);

    $produkKarsaMap = $reguler
        ->unionAll($ecat)
        ->unionAll($setMarwa)
        ->unionAll($setEcat)
        ->get()
        ->keyBy('id_produk');
}

/* ===============================
   9. AMBIL KATEGORI + MERK
================================ */
$idKategoriList = $produkKarsaMap->pluck('id_kat_produk')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

$kategoriMap = collect();

if (!empty($idKategoriList)) {
    $kategoriMap = $db_kat_prod
        ->table('tb_kat_produk as tkp')
        ->leftJoin('tb_merk as tm', 'tkp.id_merk', '=', 'tm.id_merk')
        ->whereIn('tkp.id_kat_produk', $idKategoriList)
        ->select(
            'tkp.id_kat_produk',
            'tkp.no_izin_edar',
            'tkp.nama_kategori',
            'tm.nama_merk'
        )
        ->get()
        ->keyBy('id_kat_produk');
}

/* ===============================
   10. AMBIL Grade
================================ */
$idGradeList = $produkKarsaMap->pluck('id_grade')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

$gradeMap = collect();

if (!empty($idGradeList)) {
    $gradeMap = $db_inventory_karsa
        ->table('tb_produk_grade')
        ->whereIn('id_grade', $idGradeList)
        ->select(
            'id_grade',
            'nama_grade'
        )
        ->get()
        ->keyBy('id_grade');
}

/* ===============================
   11. FORMAT DATA
================================ */
$data = [];
$no = $start + 1;

foreach ($rows as $row) {

    $prdKarsa = $produkKarsaMap->get($row->id_produk_karsa);

    if (!$prdKarsa) {
        continue;
    }

    $kode_produk = $prdKarsa->kode_produk ?? '-';
    $nama_produk = $prdKarsa->nama_produk ?? '-';

    // Search Data
    if ($search !== '' && stripos($nama_produk, $search) === false) {
        continue;
    }

    $satuan = $prdKarsa->satuan_produk ?? '-';

    $kat = $kategoriMap->get($prdKarsa->id_kat_produk);

    $namaKategori = $kat->nama_kategori ?? '-';
    $merk = $kat->nama_merk ?? '-';

    $grade = $gradeMap->get($prdKarsa->id_grade);
    $namaGrade = $grade ? $grade->nama_grade : '-';

    $aksi = "-"; // ganti dengan tombol jika perlu

    $data[] = [
        "<div class='align-middle text-center'>{$no}</div>",
        "<div class='align-middle text-center'>" . htmlspecialchars($kode_produk) . "</div>",
        "<div class='align-middle'>" . htmlspecialchars($nama_produk) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($namaKategori) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($merk) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($namaGrade) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($row->qty_request) . "</div>",
        "<div class='align-middle text-center text-wrap'>" . htmlspecialchars($satuan) . "</div>",
        "<div class='align-middle text-center'>{$aksi}</div>",
    ];

    $no++;
}

/* ===============================
   11. RETURN JSON
================================ */
ob_clean();
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
]);
exit;