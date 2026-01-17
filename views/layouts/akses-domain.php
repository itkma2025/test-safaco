 <?php
    // Ambil session
    $url_websites = $_SESSION['url_website'] ?? [];

    // Jika ternyata string, ubah jadi array
    if (is_string($url_websites)) {
        $url_websites = str_replace("'", "", $url_websites);
        $url_websites = array_map('trim', explode(",", $url_websites));
    }

    // Pastikan selalu array
    if (!is_array($url_websites)) {
        $url_websites = [];
    }

    // Mapping domain/port ke icon + name
    $mapping = [
        'localhost:8083' => [
            'path' => 'http://localhost:8083/assets/images/domain/SSO.png',
            'name' => 'IT'
        ],
        'localhost:8082' => [
            'path' => 'http://localhost:8083/assets/images/domain/ECAT.png',
            'name' => 'ECAT'
        ],
        'localhost:8080' => [
            'path' => 'http://localhost:8083/assets/images/domain/KMA.png',
            'name' => 'Karsa'
        ],
        'localhost:8084' => [
            'path' => 'http://localhost:8083/assets/images/domain/SKM.png',
            'name' => 'Karsa'
        ],

        // Hosting test mapping (FULL URL)
        'test-it.mandirialkesindo.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/SSO.png', 
            'name' => 'IT'
        ],
        'test-bertamed.mandirialkesindo.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/BUM.png', 
            'name' => 'BUM'
        ],
        'test-ecat.mandirialkesindo.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/ECAT.png', 
            'name' => 'ECAT'
        ],
        'test-inventory.mandirialkesindo.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/KMA.png', 
            'name' => 'Karsa'
        ],
        'test-sticker.mandirialkesindo.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/logo_sticker.png', 
            'name' => 'Sticker'
        ],
        'test-bank.mandirialkesindo.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/BI.png', 
            'name' => 'Bank'
        ],

        // Safaco staging
        'stagging-production.safacokaryamedika.co.id' => [
            'path' => 'https://test-it.mandirialkesindo.co.id/assets/images/domain/SKM.png',
            'name' => 'Safaco'
        ],
    ];

    // Domain aktif (biar nggak ditampilkan)
    $current_domain = $_SERVER['HTTP_HOST'];

    // Siapkan hasil akhir
    $url_accessed = [];
    foreach ($url_websites as $url) {
        $parts = parse_url($url);

        $key = $parts['host'];
        if (isset($parts['port'])) {
            $key .= ':' . $parts['port'];
        }
        if (isset($parts['path']) && $parts['path'] !== '/') {
            $key .= $parts['path'];
        }

        // Skip domain yang sedang aktif
        if ($parts['host'] === $current_domain) {
            continue;
        }

        if (isset($mapping[$key])) {
            $icon = $mapping[$key]['path']; // langsung pakai full URL dari mapping
            $url_accessed[] = [
                'url_website' => $url,
                'icon'        => $icon,
                'name'        => $mapping[$key]['name'],
            ];
        }
    }
?>