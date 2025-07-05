<?php
session_start();
include '../../db/koneksi.php';
include 'controller/AdminController.php';

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
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>

    <!-- DataTables CSS with Responsive Extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css" />

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

        .photo-upload-area {
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

        .photo-upload-area::before {
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

        .photo-upload-area:hover {
            border-color: #007bff;
            background: linear-gradient(145deg, #e3f2fd, #f8f9fa);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
        }

        .photo-upload-area:hover::before {
            left: 100%;
        }

        .photo-upload-area.dragover {
            border-color: #28a745;
            background: linear-gradient(145deg, #d4edda, #f8f9fa);
            transform: scale(1.02);
        }

        .profile-upload-area {
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

        .profile-upload-area::before {
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

        .profile-upload-area:hover {
            border-color: #007bff;
            background: linear-gradient(145deg, #e3f2fd, #f8f9fa);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
        }

        .profile-upload-area:hover::before {
            left: 100%;
        }

        .profile-upload-area.dragover {
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

        .product-image {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.1);
        }

        .product-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-header">Data Administrator</h5>
                                <!-- Tombol di sebelah kanan -->
                                <button type="button" class="btn btn-icon btn-outline-primary me-3 add-admin-btn"
                                    data-bs-toggle="modal" data-bs-target="#addModal">
                                    <span class="tf-icons bx bx-user-plus bx-22px"></span>
                                </button>
                            </div>
                            <div class="data-container card-datatable table-responsive pt-0">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Memuat data Administrator...</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Modal Detail Admin - Responsive Version -->
                    <div class="modal fade" id="adminDetailModal" tabindex="-1" aria-labelledby="adminDetailModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="adminDetailModalLabel">
                                        <i class="bx bx-user-detail me-2"></i>Detail Administrator
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-3 p-md-4">
                                    <div id="adminDetailContent">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Memuat detail admin...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer flex-column flex-sm-row gap-2">
                                    <button type="button" class="btn btn-secondary w-100 w-sm-auto"
                                        data-bs-dismiss="modal">
                                        <i class="bx bx-x me-1"></i>Tutup
                                    </button>
                                    <button type="button" class="btn btn-primary w-100 w-sm-auto"
                                        onclick="editAdmin(currentAdminId)">
                                        <i class="bx bx-edit me-1"></i>Edit Admin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Form Admin - Responsive Version -->
                    <div class="modal fade" id="adminFormModal" tabindex="-1" aria-labelledby="adminFormModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-gradient text-white"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <h5 class="modal-title" id="adminFormModalLabel">
                                        <i class="fas fa-user-plus me-2"></i>Tambah Admin
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="adminForm" enctype="multipart/form-data">
                                    <div class="modal-body p-4">
                                        <input type="hidden" id="id" name="id">
                                        <input type="hidden" id="removeExistingProfile" name="removeExistingProfile"
                                            value="0">

                                        <!-- Alert untuk validasi -->
                                        <div id="formAlert" class="alert alert-danger d-none" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span id="alertMessage"></span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label fw-semibold">
                                                        <i class="fas fa-envelope text-primary me-1"></i>Email <span
                                                            class="text-danger">*</span>
                                                    </label>
                                                    <input type="email" class="form-control form-control-lg" id="email"
                                                        name="email" required placeholder="admin@example.com">
                                                    <div class="invalid-feedback">
                                                        Email wajib diisi dengan format yang benar
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="nama" class="form-label fw-semibold">
                                                        <i class="fas fa-user text-success me-1"></i>Nama Lengkap
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control form-control-lg" id="nama"
                                                        name="nama" required placeholder="Masukkan nama lengkap">
                                                    <div class="invalid-feedback">
                                                        Nama lengkap wajib diisi
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="nohp" class="form-label fw-semibold">
                                                        <i class="fas fa-phone text-info me-1"></i>No. HP <span
                                                            class="text-danger">*</span>
                                                    </label>
                                                    <input type="tel" class="form-control form-control-lg" id="nohp"
                                                        name="nohp" required placeholder="08xxxxxxxxxx">
                                                    <div class="invalid-feedback">
                                                        Nomor HP wajib diisi (minimal 10 digit)
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>Format: 08xxxxxxxxxx
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="password" class="form-label fw-semibold">
                                                        <i class="fas fa-lock text-warning me-1"></i>Password <span
                                                            class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control form-control-lg"
                                                            id="password" name="password"
                                                            placeholder="Masukkan password">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="togglePassword">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Password wajib diisi (minimal 6 karakter)
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>Minimal 6 karakter
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="confirm_passwords" class="form-label fw-semibold">
                                                        <i class="fas fa-lock text-warning me-1"></i>Konfirmasi Password
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="password" class="form-control form-control-lg"
                                                        id="confirm_passwords" name="confirm_passwords"
                                                        placeholder="Konfirmasi password">
                                                    <div class="invalid-feedback">
                                                        Konfirmasi password harus sama dengan password
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Current Profile Display (untuk edit mode) -->
                                        <div id="currentProfileDisplay" style="display: none;"></div>

                                        <!-- Profile Image Upload -->
                                        <div class="mb-3">
                                            <label for="profile" class="form-label fw-semibold">
                                                <i class="fas fa-camera text-primary me-1"></i>Foto Profile
                                                <small class="text-muted">(Opsional)</small>
                                            </label>
                                            <div class="profile-upload-area border-2 border-dashed border-secondary rounded-4 p-4 text-center position-relative"
                                                style="cursor: pointer; transition: all 0.3s ease;">
                                                <input type="file" class="form-control" id="profile" name="profile"
                                                    accept="image/jpeg,image/jpg,image/png,image/gif"
                                                    style="display: none;">

                                                <div id="uploadPlaceholder" class="upload-placeholder">
                                                    <div class="upload-icon">
                                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                    </div>
                                                    <h6 class="mb-2">Klik untuk memilih foto atau drag & drop di sini
                                                    </h6>
                                                    <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                                    <div class="mt-2">
                                                        <span class="badge bg-light text-dark">Rekomendasi:
                                                            300x300px</span>
                                                    </div>
                                                </div>

                                                <div id="profilePreview" style="display: none;" class="mt-3"></div>

                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                    id="removeProfileBtn" style="display: none;">
                                                    <i class="fas fa-trash me-1"></i>Hapus Foto
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="profileError" style="display: none;">
                                            </div>
                                        </div>

                                        <!-- Info Box -->
                                        <div class="alert alert-info border-0 rounded-3" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Informasi:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Field yang bertanda <span class="text-danger">*</span> wajib diisi
                                                </li>
                                                <li>Password akan di-enkripsi secara otomatis</li>
                                                <li>Foto profile bersifat opsional</li>
                                                <li>Email harus unik dan tidak boleh sama dengan data lain</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i>Batal
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                            <i class="fas fa-save me-1"></i>Simpan Admin
                                        </button>
                                    </div>
                                </form>
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

    <script src="controller/AdminController.js"></script>

</body>

</html>