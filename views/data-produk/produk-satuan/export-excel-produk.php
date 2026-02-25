<?php
ini_set('memory_limit', '1024M');
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("location: 404.php");
    exit;
}

// Buat folder output_excel
$output_folder = __DIR__ . "/output_excel";
if (!file_exists($output_folder)) {
    mkdir($output_folder, 0777, true);
} else {
    array_map('unlink', glob($output_folder . "/*.xlsx"));
    @unlink($output_folder . "/data_instansi_all.zip");
}

require_once base_path('helpers/domain.php');
require_once base_path('public/vendor/autoload.php');
require_once base_path('config/database/database.php');

// ======= Query =========
require_once __DIR__ . "/query/export-data.php";

// ======= Fungsi Buat XLSX =========
require_once base_path('public/function-php/excel.php');

// ======= Pemecahan Data + Export =======
$max_per_file = 1000000;
$file_index = 1;
$current_file_row = 0;
$current_data = [];
$all_filenames = [];

// header akan otomatis bold + abu-abu
$headers = ['No', 'Kode Produk', 'Nama Produk', 'Satuan', 'Merk', 'Stock Total', 'Harga'];

// atur alignment per kolom
$alignments = [
    0 => 'center', // No
    1 => 'left',   // Kode Produk
    2 => 'left',   // Nama Produk
    3 => 'center', // Satuan
    4 => 'center',   // Merk
    5 => 'right',  // Stock
    6 => 'right',  // Harga
];

foreach ($data_produk as $i => $row) {
    $current_data[] = [
        $i + 1,
        $row->kode_produk ?? '',
        $row->nama_produk ?? '',
        $row->satuan_produk ?? '',
        $row->nama_merk ?? '',
        0,
        $row->harga ?? '',
    ];
    $current_file_row++;

    if ($current_file_row >= $max_per_file) {
        $filename = "$output_folder/data_produk_$file_index.xlsx";
        createSimpleXlsx($filename, $headers, $current_data, $alignments);
        $all_filenames[] = $filename;
        $file_index++;
        $current_data = [];
        $current_file_row = 0;
    }
}

// Sisa data terakhir
if (!empty($current_data)) {
    $filename = "$output_folder/data_produk_$file_index.xlsx";
    createSimpleXlsx($filename, $headers, $current_data, $alignments);
    $all_filenames[] = $filename;
}

ob_end_clean();

// ======= Auto-Download / ZIP =======
if (count($all_filenames) === 1) {
    $filepath = $all_filenames[0];
    $download_name = basename($filepath);
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $download_name . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
} else {
    $zip_filename = "$output_folder/data_produk_all.zip";
    $zip = new ZipArchive();
    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($all_filenames as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        if (file_exists($zip_filename)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="data_produk_all.zip"');
            header('Content-Length: ' . filesize($zip_filename));
            readfile($zip_filename);
            exit;
        }
    }

    // Fallback manual
    echo "<h4>Gagal membuat ZIP. Unduh manual:</h4>";
    foreach ($all_filenames as $i => $file) {
        $part = $i + 1;
        echo "<a href='output_excel/" . basename($file) . "' download>Download Part $part</a><br>";
    }
}
