<?php
date_default_timezone_set('Asia/Jakarta');

// Buat folder logs jika belum ada
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Nama file log berdasarkan tanggal
$logFile = $logDir . '/kategori_penjualan_form_' . date('Y-m-d') . '.log';

// Ambil data FILE
$fileData = [];
if (!empty($_FILES)) {
    foreach ($_FILES as $key => $file) {
        $fileData[$key] = [
            'name'  => $file['name'],
            'type'  => $file['type'],
            'size'  => $file['size'],
            'error' => $file['error'],
        ];
    }
}

// Fungsi umum untuk mencatat log, bisa dipanggil dari mana pun
function logRequest($type = 'request', $postData = [], $errors = []) {
    global $logFile, $fileData;

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type, // 'request', 'validation_error', 'success', etc.
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'post_data'  => $postData,
        'file_data'  => $fileData
    ];

    if (!empty($errors)) {
        $logEntry['errors'] = $errors;
    }

    file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}
