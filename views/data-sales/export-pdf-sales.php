<?php
require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');

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

        // Debug sementara:
        // echo "<pre>" . htmlspecialchars($body) . "</pre>";

        // Pastikan struktur respon benar
        if ($statusCode === 200 && isset($result['data']) && is_array($result['data'])) {
            $data_sales = $result['data'];
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

<h2 style="text-align:center;">Data Instansi / Perusahaan</h2>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Sales</th>
            <th>Nama Instansi</th>
            <th>Wilayah</th>
            <th>Telepon</th>
            <th>Email</th>
            <th>Jenis Sales</th>
        </tr>
    </thead>
    <tbody>';

if (!empty($data_sales)) {
    $no = 1;
    foreach ($data_sales as $row) {
        $html .= '<tr>
            <td style="text-align:center;">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['nama_sales']) . '</td>
            <td>' . htmlspecialchars($row['nama_instansi']) . '</td>
            <td>' . htmlspecialchars($row['nama_provinsi']) . '</td>
            <td>' . htmlspecialchars($row['no_telp']) . '</td>
            <td style="text-align:right;">' . htmlspecialchars($row['email_sales']) . '</td>
            <td>' . htmlspecialchars($row['jenis_sales']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
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

$outputFolder = base_path('views/data-sales/output_pdf');
if (!is_dir($outputFolder)) {
    mkdir($outputFolder, 0777, true);
}

$filename = 'data_sales_' . date('Ymd_His') . '.pdf';
$filepath = $outputFolder . '/' . $filename;

// Simpan file ke disk
$mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

// ============================
// Tampilkan ke browser
// ============================

if (ob_get_length()) ob_clean(); // Hapus buffer sebelumnya
header('Content-Type: application/pdf');
$mpdf->Output('data_sales.pdf', \Mpdf\Output\Destination::INLINE);
exit;
