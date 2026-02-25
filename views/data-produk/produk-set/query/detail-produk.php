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
require_once base_path('public/api/get-user.php');

$userMap = getUserMap();

use Illuminate\Database\Capsule\Manager as DB;

// Koneksi DB
$db_safaco   = DB::connection('safaco');
$db_kat_prod = DB::connection('kat_produk');
$db_prd_m    = DB::connection('produk_master');

// --- Ambil produk utama ---
$produk = $db_safaco->table('produk_set')
    ->where('id_produk_set', $id_produk_decrypt)
    ->first();

if (!$produk) {
    die("Produk tidak ditemukan");
}

// --- Ambil kategori produk & merk ---
$kategori = $db_kat_prod->table('tb_kat_produk as tkp')
    ->leftJoin('tb_merk as mr', 'tkp.id_merk', '=', 'mr.id_merk')
    ->where('tkp.id_kat_produk', $produk->id_kategori_produk)
    ->select('tkp.nama_kategori', 'tkp.no_izin_edar', 'tkp.status_expired', 'mr.nama_merk')
    ->first();

// --- Ambil produk master ---
$produkMaster = $db_prd_m->table('tb_produk_set')
    ->where('id_produk_master', $produk->id_produk_master)
    ->select('id_produk_master', 'nama_produk as nama_produk_master')
    ->first();

// --- Ambil lokasi ---
$lokasi = $db_safaco->table('produk_lokasi')
    ->where('id_lokasi', $produk->id_lokasi)
    ->select('nama_lokasi', 'lantai', 'area', 'no_rak')
    ->first();

// --- Ambil grade ---
$grade = $db_safaco->table('produk_grade')
    ->where('id_grade_produk', $produk->id_grade_produk)
    ->select('grade')
    ->first();

// --- Ambil kategori penjualan ---
$katPenjualan = $db_safaco->table('kategori_penjualan')
    ->where('id_kategori_penjualan', $produk->id_kategori_penjualan)
    ->select('id_kategori_penjualan', 'kategori_penjualan')
    ->first();

// --- Ambil gambar produk (satu atau lebih) ---
$gambar = $db_safaco->table('produk_gambar_set')
    ->where('id_produk_set', $produk->id_produk_set)
    ->select('filename', 'mime_type', 'file_path', 'iv', 'signature', 'key_file')
    ->first();

// --- Gabungkan semua data ke object final ---
$data_produk = (object)[
    'id_produk_set'          => $produk->id_produk_set ?? '',
    'kode_produk_set'        => $produk->kode_produk_set ?? '',
    'kode_katalog'           => $produk->kode_katalog ?? '',
    'nama_produk_set'        => $produk->nama_produk_set ?? '',
    'harga'                  => $produk->harga ?? '',
    'id_kategori_produk'     => $produk->id_kategori_produk ?? '',
    'deskripsi_produk'       => $produk->deskripsi_produk ?? '',
    'status_active'          => $produk->status_active ?? '',
    'created_date'           => $produk->created_date ?? '',
    'created_by'             => $produk->created_by ?? '',
    'updated_date'           => $produk->updated_date ?? '',
    'updated_by'             => $produk->updated_by ?? '',

    // relasi kategori/merk
    'nama_kategori_produk'   => $kategori->nama_kategori ?? '',
    'no_izin_edar'           => $kategori->no_izin_edar ?? '',
    'status_expired'         => $kategori->status_expired ?? '',
    'nama_merk'              => $kategori->nama_merk ?? '',

    // produk master
    'id_produk_master'       => $produkMaster->id_produk_master ?? '',
    'nama_produk_master'     => $produkMaster->nama_produk_master ?? '',

    // top-level untuk lokasi (buat alias supaya view lama tetap jalan)
    'id_lokasi'              => $produk->id_lokasi ?? '',
    'nama_lokasi'            => $lokasi->nama_lokasi ?? '',
    'lantai'                 => $lokasi->lantai ?? '',
    'area'                   => $lokasi->area ?? '',
    'no_rak'                 => $lokasi->no_rak ?? '',

    // grade (simpan id dan nama/label)
    'id_grade_produk'        => $produk->id_grade_produk ?? '',
    'grade'                  => $grade->grade ?? '',

    // kategori penjualan
    'id_kategori_penjualan'  => $katPenjualan->id_kategori_penjualan ?? '',
    'kategori_penjualan'     => $katPenjualan->kategori_penjualan ?? '',

    // gambar
    'filename'               => $gambar->filename ?? '',
    'mime_type'              => $gambar->mime_type ?? '',
    'file_path'              => $gambar->file_path ?? '',
    'iv'                     => $gambar->iv ?? '',
    'signature'              => $gambar->signature ?? '',
    'key_file'               => $gambar->key_file ?? '',
];

?>
