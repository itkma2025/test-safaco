<?php  
    $sso_url = DOMAIN_SSO;
?>

<!-- Header -->
<div class="header">
    <div class="main-header">
        <!-- Mobile Header -->
        <div class="header-left">
            <!-- Sisa Sesi -->
            <a href="#" class="logo">
               <!-- Isi jika di perlukan -->
            </a>
            <!-- End Sisa Sesi -->
            <!-- Fullscreen -->
            <div class="ms-3">
                <a href="#">
                    Sisa sesi: <span class="p-2 countdown"></span>
                </a>
            </div>
            <!-- End Fullscreen -->
            <!-- Akses Domain -->
            <div class="dropdown ms-0">
                <a href="#" class="btn btn-menubar" data-bs-toggle="dropdown">
                    <i class="ti ti-layout-grid" style="font-size: 22px;"></i>
                </a>
                <div class="dropdown-menu dropdown-lg">
                    <div class="card mb-0 border-0 shadow-none">
                        <div class="card-header">
                            <h4>Akses Domain</h4>
                        </div>						
                        <div class="card-body pb-1">		
                           	
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Akses Domain -->
             <!-- Notification -->
            <div class="notification_item me-2">
                <a href="#" class="btn btn-menubar position-relative me-1 ms-1" id="notification_popup"
                    data-bs-toggle="dropdown">
                    <i class="ti ti-bell" style="font-size: 22px;"></i>
                    <span class="badge bg-info rounded-pill d-flex align-items-center justify-content-center header-badge">5</span>
                </a>
                <div class="dropdown-menu notification-dropdown p-2">
                    <div class="noti-content">
                        <div class="d-flex flex-column">
                            <div class="border-bottom mb-3 pb-3">
                                <a href="#">
                                    <div class="d-flex">
                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                            <img src="<?= asset('img/profiles/avatar-27.jpg') ?>" alt="Profile">
                                        </span>
                                        <div class="flex-grow-1">
                                            <p class="mb-1">
                                                <span class="text-dark fw-semibold">Shawn</span>
                                                performance in Math is below the threshold.</p>
                                            <span>Just Now</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="border-bottom mb-3 pb-3">
                                <a href="activity.html" class="pb-0">
                                    <div class="d-flex">
                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                            <img src="<?= asset('img/profiles/avatar-23.jpg') ?>" alt="Profile">
                                        </span>
                                        <div class="flex-grow-1">
                                            <p class="mb-1">
                                                <span class="text-dark fw-semibold">Sylvia</span> added
                                                appointment on 02:00 PM
                                            </p>
                                            <span>10 mins ago</span>
                                            <div
                                                class="d-flex justify-content-start align-items-center mt-1">
                                                <span class="btn btn-light btn-sm me-2">Deny</span>
                                                <span class="btn btn-primary btn-sm">Approve</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="border-bottom mb-3 pb-3">
                                <a href="activity.html">
                                    <div class="d-flex">
                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                            <img src="<?= asset('img/profiles/avatar-25.jpg') ?>" alt="Profile">
                                        </span>
                                        <div class="flex-grow-1">
                                            <p class="mb-1">New student record <span class="text-dark fw-semibold"> George</span> is created by <span class="text-dark fw-semibold">Teressa</span></p>
                                            <span>2 hrs ago</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="border-0 mb-3 pb-0">
                                <a href="activity.html">
                                    <div class="d-flex">
                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                            <img src="<?= asset('img/profiles/avatar-01.jpg') ?>" alt="Profile">
                                        </span>
                                        <div class="flex-grow-1">
                                            <p class="mb-1">A new teacher record for <span class="text-dark fw-semibold">Elisa</span> </p>
                                            <span>09:45 AM</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex p-0">
                        <a href="#" class="btn btn-light w-100 me-2">Cancel</a>
                        <a href="activity.html" class="btn btn-primary w-100">View All</a>
                    </div>
                </div>
            </div>
            <!-- End Notification -->
            <!-- Profile -->
            <div class="dropdown profile-dropdown none me-3">
                <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center border-none"
                    data-bs-toggle="dropdown">
                    <span class="avatar avatar-md online">
                        <img src="<?= asset('img/profiles/avatar-12.jpg') ?>" alt="Img" class="img-fluid rounded-circle">
                    </span>
                </a>
                <div class="dropdown-menu">
                    <div class="card mb-0">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md me-2 avatar-rounded">
                                    <img src="<?= asset('img/profiles/avatar-12.jpg') ?>" alt="img">
                                </span>
                                <div>
                                    <h5 class="mb-0"><?php echo $_SESSION['nama_user'] ?></h5>
                                    <p class="fs-12 fw-medium mb-0"><?php echo $_SESSION['role'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="profile.html">
                                <i class="ti ti-user-circle me-1"></i>My Profile
                            </a>
                            <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="<?= $sso_url . 'logout.php?url=' . $_SESSION['current_url'] ?>">
                                <i class="ti ti-login me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Profile -->
        </div>
        <!-- Mobile Menu Button -->
        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>
        <!-- End Mobile Menu Button -->
        <!-- End Mobile Header -->

        <div class="header-user">
            <div class="nav user-menu nav-list">
                <div class="me-auto d-flex align-items-center" id="header-search">
                     <!-- Isi Bagian Ini -->
                </div>
                <div class="d-flex align-items-center">	
                    <!-- Sisa Sesi -->
                    <div class="me-1">
                        <a href="#" class="logo">
                            Sisa sesi: <span class="p-2 countdown"></span>
                        </a>
                    </div>
                    <!-- End Sisa Sesi -->
                    <!-- Fullscreen -->
                    <div class="me-1">
                        <a href="#" class="btn btn-menubar btnFullscreen">
                            <i class="ti ti-maximize" style="font-size: 22px;"></i>
                        </a>
                    </div>
                    <!-- End Fullscreen -->
                    <!-- Akses Domain -->
                    <?php require_once __DIR__ . "/akses-domain.php" ?>
                    <div class="dropdown me-1">
                        <a href="#" class="btn btn-menubar" data-bs-toggle="dropdown">
                            <i class="ti ti-layout-grid" style="font-size: 22px;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-lg dropdown-menu-end">
                            <div class="card mb-0 border-0 shadow-none">
                                <div class="card-header">
                                    <h4>Akses Domain</h4>
                                </div>						
                                <div class="card-body pb-1">		
                                    <div class="row">
                                        <?php if (!empty($url_accessed)): ?>
                                            <?php foreach ($url_accessed as $website): ?>
                                                <div class="col-sm-6">
                                                    <a href="<?= htmlspecialchars($website['url_website']) ?>" 
                                                    target="_blank"
                                                    class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <img src="<?= htmlspecialchars($website['icon']) ?>"
                                                                alt="<?= htmlspecialchars($website['name']) ?>"
                                                                style="width: 22px; height: 22px; margin-right: 8px;">
                                                            <?= htmlspecialchars($website['name']) ?>
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12 text-muted text-center">
                                                <em>Tidak ada akses domain</em>
                                            </div>
                                        <?php endif; ?>
                                    </div>		
                                </div>
                            </div>
                        </div>
                    </div> 
                    <!-- End Akses Domain -->
                    <!-- Notification -->
                    <div class="me-1 notification_item">
                        <a href="#" class="btn btn-menubar position-relative me-1" id="notification_popup"
                            data-bs-toggle="dropdown">
                            <i class="ti ti-bell" style="font-size: 22px;"></i>
                            <span class="badge bg-info rounded-pill d-flex align-items-center justify-content-center header-badge">5</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown p-4">
                            <div class="d-flex align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
                                <h4 class="notification-title">Notifications (2)</h4>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="text-primary fs-15 me-3 lh-1">Mark all as read</a>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="bg-white dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            <i class="ti ti-calendar-due me-1"></i>Today
                                        </a>
                                        <ul class="dropdown-menu mt-2 p-3">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                    This Week
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                    Last Week
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                    Last Month
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="noti-content">
                                <div class="d-flex flex-column">
                                    <div class="border-bottom mb-3 pb-3">
                                        <a href="activity.html">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="<?= asset('img/profiles/avatar-27.jpg') ?>" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1"><span
                                                            class="text-dark fw-semibold">Shawn</span>
                                                        performance in Math is below the threshold.</p>
                                                    <span>Just Now</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="border-bottom mb-3 pb-3">
                                        <a href="activity.html" class="pb-0">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="<?= asset('img/profiles/avatar-23.jpg') ?>" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1"><span
                                                            class="text-dark fw-semibold">Sylvia</span> added
                                                        appointment on 02:00 PM</p>
                                                    <span>10 mins ago</span>
                                                    <div
                                                        class="d-flex justify-content-start align-items-center mt-1">
                                                        <span class="btn btn-light btn-sm me-2">Deny</span>
                                                        <span class="btn btn-primary btn-sm">Approve</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="border-bottom mb-3 pb-3">
                                        <a href="activity.html">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="<?= asset('img/profiles/avatar-25.jpg') ?>" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1">New student record <span class="text-dark fw-semibold"> George</span> is created by <span class="text-dark fw-semibold">Teressa</span></p>
                                                    <span>2 hrs ago</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="border-0 mb-3 pb-0">
                                        <a href="activity.html">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="<?= asset('img/profiles/avatar-01.jpg') ?>" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1">A new teacher record for <span class="text-dark fw-semibold">Elisa</span> </p>
                                                    <span>09:45 AM</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex p-0">
                                <a href="#" class="btn btn-light w-100 me-2">Cancel</a>
                                <a href="activity.html" class="btn btn-primary w-100">View All</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Notification -->
                    <!-- Profile -->
                    <div class="dropdown profile-dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                            data-bs-toggle="dropdown">
                            <span class="avatar avatar-lg online">
                                <img src="<?= asset('img/profiles/avatar-12.jpg') ?>" alt="Img" class="img-fluid rounded-circle">
                            </span>
                        </a>
                        <div class="dropdown-menu shadow-none" style="min-width: 300px;">
                            <div class="card mb-0">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-lg me-2 avatar-rounded">
                                            <img src="<?= asset('img/profiles/avatar-12.jpg') ?>" alt="img">
                                        </span>
                                        <div>
                                            <h5 class="mb-0"><?php echo $_SESSION['nama_user'] ?></h5>
                                            <p class="fs-12 fw-medium mb-0"><?php echo $_SESSION['role'] ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="profile.html">
                                        <i class="ti ti-user-circle me-1"></i>My Profile
                                    </a>
                                    <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="<?php echo $sso_url . 'logout.php?url=' . $_SESSION['current_url'] ?>"">
                                        <i class="ti ti-login me-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Profile -->
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /Header -->