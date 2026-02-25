<?php
require_once base_path('public/vendor/autoload.php');
require_once base_path('helpers/domain.php');
require_once base_path('config/database/database.php');

// ======= Query =========
require_once __DIR__ . "/query/export-data.php";

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

<h2 style="text-align:center;">Data Produk</h2>
<table>
    <thead>
        <tr>
            <th style="width:5%;">No</th>
            <th style="width:15%;">Kode Produk</th>
            <th style="width:30%;">Nama Produk</th>
            <th style="width:10%;">Satuan</th>
            <th style="width:15%;">Merk</th>
            <th style="width:15%;">Stock Total</th>
            <th style="width:10%;">Harga</th>
        </tr>
    </thead>
    <tbody>';

if (!empty($data_produk)) {
    $no = 1;
    foreach ($data_produk as $row) {
        $html .= '  <tr>
                        <td style="text-align:center;">' . $no++ . '</td>
                        <td>' . htmlspecialchars($row->kode_produk) . '</td>
                        <td>' . htmlspecialchars($row->nama_produk) . '</td>
                        <td style="text-align:center;">' . htmlspecialchars($row->satuan_produk) . '</td>
                        <td style="text-align:center;">' . htmlspecialchars($row->nama_merk) . '</td>
                        <td style="text-align:right;">' . htmlspecialchars('0') . '</td>
                        <td style="text-align:right;">' . number_format($row->harga ?? 0, 0, '.', '.') . '</td>
                    </tr>';
                }
} else {
    $html .= '<tr><td colspan="7" style="text-align:center;">Tidak ada data.</td></tr>';
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

$outputFolder = base_path('views/data-produk/produk/output_pdf');
if (!is_dir($outputFolder)) {
    mkdir($outputFolder, 0777, true);
}

$filename = 'data_produk_' . date('Ymd_His') . '.pdf';
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
