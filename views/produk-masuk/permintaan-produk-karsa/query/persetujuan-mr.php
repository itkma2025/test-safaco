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
require_once base_path('public/function-php/encrypt-decrypt/encrypt.php');

use Illuminate\Database\Capsule\Manager as DB;

$draw   = intval($_POST['draw'] ?? 0);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');
$id_spk_produksi = $_POST['id_spk_produksi'] ?? null;
$id_spk_produksi = $id_spk_produksi !== null ? trim($id_spk_produksi) : null;

/* ===============================
   2. Connection
================================ */
$db_safaco   = DB::connection('safaco');
$db_user     = DB::connection('user');

/* ===============================
   3. BASE QUERY (SAFACO ONLY)
================================ */
$baseQuery = $db_safaco
    ->table('permintaan_barang_karsa as pbk')
    ->leftJoin('jenis_permintaan as jp', 'pbk.id_jenis_permintaan', '=', 'jp.id_jenis_permintaan')
    ->where('pbk.status_permintaan', 'Permohonan Baru')
    ->where('pbk.persetujuan_pjt_safaco', '1')
    ->where('pbk.persetujuan_mr', '0');

/* ===============================
   4. TOTAL DATA
================================ */
$totalQuery = clone $baseQuery;
$recordsTotal = $totalQuery->count();

/* ===============================
   5. SEARCH
================================ */
if ($search !== '') {
    $baseQuery->where(function ($q) use ($search) {
        $q->where('pbk.no_permintaan', 'like', "%{$search}%");
    });
}

/* ===============================
   6. TOTAL FILTERED
================================ */
$filteredQuery = clone $baseQuery;
$recordsFiltered = $filteredQuery->count();

/* ===============================
   7. ORDER
================================ */
$baseQuery->orderBy('pbk.tgl_permintaan', 'asc');

/* ===============================
   8. DATA (SAFACO)
================================ */
$rows = $baseQuery
    ->select(
        'pbk.*',
        'jp.nama_jenis_permintaan'
    )
    ->offset($start)
    ->limit($length)
    ->get();

/* ===============================
   9. Ambil nama user (sekali saja)
================================ */
$id_user = $rows->pluck('created_by')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

$userMap = collect();
if (!empty($id_user)) {
    $userMap = $db_user
        ->table('user as u')
        ->whereIn('u.id_user', $id_user)
        ->select(
            'u.id_user',
            'u.nama_user'
        )
        ->get()
        ->keyBy('id_user');
}

/* ===============================
   10. Ambil Jumlah Produk (sekali saja)
================================ */
$id_permintaan_barang = $rows->pluck('id_permintaan_barang')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

$jumlahProdukMap = collect();
if (!empty($id_permintaan_barang)) {
    $jumlahProdukMap = $db_safaco
        ->table('details_permintaan_barang_karsa')
        ->whereIn('id_permintaan_barang', $id_permintaan_barang)
        ->select(
            'id_permintaan_barang',
            $db_safaco->raw('COUNT(*) as jumlah_produk')
        )
        ->groupBy('id_permintaan_barang')
        ->get()
        ->keyBy('id_permintaan_barang');
}

/* ===============================
   11. FORMAT
================================ */
$data = [];
$no = $start + 1;

foreach ($rows as $row) {
    // nama produk karsa (hasil mapping)
    $dataUser = $userMap[$row->created_by] ?? null;
    $nama_user = $dataUser ? $dataUser->nama_user : '-';

    // jumlah produk (hasil mapping)
    $jumlah_produk = $jumlahProdukMap[$row->id_permintaan_barang]->jumlah_produk ?? 0;

    $aksi = "
        <a class='btn btn-sm btn-primary' href='produk-masuk.php?action=details-permintaan-produk-karsa&id=" . encryptId($row->id_permintaan_barang) . "'>
            <i class='fas fa-eye'></i>
        </a>
    ";

    $data[] = [
        "<div class='align-middle text-center'>{$no}</div>",
        "<div class='align-middle text-center'>" . htmlspecialchars($row->no_permintaan) . "</div>",
        "<div class='align-middle text-center'>" . htmlspecialchars(date('d/m/Y', strtotime($row->tgl_permintaan))) . "</div>",
        "<div class='align-middle'>" . htmlspecialchars($row->nama_jenis_permintaan) . "</div>",
        "<div class='align-middle text-center'>" . htmlspecialchars($jumlah_produk) . "</div>",
        "<div class='align-middle'>" . htmlspecialchars($nama_user) . "</div>",
        "<div class='align-middle text-center'>" . htmlspecialchars(date('d/m/Y H:i:s', strtotime($row->created_date))) . "</div>",
        "<div class='align-middle text-center'>{$aksi}</div>",
    ];

    $no++;
}

/* ===============================
   12. JSON
================================ */
ob_clean();
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
]);
exit;
