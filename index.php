<?php
//index.php
// Memulai session dan koneksi database
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Include database connection
include 'db/koneksi.php';

// Include HomeController untuk menggunakan fungsi getAppSetting
include 'controller/HomeController.php';
include 'controller/KomentarController.php';

// Ambil data app setting
$appSetting = getAppSetting($pdo);

// Ambil komentar yang isShow = 'true'
$testimonials = getTestimonials($pdo);

// Fungsi untuk memeriksa apakah file video exists
function isValidVideoFile($filename)
{
  if (empty($filename) || $filename === '-' || $filename === 'favicon.ico') {
    return false;
  }

  $videoPath = 'assets/img/appsetting/' . $filename;

  // Cek apakah file exists
  if (!file_exists($videoPath)) {
    return false;
  }

  // Cek ekstensi file
  $allowedExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov'];
  $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

  return in_array($fileExtension, $allowedExtensions);
}

// Gunakan fungsi ini di hero section
$hasValidVideo = isValidVideoFile($appSetting['video_header']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Index | <?php echo htmlspecialchars($appSetting['name']); ?></title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" rel="icon">
  <link href="aassets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- Custom CSS for Login -->
  <style>
    .user-dropdown .dropdown-menu {
      min-width: 200px;
    }

    .avatar-sm {
      width: 32px;
      height: 32px;
    }

    .login-btn {
      background: linear-gradient(45deg, #007bff, #0056b3);
      border: none;
      border-radius: 25px;
      padding: 8px 20px;
      color: white;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
      color: white;
    }

    /* Tambahkan CSS ini di bagian style dalam <head> */
    /* Tab Navigation Styling */
    .nav-tabs {
      border-bottom: 2px solid #e9ecef;
      margin-bottom: 30px;
    }

    .nav-tabs .nav-item {
      margin-bottom: -2px;
    }

    .nav-tabs .nav-link {
      background: transparent;
      border: 2px solid transparent;
      border-radius: 10px 10px 0 0;
      color: #6c757d;
      font-weight: 500;
      padding: 20px 15px;
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .nav-tabs .nav-link:hover {
      border-color: #e9ecef;
      color: #007bff;
      background: rgba(0, 123, 255, 0.05);
    }

    .nav-tabs .nav-link.active {
      background: linear-gradient(45deg, #007bff, #0056b3);
      border-color: #007bff;
      color: white;
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }

    .nav-tabs .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(45deg, #007bff, #0056b3);
    }

    .nav-tabs .nav-link i {
      font-size: 1.5rem;
      margin-bottom: 8px;
      display: block;
    }

    .nav-tabs .nav-link h4 {
      font-size: 0.9rem;
      margin: 0;
      font-weight: 600;
    }

    /* Tab Content Styling */
    .tab-content {
      min-height: 400px;
    }

    .tab-pane {
      padding: 20px 0;
    }

    /* Responsive adjustments for tabs */
    @media (max-width: 992px) {
      .nav-tabs .nav-link {
        padding: 15px 10px;
      }

      .nav-tabs .nav-link h4 {
        font-size: 0.8rem;
      }

      .nav-tabs .nav-link i {
        font-size: 1.2rem;
        margin-bottom: 5px;
      }
    }

    @media (max-width: 768px) {
      .nav-tabs .nav-link {
        padding: 10px 8px;
      }

      .nav-tabs .nav-link i {
        font-size: 1rem;
        margin-bottom: 3px;
      }

      .nav-tabs .nav-link span {
        font-size: 0.7rem;
        font-weight: 600;
      }
    }

    /* Animation for tab switching */
    .tab-pane.fade {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .tab-pane.fade.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* Loading state for tabs */
    .tab-loading {
      text-align: center;
      padding: 50px 0;
      color: #6c757d;
    }

    .tab-loading .spinner-border {
      width: 3rem;
      height: 3rem;
      margin-bottom: 20px;
    }

    /* Empty state styling */
    .tab-empty {
      text-align: center;
      padding: 50px 20px;
      color: #6c757d;
    }

    .tab-empty i {
      font-size: 3rem;
      color: #dee2e6;
      margin-bottom: 20px;
    }

    .tab-empty h4 {
      color: #495057;
      margin-bottom: 10px;
    }

    /* Kegiatan card adjustments for tab layout */
    .tab-pane .kegiatan-card {
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .tab-pane .kegiatan-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .tab-pane .kegiatan-description {
      flex: 1;
    }

    /* Badge untuk jumlah kegiatan di tab */
    .nav-tabs .nav-link .badge {
      position: absolute;
      top: 5px;
      right: 5px;
      background: #dc3545;
      color: white;
      font-size: 0.7rem;
      padding: 2px 6px;
      border-radius: 10px;
    }

    .nav-tabs .nav-link.active .badge {
      background: rgba(255, 255, 255, 0.3);
    }

    .kegiatan-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 30px;
    }

    .kegiatan-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .kegiatan-thumbnail {
      position: relative;
      height: 200px;
      overflow: hidden;
    }

    .kegiatan-thumbnail img,
    .kegiatan-thumbnail video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .kegiatan-date-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: white;
      padding: 8px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .kegiatan-content {
      padding: 20px;
    }

    .kegiatan-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 10px;
      line-height: 1.3;
    }

    .kegiatan-description {
      color: #666;
      font-size: 0.9rem;
      line-height: 1.5;
      margin-bottom: 15px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .kegiatan-schedule {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .kegiatan-schedule i {
      color: #007bff;
      margin-right: 8px;
    }

    .kegiatan-contacts {
      border-top: 1px solid #eee;
      padding-top: 15px;
    }

    .kegiatan-contacts h6 {
      color: #333;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .contact-person {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
      padding: 8px;
      background: #f8f9fa;
      border-radius: 6px;
    }

    .contact-person i {
      color: #28a745;
      margin-right: 8px;
    }

    .contact-person .contact-info {
      flex: 1;
    }

    .contact-person .contact-name {
      font-weight: 500;
      color: #333;
      font-size: 0.85rem;
    }

    .contact-person .contact-phone {
      color: #666;
      font-size: 0.8rem;
    }

    .contact-person .wa-button {
      background: #25d366;
      color: white;
      border: none;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.7rem;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    .contact-person .wa-button:hover {
      background: #1da851;
      color: white;
    }

    .no-kegiatan {
      text-align: center;
      color: #666;
      padding: 40px 20px;
    }

    .no-kegiatan i {
      font-size: 3rem;
      color: #ddd;
      margin-bottom: 15px;
    }

    @media (max-width: 768px) {
      .kegiatan-card {
        margin-bottom: 20px;
      }

      .kegiatan-thumbnail {
        height: 150px;
      }

      .kegiatan-content {
        padding: 15px;
      }
    }

    /* Star Rating Styles */
    .star-rating {
      direction: rtl;
      font-size: 2rem;
      unicode-bidi: bidi-override;
      display: inline-flex;
      flex-direction: row-reverse;
      gap: 5px;
    }

    .star-rating input {
      display: none;
    }

    .star-rating label {
      color: #ddd;
      cursor: pointer;
      transition: color 0.2s ease-in-out;
      font-size: 2rem;
      line-height: 1;
    }

    .star-rating label:hover,
    .star-rating label:hover~label {
      color: #ffc107;
    }

    .star-rating input:checked~label {
      color: #ffc107;
    }

    .star-rating input:checked~label:hover,
    .star-rating input:checked~label:hover~label {
      color: #ff9800;
    }

    .rating-container {
      text-align: left;
    }

    /* Profile Avatar Styles */
    .profile-avatar {
      margin-bottom: 20px;
    }

    .avatar-placeholder {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: bold;
      margin: 0 auto 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Testimonial Item Modifications */
    .testimonial-item {
      text-align: center;
      padding: 30px;
    }

    .testimonial-item .stars {
      margin-bottom: 20px;
    }

    .testimonial-item .stars i {
      color: #ffc107;
      font-size: 1.1rem;
      margin: 0 2px;
    }

    /* Form Styles */
    .php-email-form .loading {
      display: none;
      background: #fff;
      text-align: center;
      padding: 15px;
      color: var(--accent-color);
    }

    .php-email-form .loading:before {
      content: "";
      display: inline-block;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      margin: 0 10px -6px 0;
      border: 3px solid #18d26e;
      border-top-color: #eee;
      animation: animate-loading 1s linear infinite;
    }

    .php-email-form .error-message {
      display: none;
      background: #df1529;
      color: #ffffff;
      text-align: left;
      padding: 15px;
      font-weight: 600;
    }

    .php-email-form .sent-message {
      display: none;
      background: #18d26e;
      color: #ffffff;
      text-align: center;
      padding: 15px;
      font-weight: 600;
    }

    @keyframes animate-loading {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <?php if (!empty($appSetting['logo']) && $appSetting['logo'] !== 'favicon.ico'): ?>
          <img src="assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" alt="Logo">
        <?php else: ?>
          <!-- Default SVG Logo jika tidak ada logo custom -->
          <img src="assets/img/logo.png" alt="">
        <?php endif; ?>
        <h1 class="sitename"><?php echo htmlspecialchars($appSetting['name']); ?></h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#profile">Profile</a></li>
          <li><a href="#agenda_kegiatan">Agenda Kegiatan</a></li>
          <li><a href="#portfolio">Histori Kegiatan</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      &nbsp;
      &nbsp;
      &nbsp;

      <!-- Login/User Section -->
      <!-- Login Button (shown when not logged in) -->
      <div id="loginSection">
        <a class="cta-btn login-btn" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
      </div>

      <!-- User Dropdown (shown when logged in) -->
      <div id="userSection" class="user-dropdown dropdown" style="display: none;">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown"
          aria-expanded="false">
          <div class="avatar avatar-sm me-2">
            <img src="assets/img/avatars/1.png" alt="Profile" class="rounded-circle avatar-sm" id="userAvatar" />
          </div>
          <span class="d-none d-md-block" id="userName">User</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-sm">
                    <img src="assets/img/avatars/1.png" alt="Profile" class="rounded-circle avatar-sm"
                      id="userAvatarDropdown" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <span class="fw-semibold d-block" id="userNameDropdown">User</span>
                  <small class="text-muted" id="userRole">User</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item" href="#" id="dashboardLink">
              <i class="bi bi-speedometer2 me-2"></i>
              <span class="align-middle">Dashboard</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
              <i class="bi bi-person me-2"></i>
              <span class="align-middle">My Profile</span>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item" href="#" onclick="logout()">
              <i class="bi bi-power-off me-2"></i>
              <span class="align-middle">Logout</span>
            </a>
          </li>
        </ul>
      </div>

    </div>
  </header>

  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="loginModalLabel">Login</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="loginForm">
          <div class="modal-body">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
              <label class="form-check-label" for="rememberMe">
                Remember me
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Profile Modal -->
  <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="profileModalLabel">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="profileForm" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 text-center mb-3">
                <div class="avatar mx-auto mb-3">
                  <img src="assets/img/avatars/1.png" alt="Profile" class="rounded-circle" id="profilePreview"
                    style="width: 120px; height: 120px; object-fit: cover;" />
                </div>
                <input type="file" id="photo_profile" name="photo_profile" class="form-control" accept="image/*">
                <small class="text-muted">Upload foto profile (JPG, PNG, maksimal 2MB)</small>
              </div>
              <div class="col-md-8">
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama Lengkap</label>
                  <input type="text" class="form-control" id="nama" name="nama" required>
                </div>
                <div class="mb-3">
                  <label for="profile_email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="profile_email" name="email" required>
                </div>
                <div class="mb-3">
                  <label for="nohp" class="form-label">No. HP</label>
                  <input type="text" class="form-control" id="nohp" name="nohp" required>
                </div>
                <div class="mb-3">
                  <label for="current_password" class="form-label">Password Saat Ini</label>
                  <input type="password" class="form-control" id="current_password" name="current_password">
                  <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                </div>
                <div class="mb-3">
                  <label for="new_password" class="form-label">Password Baru</label>
                  <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                <div class="mb-3">
                  <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      <img src="assets/img/appsetting/<?php echo $appSetting['background_header'] ? htmlspecialchars($appSetting['background_header']) : "-"; ?>" alt="" data-aos="fade-in">

      <div class="container d-flex flex-column align-items-center">
        <h2 data-aos="fade-up" data-aos-delay="100">
          <?php echo $appSetting['name_header'] ? htmlspecialchars($appSetting['name_header']) : "-"; ?>
        </h2>
        <p data-aos="fade-up" data-aos-delay="200">
          <?php echo $appSetting['description_header'] ? htmlspecialchars($appSetting['description_header']) : "-"; ?>
        </p>
        <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
          <a href="#profile" class="btn-get-started">Get Started</a>
          <?php if (!empty($appSetting['video_header']) && $appSetting['video_header'] !== '-'): ?>
            <a href="assets/img/appsetting/<?php echo htmlspecialchars($appSetting['video_header']); ?>"
              class="glightbox btn-watch-video d-flex align-items-center"
              data-type="video">
              <i class="bi bi-play-circle"></i><span>Watch Video</span>
            </a>
          <?php else: ?>
            <a href="#profile" class="btn-watch-video d-flex align-items-center">
              <i class="bi bi-info-circle"></i><span>Learn More</span>
            </a>
          <?php endif; ?>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- Profile Section -->
    <section id="profile" class="about section">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Profile</h2>
        <p>Tentang Kami</p>
      </div><!-- End Section Title -->
      <div class="container">

        <div class="row gy-4">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <?php if (!empty($appSetting['judul_tentang_kami'])): ?>
              <h3><?php echo htmlspecialchars($appSetting['judul_tentang_kami']); ?></h3>
            <?php else: ?>
              <h3>Voluptatem dignissimos provident laboris nisi ut aliquip ex ea commodo</h3>
            <?php endif; ?>

            <?php if (!empty($appSetting['foto_tentang_kami'])): ?>
              <img src="assets/img/appsetting/<?php echo htmlspecialchars($appSetting['foto_tentang_kami']); ?>" class="img-fluid rounded-4 mb-4" alt="Tentang Kami">
            <?php else: ?>
              <img src="assets/img/about.jpg" class="img-fluid rounded-4 mb-4" alt="">
            <?php endif; ?>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="250">
            <div class="content ps-0 ps-lg-5">
              <?php if (!empty($appSetting['deskripsi_tentang_kami'])): ?>
                <p><?php echo nl2br(htmlspecialchars($appSetting['deskripsi_tentang_kami'])); ?></p>
              <?php else: ?>
                <p>Ut fugiat ut sunt quia veniam. Voluptate perferendis perspiciatis quod nisi et. Placeat debitis quia
                  recusandae odit et consequatur voluptatem. Dignissimos pariatur consectetur fugiat voluptas ea.</p>
                <p>Temporibus nihil enim deserunt sed ea. Provident sit expedita aut cupiditate nihil vitae quo officia vel.
                  Blanditiis eligendi possimus et in cum. Quidem eos ut sint rem veniam qui. Ut ut repellendus nobis tempore
                  doloribus debitis explicabo similique sit. Accusantium sed ut omnis beatae neque deleniti repellendus.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

    </section>
    <!-- /Profile Section -->

    <!-- Kegiatan Section -->
    <section id="agenda_kegiatan" class="features section">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Agenda Kegiatan Mendatang</h2>
        <p>Kegiatan yang akan diselenggarakan dalam waktu dekat.</p>
      </div><!-- End Section Title -->

      <div class="container">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs row d-flex" data-aos="fade-up" data-aos-delay="100" id="kegiatanTabs">
          <!-- Tab akan dibuat dinamis berdasarkan kategori kegiatan -->
          <li class="nav-item col-3">
            <a class="nav-link active show" data-bs-toggle="tab" data-bs-target="#kegiatan-tab-1">
              <i class="bi bi-calendar-event"></i>
              <h4 class="d-none d-lg-block">Semua Kegiatan</h4>
            </a>
          </li>
        </ul><!-- End Tab Nav -->

        <!-- Tab Content -->
        <div class="tab-content" data-aos="fade-up" data-aos-delay="200" id="kegiatanTabContent">
          <div class="tab-pane fade active show" id="kegiatan-tab-1">
            <div class="row" id="kegiatanContainer">
              <!-- Kegiatan akan dimuat secara dinamis -->
              <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat kegiatan...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /Kegiatan Section -->

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Histori Kegiatan</h2>
        <p>Dokumentasi kegiatan yang telah terlaksana</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">

          <ul class="portfolio-filters isotope-filters" data-aos="fade-up" data-aos-delay="100">
            <li data-filter="*" class="filter-active">Semua</li>
            <!-- Filter akan dibuat dinamis berdasarkan bulan/tahun kegiatan -->
          </ul><!-- End Portfolio Filters -->

          <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">
            <!-- Histori kegiatan akan dimuat secara dinamis -->
            <div class="col-12 text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Memuat histori kegiatan...</p>
            </div>
          </div><!-- End Portfolio Container -->

        </div>

      </div>

    </section><!-- /Portfolio Section -->

    <section id="testimonials" class="testimonials section dark-background">
      <img src="assets/img/testimonials-bg.jpg" class="testimonials-bg" alt="">

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="swiper init-swiper">
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
          <div class="swiper-wrapper">
            <?php if (!empty($testimonials)): ?>
              <?php foreach ($testimonials as $testimonial): ?>
                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="profile-avatar">
                      <?php
                      // Generate avatar dengan inisial nama
                      $initials = '';
                      $nameParts = explode(' ', $testimonial['nama']);
                      foreach ($nameParts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                      }
                      $initials = substr($initials, 0, 2);
                      ?>
                      <div class="avatar-placeholder">
                        <?php echo $initials; ?>
                      </div>
                    </div>
                    <h3><?php echo htmlspecialchars($testimonial['nama']); ?></h3>
                    <h4><?php echo htmlspecialchars($testimonial['instansi']); ?></h4>
                    <div class="stars">
                      <?php
                      $rating = (int)$testimonial['rating'];
                      for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating) {
                          echo '<i class="bi bi-star-fill"></i>';
                        } else {
                          echo '<i class="bi bi-star"></i>';
                        }
                      }
                      ?>
                    </div>
                    <p>
                      <i class="bi bi-quote quote-icon-left"></i>
                      <span><?php echo htmlspecialchars($testimonial['komentar']); ?></span>
                      <i class="bi bi-quote quote-icon-right"></i>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Default testimonials jika belum ada komentar -->
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <div class="profile-avatar">
                    <div class="avatar-placeholder">SG</div>
                  </div>
                  <h3>Saul Goodman</h3>
                  <h4>CEO &amp; Founder</h4>
                  <div class="stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                  </div>
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span>Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.</span>
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </section><!-- /Testimonials Section -->

    <!-- Komentar Section -->
    <section id="komentar" class="contact section">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Berikan Komentar</h2>
        <p>Bagikan pengalaman Anda dengan kami</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <form id="komentarForm" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">
                <div class="col-md-6">
                  <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                </div>

                <div class="col-md-6">
                  <input type="text" name="instansi" class="form-control" placeholder="Instansi/Perusahaan" required>
                </div>

                <div class="col-md-12">
                  <label class="form-label">Rating</label>
                  <div class="rating-container mb-3">
                    <div class="star-rating">
                      <input type="radio" id="star5" name="rating" value="5" required>
                      <label for="star5" title="5 bintang">★</label>
                      <input type="radio" id="star4" name="rating" value="4">
                      <label for="star4" title="4 bintang">★</label>
                      <input type="radio" id="star3" name="rating" value="3">
                      <label for="star3" title="3 bintang">★</label>
                      <input type="radio" id="star2" name="rating" value="2">
                      <label for="star2" title="2 bintang">★</label>
                      <input type="radio" id="star1" name="rating" value="1">
                      <label for="star1" title="1 bintang">★</label>
                    </div>
                  </div>
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="komentar" rows="6" placeholder="Tulis komentar Anda..." required></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Komentar Anda berhasil dikirim. Terima kasih!</div>

                  <button type="submit">Kirim Komentar</button>
                </div>
              </div>
            </form>
          </div><!-- End Contact Form -->
        </div>
      </div>
    </section><!-- /Komentar Section -->

  </main>

  <footer id="footer" class="footer dark-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">Dewi</span>
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
            <li><i class="bi bi-chevron-right"></i> <a href="#hero">Home</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#profile">Profile</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#agenda_kegiatan">Agenda Kegiatan</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#portfolio">Histori Kegiatan</a></li>
          </ul>
        </div>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

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

  <!-- Controllers -->
  <script src="controller/HomeController.js"></script>
  <script src="controller/KomentarController.js"></script>
  <script>
    // Fix untuk video GLightbox
    document.addEventListener('DOMContentLoaded', function() {
      // Inisialisasi GLightbox dengan konfigurasi yang lebih aman
      const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: false, // Matikan autoplay untuk menghindari error
        videosWidth: '90vw',
        videosHeight: '80vh',
        onOpen: function() {
          console.log('GLightbox opened');
        },
        onClose: function() {
          console.log('GLightbox closed');
        }
      });

      // Validasi video sebelum membuka lightbox
      document.querySelectorAll('.glightbox[data-type="video"]').forEach(function(element) {
        element.addEventListener('click', function(e) {
          const videoSrc = this.getAttribute('href');

          // Cek apakah file video ada
          fetch(videoSrc, {
              method: 'HEAD'
            })
            .then(response => {
              if (!response.ok) {
                e.preventDefault();
                alert('Video tidak dapat dimuat. File mungkin tidak tersedia.');
              }
            })
            .catch(error => {
              e.preventDefault();
              console.error('Error checking video:', error);
              alert('Video tidak dapat dimuat. Periksa koneksi internet Anda.');
            });
        });
      });
    });
  </script>
</body>

</html>