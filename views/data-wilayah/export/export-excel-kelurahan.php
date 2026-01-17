<?php
ini_set('memory_limit', '1024M');
ob_start();

// Buat folder output_excel
$output_folder = __DIR__ . "/output_excel";
if (!file_exists($output_folder)) {
    mkdir($output_folder, 0777, true);
} else {
    array_map('unlink', glob($output_folder . "/*.xlsx"));
    @unlink($output_folder . "/data_kelurahan_all.zip");
}

require_once base_path('helpers/domain.php');
require_once base_path('public/vendor/autoload.php');

use GuzzleHttp\Client;

$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_wilayah = DOMAIN_WILAYAH;
$data_kelurahan = [];

if ($tokenJwt) {
    $client = new Client();

    try {
        $response = $client->request('GET', $domain_wilayah . 'api/data-kelurahan.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt,
                'Accept' => 'application/json',
            ],
            'query' => [
                'limit' => 'all',
                'page' => 1,
                'search' => ''
            ],
            'http_errors' => false,
        ]);

        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);

        // Simpan ke file untuk debug
        // file_put_contents(__DIR__ . '/debug_kelurahan.txt', $body);

        // ⬇ Tambahkan baris ini!
        $data_kelurahan = $result['data'] ?? [];

    } catch (Exception $e) {
        error_log('Guzzle error: ' . $e->getMessage());
    }
}

// Fungsi buat XLSX
require_once base_path('public/function-php/excel.php');

// Pemrosesan file
$max_per_file = 1000000;
$file_index = 1;
$current_data = [];
$all_filenames = [];

$headers = ['No', 'Kode Kelurahan', 'Nama Kelurahan', 'Nama Kecamatan', 'Nama Kota / Kabupaten', 'Nama Provinsi', 'Status'];

foreach ($data_kelurahan as $i => $row) {
    $current_data[] = [
        $i + 1,
        $row['kode_kelurahan'] ?? '',
        $row['nama_kelurahan'] ?? '',
        $row['nama_kecamatan'] ?? '',
        $row['nama_kota_kab'] ?? '',
        $row['nama_provinsi'] ?? '',
        $row['status'] ?? '',
    ];

    if (count($current_data) >= $max_per_file) {
        $filename = "$output_folder/data_kelurahan_$file_index.xlsx";
        createSimpleXlsx($filename, $headers, $current_data);
        $all_filenames[] = $filename;
        $file_index++;
        $current_data = [];
    }
}

// Sisa data
if (!empty($current_data)) {
    $filename = "$output_folder/data_kelurahan_final_$file_index.xlsx";
    createSimpleXlsx($filename, $headers, $current_data);
    $all_filenames[] = $filename;
}

// ⬇ Pindahkan ob_end_clean sebelum header
ob_end_clean();

// Download file
if (count($all_filenames) === 1) {
    $filepath = $all_filenames[0];
    $download_name = basename($filepath);

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($filepath));
        flush();
        readfile($filepath);
        exit;
    } else {
        echo "<p style='color:red'>File tidak ditemukan: $filepath</p>";
    }
} else {
    $zip_filename = "$output_folder/data_kelurahan_all.zip";
    $zip = new ZipArchive();
    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($all_filenames as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();

        if (file_exists($zip_filename)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="data_kelurahan_all.zip"');
            header('Content-Length: ' . filesize($zip_filename));
            flush();
            readfile($zip_filename);
            exit;
        } else {
            echo "<p style='color:red'>ZIP tidak ditemukan meskipun proses close berhasil.</p>";
        }
    } else {
        echo "<p style='color:red'>Gagal membuka ZIP</p>";
    }
}
