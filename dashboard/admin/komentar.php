<?php
session_start();
include '../../db/koneksi.php';
include 'controller/KomentarController.php';

// Ambil data app setting
$appSetting = getAppSetting($pdo);
checkAdminAccess();

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Kelola Komentar | <?php echo htmlspecialchars($appSetting['name']); ?></title>

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
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>

    <!-- FontAwesome for star ratings -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- DataTables CSS with Responsive Extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css" />

    <!-- SweetAlert2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css"
        rel="stylesheet">

    <style>
        /* Additional responsive styles */
        @media (max-width: 768px) {
            .col-lg-6 {
                margin-bottom: 20px;
            }
        }

        .komentar-text {
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .stars {
            font-size: 0.8rem;
        }

        .card-header {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .btn-group .btn {
            margin: 0 1px;
        }

        /* Custom card styles */
        .card-tersembunyi {
            border-left: 4px solid #ffc107;
        }

        .card-ditampilkan {
            border-left: 4px solid #28a745;
        }

        /* Tooltip style improvements */
        .tooltip {
            font-size: 0.75rem;
        }

        /* Loading animation */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Custom alert styles */
        .custom-alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        }
    </style>
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
                        <!-- Header -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h2 class="card-title mb-2">
                                            <i class="bx bx-message-dots text-primary me-2"></i>
                                            Kelola Komentar Website
                                        </h2>
                                        <p class="card-text text-muted">
                                            Kelola komentar yang masuk dari pengunjung website. Anda dapat menampilkan atau menyembunyikan komentar sesuai kebutuhan.
                                        </p>
                                        <div class="row text-center mt-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="badge bg-warning me-2">Tersembunyi</div>
                                                    <small class="text-muted">Komentar yang belum ditampilkan di website</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="badge bg-success me-2">Ditampilkan</div>
                                                    <small class="text-muted">Komentar yang sudah ditampilkan di website</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Komentar Cards -->
                        <div class="row">
                            <!-- Komentar Tersembunyi -->
                            <div class="col-lg-6">
                                <div class="card card-tersembunyi h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
                                        <h5 class="mb-0">
                                            <i class="bx bx-hide me-2"></i>
                                            Komentar Tersembunyi
                                        </h5>
                                        <button type="button" class="btn btn-outline-dark btn-sm" onclick="loadKomentarTersembunyi()">
                                            <i class="bx bx-refresh"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="komentarTersembunyiContainer" class="data-container">
                                            <div class="text-center py-4">
                                                <div class="spinner-border text-warning" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Memuat komentar tersembunyi...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Komentar Ditampilkan -->
                            <div class="col-lg-6">
                                <div class="card card-ditampilkan h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
                                        <h5 class="mb-0">
                                            <i class="bx bx-show me-2"></i>
                                            Komentar Ditampilkan
                                        </h5>
                                        <button type="button" class="btn btn-outline-light btn-sm" onclick="loadKomentarDitampilkan()">
                                            <i class="bx bx-refresh"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="komentarDitampilkanContainer" class="data-container">
                                            <div class="text-center py-4">
                                                <div class="spinner-border text-success" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Memuat komentar ditampilkan...</p>
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

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboards-analytics.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- DataTables JS with Responsive Extension -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.js"></script>

    <script src="controller/KomentarController.js"></script>

</body>

</html>