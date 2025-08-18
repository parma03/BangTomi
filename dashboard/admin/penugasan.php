<?php
session_start();
include '../../db/koneksi.php';
include 'controller/PenugasanController.php';

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

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

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
                                <h5 class="card-header">Data Penugasan Kegiatan</h5>
                                <!-- Tombol di sebelah kanan -->
                                <button type="button" class="btn btn-icon btn-outline-primary me-3 add-penugasan-btn"
                                    data-bs-toggle="modal" data-bs-target="#penugasanFormModal">
                                    <span class="tf-icons bx bx-user-plus bx-22px"></span>
                                </button>
                            </div>
                            <div class="data-container card-datatable table-responsive pt-0">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Memuat data Penugasan Kegiatan...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Form Penugasan -->
                    <!-- Ganti modal form penugasan yang lama dengan yang baru ini -->
                    <div class="modal fade" id="penugasanFormModal" tabindex="-1"
                        aria-labelledby="penugasanFormModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-gradient text-white"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <h5 class="modal-title" id="penugasanFormModalLabel">
                                        <i class="fas fa-user-plus me-2"></i>Tambah Penugasan
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="penugasanForm">
                                    <div class="modal-body p-4">
                                        <input type="hidden" id="idPenugasan" name="idPenugasan">

                                        <!-- Alert untuk validasi -->
                                        <div id="formAlert" class="alert alert-danger d-none" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span id="alertMessage"></span>
                                        </div>

                                        <!-- Pilihan Kegiatan -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-4">
                                                    <label for="id_kegiatan" class="form-label fw-semibold">
                                                        <i class="fas fa-calendar-alt text-primary me-1"></i>Pilih Kegiatan
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select form-select-lg" id="id_kegiatan" name="id_kegiatan" required>
                                                        <option value="">Pilih Kegiatan</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Kegiatan wajib dipilih
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info jadwal kegiatan -->
                                        <div id="jadwalInfo" class="alert alert-info d-none mb-4" role="alert">
                                            <i class="fas fa-clock me-2"></i>
                                            <strong>Jadwal Kegiatan:</strong>
                                            <span id="jadwalText"></span>
                                        </div>

                                        <!-- Peringatan konflik jadwal -->
                                        <div id="conflictWarning" class="alert alert-warning d-none mb-4" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Peringatan:</strong>
                                            <span id="conflictText"></span>
                                        </div>

                                        <!-- Form untuk 2 kategori -->
                                        <div class="row">
                                            <!-- Petugas MC -->
                                            <div class="col-md-6">
                                                <div class="card border-primary h-100">
                                                    <div class="card-header bg-primary text-white">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-microphone me-2"></i>Petugas MC
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <label for="id_pegawai_mc" class="form-label fw-semibold">
                                                                <i class="fas fa-users text-primary me-1"></i>Pilih Petugas MC
                                                            </label>
                                                            <select class="form-select" id="id_pegawai_mc" name="id_pegawai_mc[]" multiple>
                                                                <option value="">Pilih Petugas MC</option>
                                                            </select>
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>Master of Ceremony - mengatur jalannya acara
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Petugas Protokol -->
                                            <div class="col-md-6">
                                                <div class="card border-secondary h-100">
                                                    <div class="card-header bg-secondary text-white">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-clipboard-list me-2"></i>Petugas Protokol
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <label for="id_pegawai_protokol" class="form-label fw-semibold">
                                                                <i class="fas fa-users text-secondary me-1"></i>Pilih Petugas Protokol
                                                            </label>
                                                            <select class="form-select" id="id_pegawai_protokol" name="id_pegawai_protokol[]" multiple>
                                                                <option value="">Pilih Petugas Protokol</option>
                                                            </select>
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>Mengatur tata cara dan prosedur acara
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Input Channel HT -->
                                        <div class="mb-4 mt-4">
                                            <div class="card border-warning">
                                                <div class="card-header bg-warning text-dark">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-radio me-2"></i>Channel HT (Handy Talky)
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="channel_ht" class="form-label fw-semibold">
                                                            <i class="fas fa-broadcast-tower text-warning me-1"></i>Channel HT
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="channel_ht" name="channel_ht"
                                                            placeholder="Contoh: Channel 1, Freq 145.500 MHz" required>
                                                        <div class="form-text">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Masukkan channel atau frekuensi HT yang akan digunakan selama kegiatan
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opsi Notifikasi Otomatis -->
                                        <div class="mb-4 mt-4">
                                            <div class="card border-success">
                                                <div class="card-header bg-success text-white">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-bell me-2"></i>Pengaturan Notifikasi
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="send_notification" name="send_notification" value="yes" checked>
                                                        <label class="form-check-label fw-semibold" for="send_notification">
                                                            <i class="fas fa-paper-plane text-success me-1"></i>Kirim notifikasi otomatis
                                                        </label>
                                                        <div class="form-text">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Petugas akan menerima notifikasi penugasan melalui Telegram Bot
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info Box -->
                                        <div class="alert alert-info border-0 rounded-3" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Informasi:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Field Kegiatan yang bertanda <span class="text-danger">*</span> wajib diisi</li>
                                                <li>Minimal pilih satu petugas (MC atau Protokol atau keduanya)</li>
                                                <li>Petugas tidak dapat ditugaskan ke kegiatan yang jadwalnya bersamaan</li>
                                                <li>Anda dapat memilih beberapa petugas untuk setiap kategori</li>
                                                <li>Channel HT untuk koordinasi komunikasi selama kegiatan</li>
                                                <li>Notifikasi akan dikirim otomatis jika opsi diaktifkan</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i>Batal
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                            <i class="fas fa-save me-1"></i>Simpan Penugasan
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

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="controller/PenugasanController.js"></script>

</body>

</html>