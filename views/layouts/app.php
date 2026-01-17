<?php  
    require_once __DIR__ . '/../../helpers/verify-token.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My App' ?></title>
    <?php view('layouts/head') ?> <!-- Link CSS -->
</head>
<body>
    <div id="global-loader">
		<div class="page-loader"></div>
	</div>

	<!-- Main Wrapper -->
	<div class="main-wrapper">
        <!-- Navbar -->
        <?php view('layouts/navbar') ?> 
        <!-- End Navbar -->
        <!-- Sidebar -->
        <?php 
            view('layouts/sidebar', [
                'active_menu' => $active_menu,
                'active_submenu' => $active_submenu ?? ''
            ]) 
        ?>

        <!-- End Sidebar -->
        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <!-- Content -->
            <?php view($content) ?> <!-- Konten halaman dinamis -->
            <!-- End Content -->

            <!-- Footer -->
            <?php view('layouts/footer') ?> 
            <!-- End Footer -->
        </div>
    </div>
    <!-- script -->
    <?php view('layouts/script') ?>
    <!-- End script -->
</body>
</html>
