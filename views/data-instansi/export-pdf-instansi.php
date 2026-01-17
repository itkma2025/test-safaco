<?php
require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');

use GuzzleHttp\Client;

// Ambil token dari cookie
$tokenJwt = require base_path('helpers/jwt-token.php');
$domain_instansi = DOMAIN_INSTANSI;
$data_instansi = [];

if ($tokenJwt) {
    $client = new Client();
    $search = $_GET['search'] ?? '';
    $action = $_GET['action'] ?? '';

    try {
        $response = $client->request('GET', $domain_instansi . 'api/data-instansi.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt,
                'Accept'        => 'application/json',
            ],
            'query' => [
                'action'    => $action,
                'search'    => $search
            ],
            'http_errors' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);

        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";

        // Pastikan struktur respon benar
        if ($statusCode === 200 && isset($result['data']) && is_array($result['data'])) {
            $data_instansi = $result['data'];
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

<h2 style="text-align:center;">Data Instansi</h2>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Instansi</th>
            <th>Alamat</th>
            <th>No. Telp</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>';

if (!empty($data_instansi)) {
    $no = 1;
    foreach ($data_instansi as $row) {
        $html .= '<tr>
            <td style="text-align:center;">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['nama_instansi']) . '</td>
            <td>' . htmlspecialchars($row['alamat']) . '</td>
            <td style="text-align:right;">' . htmlspecialchars($row['no_telp']) . '</td>
            <td>' . htmlspecialchars($row['email_instansi']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">Tidak ada data.</td></tr>';
}

$html .= '</tbody></table>';

// ============================
// Generate PDF
// ============================

$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);
$mpdf->WriteHTML($html);

// ============================
// Simpan ke folder output_pdf
// ============================

$outputFolder = base_path('views/data-instansi/output_pdf');
if (!is_dir($outputFolder)) {
    mkdir($outputFolder, 0777, true);
}

$filename = 'data_instansi_' . date('Ymd_His') . '.pdf';
$filepath = $outputFolder . '/' . $filename;

// Simpan file ke disk
$mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

// ============================
// Tampilkan ke browser
// ============================

if (ob_get_length()) ob_clean(); // Hapus buffer sebelumnya
header('Content-Type: application/pdf');
$mpdf->Output('data_instansi.pdf', \Mpdf\Output\Destination::INLINE);
exit;
