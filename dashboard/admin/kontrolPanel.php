<?php
session_start();
include '../../db/koneksi.php';
include 'controller/AppSettingController.php';

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

    <title>App Setting Panel | <?php echo htmlspecialchars($appSetting['name']); ?></title>

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

    <!-- SweetAlert2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css"
        rel="stylesheet">

    <style>
        /* Additional responsive styles for the modal */
        @media (max-width: 768px) {
            .modal-xl {
                max-width: 95%;
                margin: 10px auto;
            }

            .modal-body {
                padding: 15px !important;
            }

            .badge {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .modal-xl {
                max-width: 98%;
                margin: 5px auto;
            }

            .modal-header h5 {
                font-size: 1rem;
            }
        }

        /* Profile image responsive sizing */
        .modal-body img.rounded-circle {
            max-width: 80px;
            max-height: 80px;
        }

        @media (max-width: 768px) {
            .modal-body img.rounded-circle {
                max-width: 60px;
                max-height: 60px;
            }
        }

        @media (max-width: 576px) {
            .modal-body img.rounded-circle {
                max-width: 50px;
                max-height: 50px;
            }
        }

        /* Text break for long content */
        .text-break {
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        /* Responsive card spacing */
        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }

            .row.g-3 {
                --bs-gutter-x: 1rem;
                --bs-gutter-y: 1rem;
            }
        }

        .file-upload-area {
            border: 3px dashed #e9ecef;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #f8f9fa, #ffffff);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .file-upload-area::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.8),
                    transparent);
            transition: left 0.5s;
        }

        .file-upload-area:hover {
            border-color: #007bff;
            background: linear-gradient(145deg, #e3f2fd, #f8f9fa);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
        }

        .file-upload-area:hover::before {
            left: 100%;
        }

        .file-upload-area.dragover {
            border-color: #28a745;
            background: linear-gradient(145deg, #d4edda, #f8f9fa);
            transform: scale(1.02);
        }

        .upload-placeholder {
            pointer-events: none;
        }

        .upload-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .preview-container {
            max-width: 200px;
            max-height: 200px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .preview-container img,
        .preview-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .current-file-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            text-align: center;
        }

        .file-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .media-preview-modal .modal-dialog {
            max-width: 90vw;
            width: auto;
        }

        .media-preview-modal .modal-body {
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000;
        }

        .media-preview-modal img,
        .media-preview-modal video {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
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
                        <div class="row">
                            <!-- Current App Setting Display -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-cog me-2"></i>App Setting Saat Ini
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="current-file-display">
                                            <h6 class="text-white mb-3">
                                                <i class="bx bx-info-circle me-2"></i>Informasi App
                                            </h6>

                                            <div class="mb-3">
                                                <strong>Nama Aplikasi:</strong><br>
                                                <span class="h5 text-white"><?php echo htmlspecialchars($appSetting['name']); ?></span>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Logo Saat Ini:</strong><br>
                                                <?php if (!empty($appSetting['logo'])): ?>
                                                    <div class="preview-container mt-2">
                                                        <img src="../../assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>"
                                                            alt="Current Logo"
                                                            class="current-logo-preview"
                                                            style="cursor: pointer;"
                                                            onclick="showMediaPreview('../../assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>', 'image', 'Current Logo')">
                                                    </div>
                                                    <small class="text-white-50 mt-2 d-block">Klik untuk memperbesar</small>
                                                <?php else: ?>
                                                    <div class="alert alert-warning mt-2">
                                                        <i class="bx bx-image-alt me-2"></i>Belum ada logo
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($appSetting['name_header']) || !empty($appSetting['description_header'])): ?>
                                                <div class="file-info">
                                                    <h6 class="text-white mb-2">Header Information:</h6>
                                                    <?php if (!empty($appSetting['name_header'])): ?>
                                                        <p class="mb-1"><strong>Header Name:</strong> <?php echo htmlspecialchars($appSetting['name_header']); ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($appSetting['description_header'])): ?>
                                                        <p class="mb-0"><strong>Header Description:</strong> <?php echo htmlspecialchars($appSetting['description_header']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($appSetting['background_header'])): ?>
                                                <div class="file-info">
                                                    <h6 class="text-white mb-2">Background Header:</h6>
                                                    <div class="preview-container">
                                                        <img src="../../assets/img/appsetting/<?php echo htmlspecialchars($appSetting['background_header']); ?>"
                                                            alt="Background Header"
                                                            style="cursor: pointer;"
                                                            onclick="showMediaPreview('../../assets/img/appsetting/<?php echo htmlspecialchars($appSetting['background_header']); ?>', 'image', 'Background Header')">
                                                    </div>
                                                    <small class="text-white-50 mt-2 d-block">Klik untuk memperbesar</small>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($appSetting['video_header'])): ?>
                                                <div class="file-info">
                                                    <h6 class="text-white mb-2">Video Header:</h6>
                                                    <div class="preview-container">
                                                        <video controls style="cursor: pointer;"
                                                            onclick="showMediaPreview('../../assets/img/appsetting/<?php echo htmlspecialchars($appSetting['video_header']); ?>', 'video', 'Video Header')">
                                                            <source src="../../assets/img/appsetting/<?php echo htmlspecialchars($appSetting['video_header']); ?>" type="video/mp4">
                                                        </video>
                                                    </div>
                                                    <small class="text-white-50 mt-2 d-block">Klik untuk memperbesar</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- App Setting Form -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-edit me-2"></i>Update App Setting
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="appSettingForm" enctype="multipart/form-data">
                                            <input type="hidden" name="id_appsetting" value="<?php echo $appSetting['id_appsetting']; ?>">

                                            <!-- App Name -->
                                            <div class="mb-3">
                                                <label for="app_name" class="form-label fw-semibold">
                                                    <i class="bx bx-apps text-primary me-1"></i>Nama Aplikasi
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="app_name"
                                                    name="app_name" value="<?php echo htmlspecialchars($appSetting['name']); ?>" required>
                                            </div>

                                            <!-- Logo Upload -->
                                            <div class="mb-3">
                                                <label for="logo" class="form-label fw-semibold">
                                                    <i class="bx bx-image text-success me-1"></i>Logo Aplikasi
                                                </label>
                                                <div class="file-upload-area" id="logoUploadArea">
                                                    <div class="upload-placeholder">
                                                        <i class="bx bx-cloud-upload upload-icon" style="font-size: 3rem; color: #007bff;"></i>
                                                        <h6 class="mt-3 mb-2">Upload Logo</h6>
                                                        <p class="text-muted mb-0">Klik atau drag & drop gambar logo</p>
                                                        <small class="text-muted">Maksimal 2MB (JPG, PNG, GIF)</small>
                                                    </div>
                                                </div>
                                                <input type="file" id="logo" name="logo" class="d-none" accept="image/*">
                                                <div id="logoPreview" class="mt-3" style="display: none;"></div>
                                            </div>

                                            <!-- Header Name -->
                                            <div class="mb-3">
                                                <label for="header_name" class="form-label fw-semibold">
                                                    <i class="bx bx-text text-info me-1"></i>Nama Header
                                                </label>
                                                <input type="text" class="form-control" id="header_name"
                                                    name="header_name" value="<?php echo htmlspecialchars($appSetting['name_header'] ?? ''); ?>">
                                            </div>

                                            <!-- Header Description -->
                                            <div class="mb-3">
                                                <label for="header_description" class="form-label fw-semibold">
                                                    <i class="bx bx-detail text-warning me-1"></i>Deskripsi Header
                                                </label>
                                                <input type="text" class="form-control" id="header_description"
                                                    name="header_description" value="<?php echo htmlspecialchars($appSetting['description_header'] ?? ''); ?>">
                                            </div>

                                            <!-- Background Header Upload -->
                                            <div class="mb-3">
                                                <label for="background_header" class="form-label fw-semibold">
                                                    <i class="bx bx-image-alt text-secondary me-1"></i>Background Header
                                                </label>
                                                <div class="file-upload-area" id="backgroundUploadArea">
                                                    <div class="upload-placeholder">
                                                        <i class="bx bx-image-add upload-icon" style="font-size: 3rem; color: #6c757d;"></i>
                                                        <h6 class="mt-3 mb-2">Upload Background</h6>
                                                        <p class="text-muted mb-0">Klik atau drag & drop gambar background</p>
                                                        <small class="text-muted">Maksimal 5MB (JPG, PNG, GIF)</small>
                                                    </div>
                                                </div>
                                                <input type="file" id="background_header" name="background_header" class="d-none" accept="image/*">
                                                <div id="backgroundPreview" class="mt-3" style="display: none;"></div>
                                            </div>

                                            <!-- Video Header Upload -->
                                            <div class="mb-4">
                                                <label for="video_header" class="form-label fw-semibold">
                                                    <i class="bx bx-video text-danger me-1"></i>Video Header
                                                </label>
                                                <div class="file-upload-area" id="videoUploadArea">
                                                    <div class="upload-placeholder">
                                                        <i class="bx bx-video-plus upload-icon" style="font-size: 3rem; color: #dc3545;"></i>
                                                        <h6 class="mt-3 mb-2">Upload Video</h6>
                                                        <p class="text-muted mb-0">Klik atau drag & drop video header</p>
                                                        <small class="text-muted">Maksimal 10MB (MP4, AVI, MOV)</small>
                                                    </div>
                                                </div>
                                                <input type="file" id="video_header" name="video_header" class="d-none" accept="video/*">
                                                <div id="videoPreview" class="mt-3" style="display: none;"></div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                                    <i class="bx bx-save me-1"></i>Update App Setting
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Media Preview Modal -->
                    <div class="modal fade media-preview-modal" id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title" id="mediaPreviewModalLabel">
                                        <i class="bx bx-show me-2"></i>Media Preview
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0 text-center">
                                    <div id="mediaPreviewContent" class="d-flex justify-content-center align-items-center" style="min-height: 400px;"></div>
                                </div>
                                <div class="modal-footer bg-dark">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bx bx-x me-1"></i>Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

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

    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.js"></script>

    <script src="controller/AppSettingController.js"></script>

</body>

</html>