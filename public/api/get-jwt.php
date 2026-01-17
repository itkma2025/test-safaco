<?php  
    require_once __DIR__ . "/../../helpers/domain.php";
    $domain_sso = DOMAIN_SSO;

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST'];
    $current_domain = $protocol . $host;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verify Token</title>
</head>
<body>
    <pre id="output" style="display: none;">Memuat token dari Domain A...</pre>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('<?php echo $domain_sso; ?>/api/send-jwt.php', {
                method: 'GET',
                credentials: 'include' // Penting agar browser kirim cookie ke Domain A
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                document.getElementById('output').textContent = JSON.stringify(data, null, 2);

                const token = data?.data?.jwt_token;

                if (token) {
                    // Jika token ditemukan, redirect ke create-cookie
                    window.location.href = `../proses/create-cookie.php?token=${encodeURIComponent(token)}`;
                } else {
                    // Jika token tidak ditemukan, redirect ke login
                    window.location.href = '<?= $domain_sso ?>logout.php?url=<?= urlencode($current_domain) ?>';
                }
            })
            .catch(error => {
                document.getElementById('output').textContent = 'Gagal ambil token: ' + error.message;
                console.error('Fetch Error:', error);
                // Redirect juga ke login jika terjadi error
                window.location.href = '<?= $domain_sso ?>logout.php?url=<?= urlencode($current_domain) ?>';
            });
        });
    </script>
</body>
</html>
