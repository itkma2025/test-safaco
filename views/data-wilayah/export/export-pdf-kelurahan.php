<?php
ini_set('memory_limit', '2048M');
set_time_limit(600);
ini_set('pcre.backtrack_limit', '10000000'); // Tambahan keamanan

require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');

use GuzzleHttp\Client;

$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_wilayah = DOMAIN_WILAYAH;
$data_kelurahan = [];

if ($tokenJwt) {
    try {
        $client = new Client();
        $response = $client->get($domain_wilayah . 'api/data-kelurahan.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt,
                'Accept' => 'application/json',
            ],
            'query' => [
                'limit' => 'all',
                'search' => ''
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        if ($response->getStatusCode() === 200 && isset($result['data'])) {
            $data_kelurahan = $result['data'];
        } else {
            die("Gagal mendapatkan data.");
        }
    } catch (\Exception $e) {
        die("Gagal ambil data: " . $e->getMessage());
    }
} else {
    die("Token JWT tidak ditemukan.");
}

// ============================
// PDF Style
// ============================
$style = '
<style>
    body { font-family: sans-serif; font-size: 11pt; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #000; padding: 4px; }
    th { background-color: #f2f2f2; text-align: center; }
    td { vertical-align: top; }
</style>';

// ============================
// Split PDF Files
// ============================
$batchSize = 5000;
$totalData = count($data_kelurahan);
$totalBatch = ceil($totalData / $batchSize);
$no = 1;

$outputDir = __DIR__ . '/export-pdf';
if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

$pdfFiles = [];

for ($i = 0; $i < $totalBatch; $i++) {
    $batchData = array_slice($data_kelurahan, $i * $batchSize, $batchSize);

    $mpdf = new \Mpdf\Mpdf([
        'orientation' => 'L',
        'simpleTables' => true,
        'useSubstitutions' => false,
    ]);

    $mpdf->SetDisplayMode('fullpage');
    $mpdf->WriteHTML($style, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML('<h3 style="text-align:center;">Data Kecamatan (Bagian ' . ($i + 1) . ')</h3>');
    $mpdf->WriteHTML('
    <table>
        <thead>
            <tr>
                <th width="8%">No</th>
                <th width="15%">Kode Kecamatan</th>
                <th width="20%">Nama Kecamatan</th>
                <th width="20%">Nama Kelurahan</th>
                <th width="25%">Nama Kota / Kabupaten</th>
                <th width="22%">Nama Provinsi</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>');

    // Tulis setiap baris langsung
    foreach ($batchData as $row) {
        $mpdf->WriteHTML('<tr>
            <td align="center">' . $no++ . '</td>
            <td align="center">' . htmlspecialchars($row['kode_kelurahan']) . '</td>
            <td>' . htmlspecialchars($row['nama_kelurahan']) . '</td>
            <td>' . htmlspecialchars($row['nama_kecamatan']) . '</td>
            <td>' . htmlspecialchars($row['nama_kota_kab']) . '</td>
            <td>' . htmlspecialchars($row['nama_provinsi']) . '</td>
            <td align="center">' . htmlspecialchars($row['status']) . '</td>
        </tr>');
    }

    $mpdf->WriteHTML('</tbody></table>');

    $filePath = $outputDir . '/data_kelurahan_part_' . ($i + 1) . '.pdf';
    $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);
    $pdfFiles[] = $filePath;
}

// ============================
// ZIP Semua File PDF
// ============================
$zipFile = $outputDir . '/data_kelurahan_all.zip';
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    foreach ($pdfFiles as $file) {
        $zip->addFile($file, basename($file));
    }
    $zip->close();
} else {
    die("Gagal membuat file ZIP.");
}

// ============================
// Unduh ZIP ke Browser
// ============================
if (ob_get_length()) ob_clean();
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);
exit;
