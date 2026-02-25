<?php
use Illuminate\Database\Capsule\Manager as DB;

// --- Koneksi DB ---
$db_safaco   = DB::connection('safaco');       // server 1
$db_kat_prod = DB::connection('kat_produk');   // server 2

// --- Ambil produk ---
$produkList = $db_safaco->table('produk_satuan')
    ->select('id_produk', 'kode_produk', 'nama_produk', 'satuan_produk', 'harga', 'status_active', 'id_kategori_produk')
    ->orderBy('nama_produk', 'asc')
    ->get(); // collection of objects

// --- Ambil kategori & merk ---
$idKategori = $produkList->pluck('id_kategori_produk')->unique()->toArray();

$kategoriList = $db_kat_prod->table('tb_kat_produk as tkp')
    ->leftJoin('tb_merk as mr', 'tkp.id_merk', '=', 'mr.id_merk')
    ->whereIn('tkp.id_kat_produk', $idKategori)
    ->select('tkp.id_kat_produk', 'tkp.nama_kategori', 'tkp.status_expired', 'mr.nama_merk')
    ->get()
    ->keyBy('id_kat_produk'); // collection keyed by id_kat_produk

// --- Gabungkan produk dengan kategori ---
// memanfaatkan map() Illuminate Collection
$data_produk = $produkList->map(function($item) use ($kategoriList) {
    $kat = $kategoriList->get($item->id_kategori_produk);

    return (object)[
        'kode_produk'     => $item->kode_produk,
        'nama_produk'     => $item->nama_produk,
        'satuan_produk'   => $item->satuan_produk,
        'harga'           => $item->harga,
        'status_active'   => $item->status_active,
        'nama_kategori'   => $kat->nama_kategori ?? null,
        'status_expired'  => $kat->status_expired ?? null,
        'nama_merk'       => $kat->nama_merk ?? null,
    ];
});
