<?php 
// Mulai sesi jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi HTMLPurifier
$config = HTMLPurifier_Config::createDefault();
$config->set('URI.AllowedSchemes', array('http' => true, 'https' => true));
$config->set('URI.DisableExternalResources', true);
$config->set('HTML.Allowed', 'p,b,a[href],i,img[src]');
$config->set('HTML.SafeIframe', true);
$config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%');

// Inisialisasi purifier
$sanitasi_input = new HTMLPurifier($config);

function hasXSS($string) {
    return preg_match('/<\s*script|on\w+\s*=|javascript:/i', $string);
}

function sanitizeInput($data) {
    global $sanitasi_input;
    $sanitized_data = [];

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized_data[$key] = [];
            foreach ($value as $sub_key => $sub_value) {
                if (hasXSS($sub_value)) {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Input mengandung kode berbahaya.'
                    ]);
                    exit;
                }
                $sanitized_data[$key][$sub_key] = $sanitasi_input->purify($sub_value);
            }
        } else {
            if (hasXSS($value)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Input mengandung kode berbahaya.'
                ]);
                exit;
            }
            $sanitized_data[$key] = $sanitasi_input->purify($value);
        }
    }

    return $sanitized_data;
}
?>
