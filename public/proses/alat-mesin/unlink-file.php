<?php  
require_once __DIR__ . '/../../../helpers/basepath.php';
require_once base_path('public/vendor/autoload.php');
require_once __DIR__ . '/log-data.php';

// Library validasi input
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
// Library API
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Tampilkan filename untuk di kirim ke api
$data_gambar = $connSafaco->table('alat_mesin_gambar')
                            ->select('filename')
                            ->where('id_alat_mesin', $id_alat_mesin)
                            ->first();
$filename = $data_gambar->filename;

if($filename){
    // File path
    $file_path  = 'file-safaco/alat-mesin/';

    $client = new Client();
    try {
        $response = $client->request('POST', $domain_sso . 'api/unlink-file.php', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt
            ],
            'multipart' => [
                [
                    'name'     => 'filename',
                    'contents' => $filename
                ],
                [
                    'name'     => 'folder',
                    'contents' =>  $file_path // folder target di SSO
                ]
            ],
            'verify' => $verify
        ]);

        // Decode response dari API
        $result = json_decode($response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Respons dari API bukan JSON valid.',
                'raw_body' => (string) $response->getBody()
            ]);
            exit;
        }

    } catch (RequestException $e) {
        $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
        $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memanggil API enkripsi.',
            'http_status' => $statusCode,
            'error_response' => $errorBody,
            'exception' => $e->getMessage()
        ]);
        exit;
    } catch (\Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan tidak terduga saat unlink file.',
            'exception' => $e->getMessage()
        ]);  
        exit;
    }
}
?>