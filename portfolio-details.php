<?php
// Memulai session dan koneksi database  
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'db/koneksi.php';

// Include HomeController untuk menggunakan fungsi getAppSetting
include 'controller/HomeController.php';

// Ambil data app setting
$appSetting = getAppSetting($pdo);

// Get kegiatan ID from URL parameter
$kegiatan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Function to get kegiatan detail
function getKegiatanDetail($pdo, $id)
{
    try {
        $sql = "SELECT 
                    k.id_kegiatan,
                    k.judul_kegiatan,
                    k.deksripsi_kegiatan,
                    k.jadwal_kegiatan,
                    k.thumbnails_kegiatan,
                    k.kehadiran_kegiatan,
                    k.status_kegiatan,
                    k.created_at,
                    GROUP_CONCAT(CONCAT(u.nama, '|', u.nohp) SEPARATOR ';') as petugas_info
                FROM tb_kegiatan k
                LEFT JOIN tb_penugasan p ON k.id_kegiatan = p.id_kegiatan
                LEFT JOIN tb_user u ON p.id_pegawai = u.id
                WHERE k.id_kegiatan = ?
                GROUP BY k.id_kegiatan, k.judul_kegiatan, k.deksripsi_kegiatan, k.jadwal_kegiatan, k.thumbnails_kegiatan, k.kehadiran_kegiatan, k.status_kegiatan, k.created_at";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Process petugas info
            $petugas_array = [];
            if (!empty($result['petugas_info'])) {
                $petugas_list = explode(';', $result['petugas_info']);
                foreach ($petugas_list as $petugas) {
                    $petugas_data = explode('|', $petugas);
                    if (count($petugas_data) == 2) {
                        $petugas_array[] = [
                            'nama' => $petugas_data[0],
                            'nohp' => $petugas_data[1]
                        ];
                    }
                }
            }
            $result['petugas'] = $petugas_array;
        }

        return $result;
    } catch (Exception $e) {
        return false;
    }
}

// Get kegiatan detail
$kegiatan = null;
if ($kegiatan_id > 0) {
    $kegiatan = getKegiatanDetail($pdo, $kegiatan_id);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $kegiatan ? htmlspecialchars($kegiatan['judul_kegiatan']) : 'Detail Kegiatan'; ?> | <?php echo htmlspecialchars($appSetting['name']); ?></title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" rel="icon">
    <link href="assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        .portfolio-info ul li {
            margin-bottom: 10px;
        }

        .contact-person-detail {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
        }

        .contact-person-detail .name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .contact-person-detail .phone {
            color: #666;
            margin-bottom: 10px;
        }

        .wa-btn {
            background: #25d366;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .wa-btn:hover {
            background: #1da851;
            color: white;
        }

        .no-data-container {
            text-align: center;
            padding: 100px 20px;
            color: #666;
        }

        .no-data-container i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 30px;
        }

        .no-data-container h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .btn-back {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
            color: white;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 400px;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-selesai {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* Custom Accordion Styles */
        .custom-accordion {
            margin-top: 30px;
        }

        .custom-accordion .accordion-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .custom-accordion .accordion-header {
            border-radius: 10px;
        }

        .custom-accordion .accordion-button {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 15px 20px;
            box-shadow: none;
        }

        .custom-accordion .accordion-button:not(.collapsed) {
            background: linear-gradient(45deg, #0056b3, #004085);
            color: white;
            box-shadow: none;
        }

        .custom-accordion .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }

        .custom-accordion .accordion-button::after {
            filter: brightness(0) invert(1);
        }

        .custom-accordion .accordion-body {
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
            padding: 20px;
        }

        .petugas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .petugas-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="portfolio-details-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="index.php" class="logo d-flex align-items-center me-auto">
                <?php if (!empty($appSetting['logo']) && $appSetting['logo'] !== 'favicon.ico'): ?>
                    <img src="assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" alt="Logo">
                <?php else: ?>
                    <img src="assets/img/logo.png" alt="">
                <?php endif; ?>
                <h1 class="sitename"><?php echo htmlspecialchars($appSetting['name']); ?></h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php#hero">Home</a></li>
                    <li><a href="index.php#profile">Profile</a></li>
                    <li><a href="index.php#agenda_kegiatan">Agenda Kegiatan</a></li>
                    <li><a href="index.php#portfolio">Portfolio</a></li>
                    <li><a href="index.php#team">Team</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="cta-btn" href="index.php#profile">Get Started</a>

        </div>
    </header>

    <main class="main">

        <!-- Page Title -->
        <div class="page-title dark-background" data-aos="fade" style="background-image: url(assets/img/page-title-bg.webp);">
            <div class="container position-relative">
                <h1><?php echo $kegiatan ? htmlspecialchars($kegiatan['judul_kegiatan']) : 'Detail Kegiatan'; ?></h1>
                <p><?php echo $kegiatan ? 'Detail informasi kegiatan dan dokumentasi' : 'Data kegiatan tidak ditemukan'; ?></p>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#portfolio">Portfolio</a></li>
                        <li class="current">Detail Kegiatan</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <?php if ($kegiatan): ?>
            <!-- Portfolio Details Section -->
            <section id="portfolio-details" class="portfolio-details section">
                <div class="container" data-aos="fade-up" data-aos-delay="100">
                    <div class="row gy-4">

                        <div class="col-lg-8">
                            <?php
                            $thumbnailPath = "assets/img/thumb/" . $kegiatan['thumbnails_kegiatan'];
                            $isVideo = in_array(strtolower(pathinfo($kegiatan['thumbnails_kegiatan'], PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg']);
                            ?>

                            <?php if ($isVideo): ?>
                                <div class="video-container">
                                    <video controls>
                                        <source src="<?php echo $thumbnailPath; ?>" type="video/mp4">
                                        Browser Anda tidak mendukung video.
                                    </video>
                                </div>
                            <?php else: ?>
                                <div class="portfolio-details-slider swiper init-swiper">
                                    <script type="application/json" class="swiper-config">
                                        {
                                            "loop": true,
                                            "speed": 600,
                                            "autoplay": {
                                                "delay": 5000
                                            },
                                            "slidesPerView": "auto",
                                            "pagination": {
                                                "el": ".swiper-pagination",
                                                "type": "bullets",
                                                "clickable": true
                                            }
                                        }
                                    </script>

                                    <div class="swiper-wrapper align-items-center">
                                        <div class="swiper-slide">
                                            <img src="<?php echo $thumbnailPath; ?>" alt="<?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>" onerror="this.src='assets/img/placeholder.jpg'">
                                        </div>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            <?php endif; ?>

                            <!-- Petugas Accordion Section - Moved below thumbnail -->
                            <?php if (!empty($kegiatan['petugas'])): ?>
                                <div class="custom-accordion" data-aos="fade-up" data-aos-delay="400">
                                    <div class="accordion" id="petugasAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingPetugas">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePetugas" aria-expanded="false" aria-controls="collapsePetugas">
                                                    <i class="bi bi-people-fill me-2"></i>
                                                    Petugas Yang Bertugas (<?php echo count($kegiatan['petugas']); ?> Orang)
                                                </button>
                                            </h2>
                                            <div id="collapsePetugas" class="accordion-collapse collapse" aria-labelledby="headingPetugas" data-bs-parent="#petugasAccordion">
                                                <div class="accordion-body">
                                                    <div class="petugas-grid">
                                                        <?php foreach ($kegiatan['petugas'] as $index => $petugas): ?>
                                                            <div class="contact-person-detail" data-aos="fade-up" data-aos-delay="<?php echo 500 + ($index * 100); ?>">
                                                                <div class="name">
                                                                    <i class="bi bi-person-check me-2"></i>
                                                                    <?php echo htmlspecialchars($petugas['nama']); ?>
                                                                </div>
                                                                <div class="phone">
                                                                    <i class="bi bi-telephone me-2"></i>
                                                                    <?php echo htmlspecialchars($petugas['nohp']); ?>
                                                                </div>
                                                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $petugas['nohp']); ?>"
                                                                    target="_blank" class="wa-btn">
                                                                    <i class="bi bi-whatsapp me-1"></i> Hubungi via WhatsApp
                                                                </a>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <div class="portfolio-info" data-aos="fade-up" data-aos-delay="200">
                                <h3>Informasi Kegiatan</h3>
                                <ul>
                                    <li><strong>Status</strong>:
                                        <span class="status-badge status-<?php echo $kegiatan['status_kegiatan']; ?>">
                                            <?php echo ucfirst($kegiatan['status_kegiatan']); ?>
                                        </span>
                                    </li>
                                    <li><strong>Tanggal Kegiatan</strong>:
                                        <?php
                                        $jadwal = new DateTime($kegiatan['jadwal_kegiatan']);
                                        echo $jadwal->format('d F Y, H:i') . ' WIB';
                                        ?>
                                    </li>
                                    <li><strong>Tanggal Dibuat</strong>:
                                        <?php
                                        $created = new DateTime($kegiatan['created_at']);
                                        echo $created->format('d F Y');
                                        ?>
                                    </li>
                                    <?php if (!empty($kegiatan['kehadiran_kegiatan'])): ?>
                                        <li><strong>Daftar Kehadiran</strong>:
                                            <a href="<?php echo htmlspecialchars($kegiatan['kehadiran_kegiatan']); ?>" target="_blank" class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-spreadsheet"></i> Lihat Kehadiran
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="portfolio-description" data-aos="fade-up" data-aos-delay="300">
                                <h2><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></h2>
                                <p><?php echo nl2br(htmlspecialchars($kegiatan['deksripsi_kegiatan'])); ?></p>

                                <div class="mt-4">
                                    <a href="index.php#portfolio" class="btn-back">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Portfolio
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section><!-- /Portfolio Details Section -->

        <?php else: ?>
            <!-- No Data Section -->
            <section class="section">
                <div class="container">
                    <div class="no-data-container" data-aos="fade-up">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h2>Data Kegiatan Tidak Ditemukan</h2>
                        <p>Maaf, kegiatan yang Anda cari tidak ditemukan atau mungkin telah dihapus.</p>
                        <p>Silakan kembali ke halaman utama untuk melihat kegiatan lainnya.</p>
                        <a href="index.php#portfolio" class="btn-back">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Portfolio
                        </a>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <footer id="footer" class="footer dark-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.php" class="logo d-flex align-items-center">
                        <span class="sitename"><?php echo htmlspecialchars($appSetting['name']); ?></span>
                    </a>
                    <div class="footer-contact pt-3">
                        <p>A108 Adam Street</p>
                        <p>New York, NY 535022</p>
                        <p class="mt-3"><strong>Phone:</strong> <span>+1 5589 55488 55</span></p>
                        <p><strong>Email:</strong> <span>info@example.com</span></p>
                    </div>
                    <div class="social-links d-flex mt-4">
                        <a href=""><i class="bi bi-twitter-x"></i></a>
                        <a href=""><i class="bi bi-facebook"></i></a>
                        <a href=""><i class="bi bi-instagram"></i></a>
                        <a href=""><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Useful Links</h4>
                    <ul>
                        <li><i class="bi bi-chevron-right"></i> <a href="index.php">Home</a></li>
                        <li><i class="bi bi-chevron-right"></i> <a href="index.php#profile">About us</a></li>
                        <li><i class="bi bi-chevron-right"></i> <a href="index.php#agenda_kegiatan">Services</a></li>
                        <li><i class="bi bi-chevron-right"></i> <a href="index.php#contact">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename"><?php echo htmlspecialchars($appSetting['name']); ?></strong> <span>All Rights Reserved</span></p>
            <div class="credits">
                Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed by <a href="https://themewagon.com">ThemeWagon</a>
            </div>
        </div>
    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>