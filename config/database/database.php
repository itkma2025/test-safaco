<?php
// Digunakan untuk debuging, jangan hapus
// require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// debug — ubah ke false jika tidak ingin menampilkan pesan koneksi
$debugDatabase = false;


// Inisialisasi Capsule
$connect = new Capsule;

// Koneksi Localhost

// Koneksi DB safaco
$connect->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'test_safaco',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'port'      => 3310,
], 'safaco'); // nama koneksi

// Koneksi DB produk Master
$connect->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'test_produk',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'port'      => 3310,
], 'produk_master'); // nama koneksi lain

// Koneksi DB user
$connect->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'test_user',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'port'      => 3310,
], 'user'); // nama koneksi lain

// Koneksi DB Kategori Produk
$connect->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'test_katprod',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'port'      => 3310,
], 'kat_produk'); // nama koneksi lain

// Koneksi DB Supplier
$connect->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',
    'database'  => 'test_supplier',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'port'      => 3310,
], 'supplier'); // nama koneksi lain

// Hosting

// Koneksi DB safaco
// $connect->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'aversa-db.id.domainesia.com',
//     'username'  => 'safacoka_test_safaco',
//     'password'  => 'test_safaco2025',
//     'database'  => 'safacoka_test_safaco',
//     'charset'   => 'utf8mb4',
//     'collation' => 'utf8mb4_unicode_ci',
//     'prefix'    => '',
// ], 'safaco'); // nama koneksi

// // Koneksi DB produk Master
// $connect->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'anzio-db.id.domainesia.com',
//     'username'  => 'mandir36_testproduk',
//     'password'  => 'test_produk',
//     'database'  => 'mandir36_test_produk',
//     'charset'   => 'utf8mb4',
//     'collation' => 'utf8mb4_unicode_ci',
//     'prefix'    => '',
// ], 'produk_master'); // nama koneksi lain

// // Koneksi DB user
// $connect->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'anzio-db.id.domainesia.com',
//     'username'  => 'mandir36_user',
//     'password'  => 'sso_IT2024',
//     'database'  => 'mandir36_test_user2025',
//     'charset'   => 'utf8mb4',
//     'collation' => 'utf8mb4_unicode_ci',
//     'prefix'    => '',
// ], 'user'); // nama koneksi lain

// // Koneksi DB Kategori Produk
// $connect->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'anzio-db.id.domainesia.com',
//     'username'  => 'mandir36_test_katprod',
//     'password'  => 'mandir36_test_katprod2025',
//     'database'  => 'mandir36_test_katprod',
//     'charset'   => 'utf8mb4',
//     'collation' => 'utf8mb4_unicode_ci',
//     'prefix'    => '',
// ], 'kat_produk'); // nama koneksi lain

// // Koneksi DB Supplier
// $connect->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'anzio-db.id.domainesia.com',
//     'username'  => 'mandir36_test_katprod',
//     'password'  => 'mandir36_test_katprod2025',
//     'database'  => 'mandir36_test_katprod',
//     'charset'   => 'utf8mb4',
//     'collation' => 'utf8mb4_unicode_ci',
//     'prefix'    => '',
// ], 'supplier'); // nama koneksi lain

$connect->setAsGlobal();
$connect->bootEloquent();

// 🔍 Debug koneksi (bisa dimatikan dengan $debugDatabase = false)
if ($debugDatabase) {
    try {
        $connect->getConnection('safaco')->getPdo();
        // echo "✅ Koneksi safaco berhasil.<br>";

        $connect->getConnection('produk_master')->getPdo();
        // echo "✅ Koneksi produk master berhasil.<br>";

        $connect->getConnection('user')->getPdo();
        // echo "✅ Koneksi user berhasil.<br>";

        $connect->getConnection('kat_produk')->getPdo();
        // echo "✅ Koneksi kategori produk berhasil.<br>";

        $connect->getConnection('supplier')->getPdo();
        // echo "✅ Koneksi supplier produk berhasil.<br>";

    } catch (\PDOException $e) {
        die("❌ Koneksi gagal: " . $e->getMessage());
    }
}

return $connect;
