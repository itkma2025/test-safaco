<div class="content">
    <!-- Breadcrumb -->
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Dashboard</h2>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="index.html"><i class="ti ti-smart-home"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- /Breadcrumb -->

    <!-- Welcome Wrap -->
    <div class="card border-0">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap pb-1">
            <div class="d-flex align-items-center mb-3">
                <span class="avatar avatar-xl flex-shrink-0">
                    <img src="<?= asset('img/profiles/avatar-31.jpg') ?>" class="rounded-circle" alt="img">
                </span>
                <div class="ms-3">
                    <h3 class="mb-2">Welcome Back, Adrian <a href="javascript:void(0);" class="edit-icon"><i class="ti ti-edit fs-14"></i></a></h3>
                    <p>You have <span class="text-primary text-decoration-underline">21</span> Pending Approvals & <span class="text-primary text-decoration-underline">14</span> Leave Requests</p>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap mb-1">
                <a href="#" class="btn btn-secondary btn-md me-2 mb-2" data-bs-toggle="modal" data-bs-target="#add_project"><i class="ti ti-square-rounded-plus me-1"></i>Add Project</a>
                <a href="#" class="btn btn-primary btn-md mb-2" data-bs-toggle="modal" data-bs-target="#add_leaves"><i class="ti ti-square-rounded-plus me-1"></i>Add Requests</a>
            </div>
        </div>
        <div class="card">
            <?php 
                // echo "<pre>";
                // print_r($_SERVER);
                // echo "</pre>";

                // echo "<pre>";
                // print_r($_SESSION);
                // echo "</pre>";

                // echo "Session ID dari cookie: " . ($_COOKIE['PHPSESSID'] ?? 'tidak ada') . "<br>";
                // echo "Session ID aktif: " . session_id() . "<br>";
            ?>
        </div>
    </div>
    <!-- /Welcome Wrap -->
</div>