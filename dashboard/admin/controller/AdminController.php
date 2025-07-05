<?php
// admin/controller/AdminController.php

// Pastikan koneksi database tersedia
if (!isset($pdo)) {
    require_once '../../../db/koneksi.php';
}
$request = $_POST['request'] ?? '';

function checkAdminAccess()
{
    // Cek apakah session sudah dimulai
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Jika belum login, redirect ke halaman login
        if (!headers_sent()) {
            header('Location: ../../../index.php');
            exit();
        } else {
            // Jika headers sudah dikirim, gunakan JavaScript redirect
            echo '<script>window.location.href = "../../../index.php";</script>';
            exit();
        }
    }

    // Cek apakah role adalah admin
    if ($_SESSION['role'] !== 'admin') {
        // Jika bukan admin, redirect ke halaman unauthorized atau halaman utama
        header('Location: ../../petugas/index.php');
        exit();
    }

    return true;
}

function logout()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Hapus semua session
    session_unset();
    session_destroy();

    // Redirect ke halaman login
    header('Location: ../../../index.php');
    exit();
}

function updateProfile($pdo, $user_id, $data, $file = null)
{
    try {
        // Validasi input
        if (empty($data['nama']) || empty($data['email']) || empty($data['nohp'])) {
            throw new Exception('Semua field wajib diisi');
        }

        // Validasi email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }

        // Cek apakah email sudah digunakan user lain
        $checkEmail = $pdo->prepare("SELECT id FROM tb_user WHERE email = ? AND id != ?");
        $checkEmail->execute([$data['email'], $user_id]);
        if ($checkEmail->rowCount() > 0) {
            throw new Exception('Email sudah digunakan oleh user lain');
        }

        // Ambil data user saat ini
        $currentUser = $pdo->prepare("SELECT * FROM tb_user WHERE id = ?");
        $currentUser->execute([$user_id]);
        $userData = $currentUser->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            throw new Exception('User tidak ditemukan');
        }

        // Validasi password jika ingin mengubah
        $updatePassword = false;
        if (!empty($data['current_password']) && !empty($data['new_password'])) {
            if (!password_verify($data['current_password'], $userData['password'])) {
                throw new Exception('Password saat ini tidak sesuai');
            }

            if ($data['new_password'] !== $data['confirm_password']) {
                throw new Exception('Konfirmasi password tidak sama');
            }

            if (strlen($data['new_password']) < 6) {
                throw new Exception('Password baru minimal 6 karakter');
            }

            $updatePassword = true;
        }

        // Handle upload foto
        $photo_profile = $userData['photo_profile'] ?? '';
        if ($file && $file['photo_profile']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['photo_profile']['type'], $allowedTypes)) {
                throw new Exception('Hanya file JPG dan PNG yang diperbolehkan');
            }

            if ($file['photo_profile']['size'] > $maxSize) {
                throw new Exception('Ukuran file maksimal 2MB');
            }

            // Buat nama file unik
            $extension = pathinfo($file['photo_profile']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $uploadPath = '../../../assets/img/avatars/';

            // Buat folder jika belum ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fullPath = $uploadPath . $filename;

            if (move_uploaded_file($file['photo_profile']['tmp_name'], $fullPath)) {
                // Hapus foto lama jika ada dan bukan default
                if (
                    !empty($userData['photo_profile']) &&
                    $userData['photo_profile'] !== '../../../assets/img/avatars/1.png' &&
                    file_exists('../../../assets/img/avatars/' . $userData['photo_profile'])
                ) {
                    unlink('../../../assets/img/avatars/' . $userData['photo_profile']);
                }

                $photo_profile = $filename;
            } else {
                throw new Exception('Gagal upload foto profile');
            }
        }

        // Update database
        if ($updatePassword) {
            $sql = "UPDATE tb_user SET nama = ?, email = ?, nohp = ?, password = ?, photo_profile = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['nama'],
                $data['email'],
                $data['nohp'],
                password_hash($data['new_password'], PASSWORD_DEFAULT),
                $photo_profile,
                $user_id
            ]);
        } else {
            $sql = "UPDATE tb_user SET nama = ?, email = ?, nohp = ?, photo_profile = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['nama'],
                $data['email'],
                $data['nohp'],
                $photo_profile,
                $user_id
            ]);
        }

        if ($result) {
            return [
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'photo_profile' => $photo_profile
            ];
        } else {
            throw new Exception('Gagal update profile');
        }

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getAppSetting($pdo)
{
    try {
        $sql = "SELECT * FROM tb_appsetting ORDER BY id_appsetting DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jika tidak ada data, return default values
        if (!$result) {
            return [
                'id_appsetting' => 0,
                'name' => 'Sumbar Protokol INTEGRETED',
                'logo' => 'favicon.ico'
            ];
        }

        return $result;
    } catch (Exception $e) {
        // Jika ada error, return default values
        return [
            'id_appsetting' => 0,
            'name' => 'Sumbar Protokol INTEGRETED',
            'logo' => 'favicon.ico'
        ];
    }
}

function getProfileDisplay($nama, $profile)
{
    $profile_path = "../../../assets/img/avatars/";

    if (!empty($profile) && file_exists($profile_path . $profile)) {
        return '<img src="' . $profile_path . htmlspecialchars($profile) . '" 
                     alt="Profile" 
                     class="rounded-circle" 
                     style="width: 35px; height: 35px; object-fit: cover;">';
    } else {
        return '<div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 35px; height: 35px; font-size: 14px;">
                    ' . strtoupper(substr($nama, 0, 2)) . '
                </div>';
    }
}

function getLargeProfileDisplay($nama, $profile)
{
    $profile_path = "../../../assets/img/avatars/";

    if (!empty($profile) && file_exists($profile_path . $profile)) {
        return '<img src="' . $profile_path . htmlspecialchars($profile) . '" 
                     alt="Profile" 
                     class="rounded-circle mx-auto mb-3" 
                     style="width: 80px; height: 80px; object-fit: cover; display: block;">';
    } else {
        return '<div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px; font-size: 24px;">
                    ' . strtoupper(substr($nama, 0, 2)) . '
                </div>';
    }
}

// Admin Section
function getDataAdmin($pdo)
{
    try {
        // Pastikan $pdo tersedia
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Get all admin data with user information
        $query = "SELECT * FROM tb_user WHERE role = 'admin' ORDER BY nama ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the output HTML
        ob_start();

        if (count($admins) > 0) {
            ?>
            <table id="adminTable" class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" style="width: 5%;">#</th>
                        <th scope="col" style="width: 20%;">Nama</th>
                        <th scope="col" style="width: 20%;">Email</th>
                        <th scope="col" style="width: 20%;">Nohp</th>
                        <th scope="col" style="width: 10%;">Role</th>
                        <th scope="col" style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($admins as $admin) {
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php echo getProfileDisplay($admin['nama'], $admin['photo_profile'] ?? ''); ?>
                                    <div class="ms-2">
                                        <strong><?php echo htmlspecialchars($admin['nama']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($admin['email']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($admin['nohp']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($admin['role']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-info view-admin-btn" data-id="<?php echo $admin['id']; ?>"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning edit-admin-btn" data-id="<?php echo $admin['id']; ?>"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Admin">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger delete-admin-btn" data-id="<?php echo $admin['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($admin['nama']); ?>" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Hapus Admin">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        } else {
            ?>
            <div class="container-xxl flex-grow-1 container-p-y">
                <div class="alert alert-info text-center" role="alert">
                    <i class="bx bx-info-circle me-2"></i>Belum ada data Administrator.
                </div>
            </div>
            <?php
        }

        $html = ob_get_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}
function getDetailAdmin($pdo, $adminId)
{
    try {
        // Pastikan $pdo tersedia
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Validasi admin ID
        if (empty($adminId) || !is_numeric($adminId)) {
            throw new Exception('ID Admin tidak valid');
        }

        // Query menggunakan PDO (bukan mysqli)
        $query = "SELECT * FROM tb_user WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            ob_start();
            ?>
            <!-- Responsive Layout -->
            <div class="row g-3">
                <!-- Profile Section - Stack on mobile, side-by-side on larger screens -->
                <div class="col-12 col-lg-4">
                    <div class="text-center">
                        <?php echo getLargeProfileDisplay($admin['nama'], $admin['photo_profile'] ?? ''); ?>
                        <h5 class="mt-2 mb-2"><?php echo htmlspecialchars($admin['nama']); ?></h5>
                        <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($admin['role']); ?></span>
                    </div>
                </div>

                <!-- Details Section -->
                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bx bx-info-circle me-2"></i>Informasi Detail
                            </h6>

                            <!-- Responsive Table - Stack on mobile -->
                            <div class="table-responsive">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" style="width: 35%;">
                                                <i class="bx bx-hash me-1 text-muted"></i>ID:
                                            </td>
                                            <td><?php echo htmlspecialchars($admin['id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-envelope me-1 text-muted"></i>Email:
                                            </td>
                                            <td class="text-break">
                                                <small><?php echo htmlspecialchars($admin['email']); ?></small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-user me-1 text-muted"></i>Nama:
                                            </td>
                                            <td class="text-break">
                                                <?php echo htmlspecialchars($admin['nama']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-phone me-1 text-muted"></i>No. HP:
                                            </td>
                                            <td class="text-break">
                                                <?php echo htmlspecialchars($admin['nohp'] ?? 'Tidak ada'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-shield me-1 text-muted"></i>Role:
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($admin['role']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-calendar me-1 text-muted"></i>Dibuat:
                                            </td>
                                            <td>
                                                <small>
                                                    <?php
                                                    if (!empty($admin['created_at'])) {
                                                        echo date('d M Y H:i', strtotime($admin['created_at']));
                                                    } else {
                                                        echo 'Tidak diketahui';
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-calendar-edit me-1 text-muted"></i>Update:
                                            </td>
                                            <td>
                                                <small>
                                                    <?php
                                                    if (!empty($admin['updated_at'])) {
                                                        echo date('d M Y H:i', strtotime($admin['updated_at']));
                                                    } else {
                                                        echo 'Belum pernah';
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $html = ob_get_clean();
            echo json_encode(['status' => 'success', 'html' => $html]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data admin tidak ditemukan']);
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}
function getAdminById($pdo, $adminId)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        if (empty($adminId) || !is_numeric($adminId)) {
            throw new Exception('ID Admin tidak valid');
        }

        $query = "SELECT * FROM tb_user WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            echo json_encode([
                'status' => 'success',
                'data' => $admin
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Data admin tidak ditemukan'
            ]);
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}
function addAdmin($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Validasi input
        $email = trim($_POST['email'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $nohp = trim($_POST['nohp'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_passwords'] ?? '';

        if (empty($email) || empty($nohp) || empty($nama) || empty($password)) {
            throw new Exception('Semua field wajib diisi');
        }

        // Validasi email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }

        // Validasi password
        if ($password !== $confirm_password) {
            throw new Exception('Password dan konfirmasi password tidak cocok');
        }

        if (strlen($password) < 6) {
            throw new Exception('Password minimal 6 karakter');
        }

        // Validasi nomor HP jika diisi
        if (!empty($nohp)) {
            if (!preg_match('/^[0-9+\-\s()]+$/', $nohp)) {
                throw new Exception('Format nomor HP tidak valid');
            }
            if (strlen($nohp) < 10) {
                throw new Exception('Nomor HP minimal 10 digit');
            }
        }

        // Cek apakah email sudah ada
        $checkEmail = $pdo->prepare("SELECT id FROM tb_user WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->rowCount() > 0) {
            throw new Exception('Email sudah digunakan');
        }

        // Handle upload foto profile
        $photo_profile = null;
        if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['profile']['type'], $allowedTypes)) {
                throw new Exception('Format file tidak valid! Gunakan JPG, PNG, atau GIF');
            }

            if ($_FILES['profile']['size'] > $maxSize) {
                throw new Exception('Ukuran file terlalu besar! Maksimal 2MB');
            }

            // Buat nama file unik
            $extension = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = '../../../assets/img/avatars/';

            // Buat folder jika belum ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fullPath = $uploadPath . $filename;

            if (move_uploaded_file($_FILES['profile']['tmp_name'], $fullPath)) {
                $photo_profile = $filename;
            } else {
                throw new Exception('Gagal upload foto profile');
            }
        }

        // Insert data admin baru
        $sql = "INSERT INTO tb_user (nama, email, nohp, role, password, photo_profile, created_at) VALUES (?, ?, ?, 'admin', ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $nama,
            $email,
            $nohp,
            password_hash($password, PASSWORD_DEFAULT),
            $photo_profile
        ]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Admin berhasil ditambahkan'
            ]);
        } else {
            throw new Exception('Gagal menambahkan admin');
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
    exit;
}
function deleteAdmin($pdo, $adminId, $adminName)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        if (empty($adminId) || !is_numeric($adminId)) {
            throw new Exception('ID Admin tidak valid');
        }

        if (empty($adminName)) {
            throw new Exception('Nama Admin tidak valid');
        }

        // Cek apakah admin dengan ID tersebut ada
        $checkQuery = "SELECT * FROM tb_user WHERE id = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$adminId]);
        $admin = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            throw new Exception('Admin tidak ditemukan');
        }

        // Cek apakah ini admin terakhir (opsional - untuk mencegah hapus admin terakhir)
        $countQuery = "SELECT COUNT(*) as total FROM tb_user WHERE role = 'admin'";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute();
        $totalAdmin = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalAdmin <= 1) {
            throw new Exception('Tidak dapat menghapus admin terakhir! Sistem harus memiliki minimal 1 admin.');
        }

        // Hapus foto profile jika ada
        if (!empty($admin['photo_profile'])) {
            $photoPath = '../../../assets/img/avatars/' . $admin['photo_profile'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        // Hapus admin dari database
        $deleteQuery = "DELETE FROM tb_user WHERE id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $result = $deleteStmt->execute([$adminId]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Admin '{$adminName}' berhasil dihapus"
            ]);
        } else {
            throw new Exception('Gagal menghapus admin dari database');
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}
function updateAdmin($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Ambil data dari POST
        $adminId = $_POST['id'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $nohp = trim($_POST['nohp'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_passwords'] ?? '';
        $removeExisting = $_POST['removeExistingProfile'] ?? '0';

        // Validasi ID admin
        if (empty($adminId) || !is_numeric($adminId)) {
            throw new Exception('ID Admin tidak valid');
        }

        // Validasi input wajib
        if (empty($email) || empty($nama) || empty($nohp)) {
            throw new Exception('Semua field wajib diisi');
        }

        // Validasi email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }

        // Validasi password jika diisi
        $updatePassword = false;
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                throw new Exception('Password dan konfirmasi password tidak cocok');
            }

            if (strlen($password) < 6) {
                throw new Exception('Password minimal 6 karakter');
            }

            $updatePassword = true;
        }

        // Validasi nomor HP
        if (!preg_match('/^[0-9+\-\s()]+$/', $nohp)) {
            throw new Exception('Format nomor HP tidak valid');
        }

        if (strlen($nohp) < 10) {
            throw new Exception('Nomor HP minimal 10 digit');
        }

        // Cek apakah email sudah digunakan user lain
        $checkEmail = $pdo->prepare("SELECT id FROM tb_user WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $adminId]);
        if ($checkEmail->rowCount() > 0) {
            throw new Exception('Email sudah digunakan oleh user lain');
        }

        // Ambil data admin saat ini
        $currentQuery = "SELECT * FROM tb_user WHERE id = ?";
        $currentStmt = $pdo->prepare($currentQuery);
        $currentStmt->execute([$adminId]);
        $currentAdmin = $currentStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentAdmin) {
            throw new Exception('Data admin tidak ditemukan');
        }

        // Handle foto profile
        $photo_profile = $currentAdmin['photo_profile'];

        // Jika ada request untuk hapus foto existing
        if ($removeExisting === '1') {
            if (!empty($currentAdmin['photo_profile'])) {
                $oldPhotoPath = '../../../assets/img/avatars/' . $currentAdmin['photo_profile'];
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            $photo_profile = null;
        }

        // Handle upload foto baru
        if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['profile']['type'], $allowedTypes)) {
                throw new Exception('Format file tidak valid! Gunakan JPG, PNG, atau GIF');
            }

            if ($_FILES['profile']['size'] > $maxSize) {
                throw new Exception('Ukuran file terlalu besar! Maksimal 2MB');
            }

            // Buat nama file unik
            $extension = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . $adminId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = '../../../assets/img/avatars/';

            // Buat folder jika belum ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fullPath = $uploadPath . $filename;

            if (move_uploaded_file($_FILES['profile']['tmp_name'], $fullPath)) {
                // Hapus foto lama jika ada
                if (!empty($currentAdmin['photo_profile'])) {
                    $oldPhotoPath = '../../../assets/img/avatars/' . $currentAdmin['photo_profile'];
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
                $photo_profile = $filename;
            } else {
                throw new Exception('Gagal upload foto profile');
            }
        }

        // Update database
        if ($updatePassword) {
            $sql = "UPDATE tb_user SET nama = ?, email = ?, nohp = ?, password = ?, photo_profile = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $nama,
                $email,
                $nohp,
                password_hash($password, PASSWORD_DEFAULT),
                $photo_profile,
                $adminId
            ]);
        } else {
            $sql = "UPDATE tb_user SET nama = ?, email = ?, nohp = ?, photo_profile = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $nama,
                $email,
                $nohp,
                $photo_profile,
                $adminId
            ]);
        }

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Data admin berhasil diupdate'
            ]);
        } else {
            throw new Exception('Gagal mengupdate data admin');
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    switch ($request) {
        case 'logout':
            logout();
            break;

        case 'update_profile':
            checkAdminAccess();
            $result = updateProfile($pdo, $_SESSION['user_id'], $_POST, $_FILES);
            echo json_encode($result);
            break;

        case 'get_admin':
            checkAdminAccess();
            getDataAdmin($pdo);
            break;

        case 'get_admin_detail':
            checkAdminAccess();
            $adminId = $_POST['admin_id'] ?? null;
            if ($adminId) {
                getDetailAdmin($pdo, $adminId);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID Admin tidak ditemukan']);
            }
            break;

        case 'add_admin':
            checkAdminAccess();
            addAdmin($pdo);
            break;

        case 'get_admin_by_id':
            checkAdminAccess();
            $adminId = $_POST['admin_id'] ?? null;
            $adminName = $_POST['admin_id'] ?? null;
            if ($adminId) {
                getAdminById($pdo, $adminId);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID Admin tidak ditemukan']);
            }
            break;

        case 'delete_admin':
            checkAdminAccess();
            $adminId = $_POST['admin_id'] ?? null;
            $adminName = $_POST['admin_name'] ?? null;
            if ($adminId && $adminName) {
                deleteAdmin($pdo, $adminId, $adminName);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data admin tidak lengkap']);
            }
            break;

        case 'update_admin':
            checkAdminAccess();
            updateAdmin($pdo);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Request tidak valid']);
            break;
    }
    exit();
}
?>