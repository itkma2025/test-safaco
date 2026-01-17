<?php
ini_set('memory_limit', '1024M');
ob_start();

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

use GuzzleHttp\Client;

// Ambil token dari cookie
$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_instansi = DOMAIN_INSTANSI;
$data_sales = [];

if ($tokenJwt) {
    $client = new Client();
    $search = $_GET['search'] ?? '';
    $action = $_GET['action'] ?? '';

    try {
        $response = $client->request('GET', $domain_instansi . 'api/data-sales.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt,
                'Accept' => 'application/json',
            ],
            'query' => [
                'action'    => $action,
                'search'    => $search
            ],
            'http_errors' => false,
        ]);

        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);

        // Debug sementara:
        // echo "<pre>" . htmlspecialchars($body) . "</pre>";

        if ($response->getStatusCode() === 200 && isset($result['data']) && is_array($result['data'])) {
            $data_sales = $result['data'];
        }
    } catch (Exception $e) {
        error_log('Guzzle error: ' . $e->getMessage());
    }
}

// ======= Fungsi Buat XLSX =========
require_once base_path('public/function-php/excel.php');

// ======= Pemecahan Data + Export =======
$max_per_file = 1000000;
$file_index = 1;
$current_file_row = 0;
$current_data = [];
$all_filenames = [];

$headers = ['No', 'Nama Sales', 'Nama Instansi', 'Wilayah', 'Telepon', 'Email', 'Jenis Sales'];

foreach ($data_sales as $i => $row) {
    $current_data[] = [
        $i + 1,
        $row['nama_sales'] ?? '',
        $row['nama_instansi'] ?? '',
        $row['nama_provinsi'] ?? '',
        $row['no_telp'] ?? '',
        $row['email_sales'] ?? '',
        $row['jenis_sales'] ?? '',
    ];
    $current_file_row++;

    if ($current_file_row >= $max_per_file) {
        $filename = "$output_folder/data_sales_$file_index.xlsx";
        createSimpleXlsx($filename, $headers, $current_data);
        $all_filenames[] = $filename;
        $file_index++;
        $current_data = [];
        $current_file_row = 0;
    }
}

// Sisa data terakhir
if (!empty($current_data)) {
    $filename = "$output_folder/data_sales_final_$file_index.xlsx";
    createSimpleXlsx($filename, $headers, $current_data);
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
    $zip_filename = "$output_folder/data_sales_all.zip";
    $zip = new ZipArchive();
    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($all_filenames as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        if (file_exists($zip_filename)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="data_sales_all.zip"');
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
