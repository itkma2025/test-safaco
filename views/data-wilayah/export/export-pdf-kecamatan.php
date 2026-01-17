<?php
ini_set('memory_limit', '1024M');
require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');

use GuzzleHttp\Client;

// Ambil data via API
$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_wilayah = DOMAIN_WILAYAH;
$data_kecamatan = [];

if ($tokenJwt) {
    try {
        $client = new Client();
        $response = $client->get($domain_wilayah . 'api/data-kecamatan.php', [
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
            $data_kecamatan = $result['data'];
        }
    } catch (\Exception $e) {
        die("Gagal ambil data: " . $e->getMessage());
    }
} else {
    die("Token JWT tidak ditemukan.");
}

// ============================
// Setup mPDF
// ============================
$mpdf = new \Mpdf\Mpdf([
    'orientation' => 'L',
    'simpleTables' => true,
    'useSubstitutions' => false,
]);

$mpdf->SetDisplayMode('fullpage');

// CSS + Header
$style = '
<style>
    body { font-family: sans-serif; font-size: 11pt; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #000; padding: 4px; }
    th { background-color: #f2f2f2; text-align: center; }
    td { vertical-align: top; }
</style>';

$mpdf->WriteHTML($style, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML('<h3 style="text-align:center;">Data Kecamatan</h3>');

// Mulai tabel
$mpdf->WriteHTML('
<table>
    <thead>
        <tr>
            <th width="8%">No</th>
            <th width="15%">Kode Kecamatan</th>
            <th width="20%">Nama Kecamatan</th>
            <th width="25%">Nama Kota / Kabupaten</th>
            <th width="22%">Nama Provinsi</th>
            <th width="10%">Status</th>
        </tr>
    </thead>
    <tbody>
');

// Tulis data dalam potongan kecil
$chunkSize = 1000;
$no = 1;

foreach (array_chunk($data_kecamatan, $chunkSize) as $chunk) {
    $rowsHtml = '';
    foreach ($chunk as $row) {
        $rowsHtml .= '<tr>
            <td align="center">' . $no++ . '</td>
            <td align="center">' . htmlspecialchars($row['kode_kecamatan']) . '</td>
            <td>' . htmlspecialchars($row['nama_kecamatan']) . '</td>
            <td>' . htmlspecialchars($row['nama_kota_kab']) . '</td>
            <td>' . htmlspecialchars($row['nama_provinsi']) . '</td>
            <td align="center">' . htmlspecialchars($row['status']) . '</td>
        </tr>';
    }
    $mpdf->WriteHTML($rowsHtml);
}

// Tutup tabel
$mpdf->WriteHTML('</tbody></table>');

// ============================
// Output ke Browser
// ============================

if (ob_get_length()) ob_clean();
header('Content-Type: application/pdf');
$mpdf->Output('data_kecamatan.pdf', \Mpdf\Output\Destination::INLINE);
exit;
