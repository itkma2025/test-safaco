<?php
// Helper - Domain
require_once base_path('helpers/domain.php');
$domain_customer        = DOMAIN_CUSTOMER;
$domain_supplier        = DOMAIN_SUPPLIER;
$domain_kategori_produk = DOMAIN_KATEGORI_PRODUK;
$domain_sticker         = DOMAIN_STICKER;

// Untuk memeriksa domain asal
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST']; // akan memberi 'localhost:8084'

$currentUrl = $protocol . $host;


function isActive($active, $expected) {
    return $active === $expected ? 'active' : '';
}
?>
<style>
    .logo-sidebar-custom {
        display: none !important;
    }

    .sidebar-inner {
        max-height: 100vh;
        overflow-y: auto;
    }

    .sidebar .sidebar-logo img {
        padding: 0px 32px 0 !important;
    }

    @media (max-width: 450px) {
        .logo-sidebar-custom {
            display: block !important;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="#" class="logo logo-normal">
            <img src="<?= asset('img/logo.png') ?>" alt="Logo">
        </a>
        <a href="#" class="logo-small">
            <img src="<?= asset('img/logo.png') ?>" alt="Logo">
        </a>
        <a href="#" class="dark-logo">
            <img src="<?= asset('img/logo.png') ?>" alt="Logo">
        </a>
    </div>
    <!-- /Logo -->
    <div class="sidebar-inner slimscroll mt-1">
        <div id="sidebar-menu" class="sidebar-menu">
            <div class="mb-2 logo-sidebar-custom">
                <img src="<?= asset('img/logo.png') ?>" alt="Logo">
            </div>
            <ul class="mb-5">
                <li class="menu-title"><span>MAIN MENU</span></li>
                <li>
                    <ul>
                        <li class="<?= isActive($active_menu ?? '', 'dashboard') ?>">
                            <a href="dashboard.php">
                                <i class="fe fe-dashboard"></i><span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Data Management</span></li>
                <li>
                    <ul>
                        <li class="<?= isActive($active_menu ?? '', 'data-instansi') ?>">
                            <a href="data-instansi.php">
                                <i class="fe fe-home"></i><span>Data Instansi</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $domain_customer . "?url=" . $currentUrl ?>" target="_blank">
                                <i class="fe fe-user"></i><span>Data Customer</span>
                            </a>
                        </li>
                        <li class="<?= isActive($active_menu ?? '', 'data-sales') ?>">
                            <a href="data-sales.php">
                                <i class="fe fe-user"></i><span>Data Sales</span>
                            </a>
                        </li>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'data-wilayah') ?> <?= ($active_menu ?? '') === 'data-wilayah' ? 'subdrop' : '' ?>">
                                <i class="fe fe-map"></i><span>Data Wilayah</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="data-wilayah.php?action=provinsi" class="<?= isActive($active_submenu  ?? '', 'data-provinsi') ?>">
                                        Provinsi
                                    </a>
                                </li>
                                <li>
                                   <a href="data-wilayah.php?action=kotakab" class="<?= isActive($active_submenu  ?? '', 'data-kota-kab') ?>">
                                        Kota / Kab
                                    </a>
                                </li>
                                <li>
                                    <a href="data-wilayah.php?action=kecamatan" class="<?= isActive($active_submenu  ?? '', 'data-kecamatan') ?>">
                                        Kecamatan
                                    </a>
                                </li>
                                <li>
                                    <a href="data-wilayah.php?action=kelurahan" class="<?= isActive($active_submenu  ?? '', 'data-kelurahan') ?>">
                                        Keluarahan
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="<?= isActive($active_menu ?? '', 'data-operator') ?>">
                            <a href="data-operator.php?action=operator"">
                                <i class="fe fe-user"></i><span>Operator Management</span>
                            </a>
                        </li>
                        <li class="<?= isActive($active_menu ?? '', 'data-jadwal-kerja') ?>">
                            <a href="data-jadwal-kerja.php?action=jadwal-kerja">
                                <i class="fe fe-clipboard"></i><span>Work Schedule Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $domain_supplier . "?url=" . $currentUrl ?>" target="_blank">
                                <i class="fe fe-truck"></i><span>Data Supplier / Vendor</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Data Produk</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'data-produk') ?> <?= ($active_menu ?? '') === 'data-produk' ? 'subdrop' : '' ?>">
                                <i class="fe fe-package"></i><span>Data Produk</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="data-produk.php?action=produk-satuan" class="<?= isActive($active_submenu  ?? '', 'produk-satuan') ?>">
                                        Produk Satuan
                                    </a>
                                </li>
                                <li>
                                    <a href="data-produk.php?action=produk-set" class="<?= isActive($active_submenu  ?? '', 'produk-set') ?>">
                                        Produk Set
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="<?= isActive($active_menu ?? '', 'kategori-penjualan') ?>">
                            <a href="data-produk.php?action=kategori-penjualan">
                                <i class="fe fe-grid"></i><span>Kategori Penjualan</span>
                            </a>
                        </li>
                        <li class="<?= isActive($active_menu ?? '', 'lokasi-produk') ?>">
                            <a href="data-produk.php?action=lokasi">
                                <i class="fe fe-map-pin"></i><span>Lokasi Produk</span>
                            </a>
                        </li class="<?= isActive($active_menu ?? '', 'grade-produk') ?>">
                        <li>
                            <a href="data-produk.php?action=grade">
                                <i class="fe fe-layers"></i><span>Grade Produk</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $domain_kategori_produk . "?url=" . $currentUrl ?>" target="_blank">
                                <i class="fe fe-grid"></i><span>Kategori Produk & Merk</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-file"></i><span>Dead Stock Reporting</span>
                            </a>
                        </li>						
                        <li class="submenu">
                            <a href="#">
                                <i class="fe fe-download"></i><span>Produk Masuk</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="#">
                                        <i class="fe fe-package"></i><span>Karsa</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <a href="jenis-permintaan.php?action=view">
                                            Jenis Permintaan
                                        </a>
                                        <a href="produk-masuk.php?action=karsa">
                                            Data Permintaan
                                        </a>
                                    </ul>
                                </li>
                                <li><a href="#">Import</a></li>
                                <li><a href="#">Produk Set E-Catalogue</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Data Stock Produk</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'stock-karsa') ?> <?= ($active_menu ?? '') === 'stock-karsa' ? 'subdrop' : '' ?>">
                                <i class="fe fe-package"></i><span>Stock Produk Karsa</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="stock-produk.php?action=produk-karsa-reg" class="<?= isActive($active_submenu  ?? '', 'stock-karsa-reg') ?>">
                                        Reguler
                                    </a>
                                </li>
                                <li>
                                    <a href="stock-produk.php?action=produk-karsa-ecat" class="<?= isActive($active_submenu  ?? '', 'stock-karsa-ecat') ?>">
                                        E-Cat
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-package"></i><span>Stock Produk Sorting</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-package"></i><span>Stock Produk Raw Material</span>
                            </a>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="fe fe-package"></i><span>Stock Produk Karantina</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="#">Stock Karantina Raw Material</a></li>
                                <li><a href="#">Stock Karantina SOrtir Material</a></li>
                            </ul>
                        </li>

                    </ul>
                </li>
                <li class="menu-title"><span>Data Stock Packaging</span></li>
                <li>
                    <ul>
                        <li>
                            <a href="#">
                                <i class="fe fe-package"></i><span>Stock Kardus</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-package"></i><span>Stock Stiker</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-package"></i><span>Stock Plastik Ready</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="menu-title"><span>Produksi Management</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'data-pendukung-produksi') ?> <?= ($active_menu ?? '') === 'data-pendukung-produksi' ? 'subdrop' : '' ?>">
                                <i class="fe fe-file-text"></i><span>Data Pendukung Produksi</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="data-pendukung-produksi.php?action=jenis-produksi" class="<?= isActive($active_submenu  ?? '', 'jenis-produksi') ?>">
                                        Jenis Produksi
                                    </a>
                                </li>
                                <li>
                                    <a href="data-pendukung-produksi.php?action=jenis-pengerjaan" class="<?= isActive($active_submenu  ?? '', 'jenis-pengerjaan') ?>">
                                        Jenis Pengerjaan
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'perencanaan-produksi') ?> <?= ($active_menu ?? '') === 'perencanaan-produksi' ? 'subdrop' : '' ?>">
                                <i class="fe fe-file-text"></i><span>Perencanaan Produksi</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="#" class="<?= isActive($active_submenu  ?? '', 'kalender-produksi') ?>">
                                        Kalender Produksi
                                    </a>
                                </li>
                                <li>
                                    <a href="perencanaan-produksi.php?action=spk-produksi" class="<?= isActive($active_submenu  ?? '', 'spk-produksi') ?>">
                                        SPK Produksi
                                    </a>
                                </li>
                                <li><a href="#">Permintaan Barang</a></li>
                                <li><a href="#">Analisis Produksi</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'perawatan-alat-mesin') ?> <?= ($active_menu ?? '') === 'perawatan-alat-mesin' ? 'subdrop' : '' ?>">
                                <i class="fe fe-clipboard"></i><span>Perawatan Alat & Mesin</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="perawatan-alat-mesin.php?action=list-alat-mesin" class="<?= isActive($active_submenu  ?? '', 'list-alat-mesin') ?>">
                                        List Alat & Mesin
                                    </a>
                                </li>
                                <li><a href="#">Jadwal Maintenance</a></li>
                                <li><a href="#">Kalibrasi Alat / Mesin</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#" class="<?= isActive($active_menu ?? '', 'data-jenis-perbaikan') ?> <?= ($active_menu ?? '') === 'data-jenis-perbaikan' ? 'subdrop' : '' ?>">
                                <i class="fe fe-grid"></i><span>Data Jenis Perbaikan</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="data-jenis-perbaikan.php?action=kategori-perbaikan" class="<?= isActive($active_submenu  ?? '', 'kategori-perbaikan') ?>">
                                        Kategori Perbaikan
                                    </a>
                                </li>
                                <li>
                                    <a href="data-jenis-perbaikan.php?action=jenis-perbaikan" class="<?= isActive($active_submenu  ?? '', 'jenis-perbaikan') ?>">
                                        Jenis Perbaikan
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="<?= $domain_sticker ?>">
                                <i class="fe fe-file"></i><span>Permintaan Stiker</span>
                            </a>
                        </li>
                    </ul>							
                </li>
                <li class="menu-title"><span>Log Product</span></li>
                <li>
                    <ul>
                        <li>
                            <a href="#">
                                <i class="fe fe-column-insert-left"></i><span>Material Consumption Log</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-column-insert-left"></i><span>Supplier Quality Log</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Digital Document</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="#">
                                <i class="fe fe-package"></i><span>Surat Menyurat</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="#">Surat Masuk</a></li>
                                <li><a href="#">Surat Keluar</a></li>
                            </ul>
                        </li>
                       <li class="submenu">
                            <a href="#">
                                <i class="fe fe-package"></i><span>Procedure</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="#">S.O.P</a></li>
                                <li><a href="#">Intruksi Kerja</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-column-insert-left"></i><span>Sertifikat / ISO</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-column-insert-left"></i><span>Dokumen Produksi</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fe fe-column-insert-left"></i><span>Dokumen Registrasi</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->