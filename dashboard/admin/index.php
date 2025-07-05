<?php
session_start();
include '../../db/koneksi.php';
include 'controller/IndexController.php';
// Ambil data app setting
$appSetting = getAppSetting($pdo);
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Dashboard Panel | <?php echo htmlspecialchars($appSetting['name']); ?></title>

    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon"
        href="../../assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
    <!-- SweetAlert2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.css"
        rel="stylesheet" />

    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <?php include 'components/menu.php'; ?>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include 'components/navbar.php'; ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Alert Container -->
                        <div id="alertContainer"></div>

                        <!-- Welcome Card -->
                        <div class="row">
                            <div class="col-lg-12 mb-4 order-0">
                                <div class="card">
                                    <div class="d-flex align-items-end row">
                                        <div class="col-sm-7">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">Selamat Datang! 🎉</h5>
                                                <p class="mb-4">
                                                    Dashboard <span
                                                        class="fw-bold"><?php echo htmlspecialchars($appSetting['name']); ?></span>
                                                    untuk mengelola kegiatan dan penugasan pegawai.
                                                </p>
                                                <a href="javascript:;" class="btn btn-sm btn-outline-primary"
                                                    onclick="refreshDashboard()">
                                                    <i class="bx bx-refresh me-1"></i>Refresh Data
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-sm-5 text-center text-sm-left">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <img src="assets/img/illustrations/man-with-laptop-light.png"
                                                    height="140" alt="View Badge User"
                                                    data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                                    data-app-light-img="illustrations/man-with-laptop-light.png" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-lg-2 col-md-4 col-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-calendar-event text-primary"
                                                    style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Total Kegiatan</span>
                                        <h3 class="card-title mb-2" id="totalKegiatan">-</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-time-five text-warning" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Pending</span>
                                        <h3 class="card-title mb-2" id="kegiatanPending">-</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Selesai</span>
                                        <h3 class="card-title mb-2" id="kegiatanSelesai">-</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-user text-info" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Total Petugas</span>
                                        <h3 class="card-title mb-2" id="totalPetugas">-</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-calendar-today text-danger"
                                                    style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Hari Ini</span>
                                        <h3 class="card-title mb-2" id="kegiatanHariIni">-</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-calendar-week text-purple" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Minggu Ini</span>
                                        <h3 class="card-title mb-2" id="kegiatanMingguIni">-</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar and Upcoming Events -->
                        <div class="row">
                            <!-- Calendar -->
                            <div class="col-lg-8 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h5 class="card-title mb-0">
                                                    <i class="bx bx-calendar me-2"></i>Kalender Kegiatan
                                                </h5>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Enhanced Filter Section -->
                                    <div class="card-body">
                                        <div id="calendar"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upcoming Events -->
                            <div class="col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-time-five me-2"></i>Kegiatan Mendatang
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="upcomingEvents">
                                            <div class="text-center py-3">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Memuat data...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <?php include 'components/footer.php'; ?>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailModalLabel">
                        <i class="bx bx-calendar-event me-2"></i>Detail Kegiatan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventDetailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboards-analytics.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/id.global.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.js"></script>

    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script src="controller/IndexController.js"></script>

</body>

</html>