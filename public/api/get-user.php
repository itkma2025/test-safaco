<?php
use GuzzleHttp\Client;

function getUserMap(): array {
    $client = new Client();
    $tokenJwt = require base_path('helpers/jwt-token.php');
    $domainSSO = DOMAIN_SSO;

    $userMap = [];

    if (!$tokenJwt || !$domainSSO) {
        return $userMap;
    }

    try {
        $response = $client->get($domainSSO . 'api/data-user.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt
            ],
            'http_errors' => false
        ]);

        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);

        // DEBUG RESPONSE BODY
        // echo "<pre>";
        // print_r($body);
        // echo "</pre>";

        if ($status === 200 && isset($result['data']) && is_array($result['data'])) {
            foreach ($result['data'] as $item) {
                if (!empty($item['id_user']) && !empty($item['nama_user'])) {
                    $userMap[$item['id_user']] = $item['nama_user'];
                }
            }
        }
    } catch (\Exception $e) {
        // Log atau abaikan error jika perlu
    }

    return $userMap;
}
