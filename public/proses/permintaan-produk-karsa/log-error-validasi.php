<?php  
    // Ambil file log yang sedang dipakai
    $logDir = __DIR__ . '/logs'; 
    $logFile = $logDir . '/error_log_validation' . date('Y-m-d') . '.log';

    // Jika folder belum ada → buat folder
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    // Jika file belum ada → buat file kosong
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '');
    }

    // Tambahkan entry error ke log
    $logError = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type'          => 'validation_error',
        'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'post_data'     => $_POST,
        'errors'        => $validator->errors()->all()
    ];

    file_put_contents($logFile, json_encode($logError, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

    // Kirim response ke frontend
    $allErrors = implode(" ", $validator->errors()->all());
    echo json_encode([
        'status'    => 'error',
        'message'   => $allErrors
    ]);

    exit;

?>