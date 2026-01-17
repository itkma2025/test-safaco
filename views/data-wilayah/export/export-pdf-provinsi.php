<?php
require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');

use GuzzleHttp\Client;

// Ambil token dari cookie
$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_wilayah = DOMAIN_WILAYAH;
$data_provinsi = [];

if ($tokenJwt) {
    $client = new Client();

    try {
        $response = $client->request('GET', $domain_wilayah . 'api/data-provinsi.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt,
                'Accept'        => 'application/json',
            ],
            'http_errors' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);

        // Pastikan struktur respon benar
        if ($statusCode === 200 && isset($result['data']) && is_array($result['data'])) {
            $data_provinsi = $result['data'];
        } else {
            error_log("Export PDF - Data kosong atau tidak valid.");
        }
    } catch (\Exception $e) {
        error_log("Export PDF - Guzzle error: " . $e->getMessage());
    }
} else {
    error_log("Export PDF - Token JWT tidak ditemukan.");
}

// ============================
// HTML untuk PDF
// ============================

$html = '
<style>
    body { font-family: sans-serif; font-size: 12pt; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #000; padding: 5px; }
    th { background-color: #f2f2f2; text-align: center; }
</style>

<h2 style="text-align:center;">Data Provinsi / Perusahaan</h2>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Provinsi</th>
            <th>Nama Provinsi</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>';

if (!empty($data_provinsi)) {
    $no = 1;
    foreach ($data_provinsi as $row) {
        $html .= '<tr>
            <td style="text-align:center;">' . $no++ . '</td>
            <td style="text-align: center;">' . htmlspecialchars($row['kode_provinsi']) . '</td>
            <td>' . htmlspecialchars($row['nama_provinsi']) . '</td>
            <td style="text-align: center;">' . htmlspecialchars($row['status']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">Tidak ada data.</td></tr>';
}

$html .= '</tbody></table>';

// ============================
// Generate PDF
// ============================

$mpdf = new \Mpdf\Mpdf(['orientation' => 'P']);
$mpdf->WriteHTML($html);

// ============================
// Simpan ke folder output_pdf
// ============================

$outputFolder = base_path('views/data-wilayah/export/export-pdf/output_pdf');
if (!is_dir($outputFolder)) {
    mkdir($outputFolder, 0777, true);
}

$filename = 'data_provinsi_' . date('Ymd_His') . '.pdf';
$filepath = $outputFolder . '/' . $filename;

// Simpan file ke disk
$mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

// ============================
// Tampilkan ke browser
// ============================

if (ob_get_length()) ob_clean(); // Hapus buffer sebelumnya
header('Content-Type: application/pdf');
$mpdf->Output('data_provinsi.pdf', \Mpdf\Output\Destination::INLINE);
exit;
