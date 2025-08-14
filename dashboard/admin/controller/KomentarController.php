<?php
// kegiatan/controller/PenugasanController.php

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

    // Cek apakah role adalah kegiatan
    if ($_SESSION['role'] !== 'admin') {
        // Jika bukan kegiatan, redirect ke halaman unauthorized atau halaman utama
        header('Location: ../../../index.php');
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

// Komentar Section Functions
function getKomentarTersembunyi($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Get komentar yang belum ditampilkan (isShow = false)
        $query = "SELECT * FROM tb_komentar WHERE isShow = 'false' ORDER BY id_komentar DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $komentars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the output HTML
        ob_start();

        if (count($komentars) > 0) {
?>
            <div class="table-responsive">
                <table id="komentarTersembunyiTable" class="table table-hover table-striped align-middle">
                    <thead class="table-warning">
                        <tr>
                            <th scope="col" style="width: 5%;">#</th>
                            <th scope="col" style="width: 20%;">Nama</th>
                            <th scope="col" style="width: 20%;">Instansi</th>
                            <th scope="col" style="width: 10%;">Rating</th>
                            <th scope="col" style="width: 35%;">Komentar</th>
                            <th scope="col" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($komentars as $komentar) {
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-wrapper">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-warning">
                                                    <?php echo strtoupper(substr($komentar['nama'], 0, 1)); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($komentar['nama']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($komentar['instansi']); ?></td>
                                <td>
                                    <div class="stars">
                                        <?php
                                        $rating = (int)$komentar['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } else {
                                                echo '<i class="fas fa-star text-muted"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <small class="text-muted"><?php echo $rating; ?>/5</small>
                                </td>
                                <td>
                                    <div class="komentar-text" style="max-height: 80px; overflow-y: auto;">
                                        <?php echo htmlspecialchars($komentar['komentar']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Tombol Tampilkan -->
                                        <button type="button" class="btn btn-success btn-sm show-komentar-btn"
                                            data-id="<?php echo $komentar['id_komentar']; ?>"
                                            data-nama="<?php echo htmlspecialchars($komentar['nama']); ?>"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Tampilkan Komentar">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button type="button" class="btn btn-danger btn-sm delete-komentar-btn"
                                            data-id="<?php echo $komentar['id_komentar']; ?>"
                                            data-nama="<?php echo htmlspecialchars($komentar['nama']); ?>"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Komentar">
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
            </div>
        <?php
        } else {
        ?>
            <div class="alert alert-warning text-center border-0 rounded-3" role="alert">
                <i class="bx bx-info-circle me-2"></i>Tidak ada komentar yang disembunyikan.
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

function getKomentarDitampilkan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Get komentar yang ditampilkan (isShow = true)
        $query = "SELECT * FROM tb_komentar WHERE isShow = 'true' ORDER BY id_komentar DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $komentars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the output HTML
        ob_start();

        if (count($komentars) > 0) {
        ?>
            <div class="table-responsive">
                <table id="komentarDitampilkanTable" class="table table-hover table-striped align-middle">
                    <thead class="table-success">
                        <tr>
                            <th scope="col" style="width: 5%;">#</th>
                            <th scope="col" style="width: 20%;">Nama</th>
                            <th scope="col" style="width: 20%;">Instansi</th>
                            <th scope="col" style="width: 10%;">Rating</th>
                            <th scope="col" style="width: 35%;">Komentar</th>
                            <th scope="col" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($komentars as $komentar) {
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-wrapper">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-success">
                                                    <?php echo strtoupper(substr($komentar['nama'], 0, 1)); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($komentar['nama']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($komentar['instansi']); ?></td>
                                <td>
                                    <div class="stars">
                                        <?php
                                        $rating = (int)$komentar['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } else {
                                                echo '<i class="fas fa-star text-muted"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <small class="text-muted"><?php echo $rating; ?>/5</small>
                                </td>
                                <td>
                                    <div class="komentar-text" style="max-height: 80px; overflow-y: auto;">
                                        <?php echo htmlspecialchars($komentar['komentar']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Tombol Sembunyikan -->
                                        <button type="button" class="btn btn-warning btn-sm hide-komentar-btn"
                                            data-id="<?php echo $komentar['id_komentar']; ?>"
                                            data-nama="<?php echo htmlspecialchars($komentar['nama']); ?>"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Sembunyikan Komentar">
                                            <i class="bx bx-hide"></i>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button type="button" class="btn btn-danger btn-sm delete-komentar-btn"
                                            data-id="<?php echo $komentar['id_komentar']; ?>"
                                            data-nama="<?php echo htmlspecialchars($komentar['nama']); ?>"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Komentar">
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
            </div>
        <?php
        } else {
        ?>
            <div class="alert alert-success text-center border-0 rounded-3" role="alert">
                <i class="bx bx-info-circle me-2"></i>Tidak ada komentar yang ditampilkan.
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

function showKomentar($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $komentarId = $_POST['komentar_id'] ?? '';
        $nama = $_POST['nama'] ?? '';

        if (empty($komentarId) || !is_numeric($komentarId)) {
            throw new Exception('ID Komentar tidak valid');
        }

        // Cek apakah komentar dengan ID tersebut ada
        $checkQuery = "SELECT * FROM tb_komentar WHERE id_komentar = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$komentarId]);
        $komentar = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$komentar) {
            throw new Exception('Komentar tidak ditemukan');
        }

        // Update status isShow menjadi true
        $updateQuery = "UPDATE tb_komentar SET isShow = 'true' WHERE id_komentar = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $result = $updateStmt->execute([$komentarId]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Komentar dari '{$nama}' berhasil ditampilkan dan akan muncul di website"
            ]);
        } else {
            throw new Exception('Gagal mengupdate status komentar');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function hideKomentar($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $komentarId = $_POST['komentar_id'] ?? '';
        $nama = $_POST['nama'] ?? '';

        if (empty($komentarId) || !is_numeric($komentarId)) {
            throw new Exception('ID Komentar tidak valid');
        }

        // Cek apakah komentar dengan ID tersebut ada
        $checkQuery = "SELECT * FROM tb_komentar WHERE id_komentar = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$komentarId]);
        $komentar = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$komentar) {
            throw new Exception('Komentar tidak ditemukan');
        }

        // Update status isShow menjadi false
        $updateQuery = "UPDATE tb_komentar SET isShow = 'false' WHERE id_komentar = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $result = $updateStmt->execute([$komentarId]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Komentar dari '{$nama}' berhasil disembunyikan dan tidak akan muncul di website"
            ]);
        } else {
            throw new Exception('Gagal mengupdate status komentar');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function deleteKomentar($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $komentarId = $_POST['komentar_id'] ?? '';
        $nama = $_POST['nama'] ?? '';

        if (empty($komentarId) || !is_numeric($komentarId)) {
            throw new Exception('ID Komentar tidak valid');
        }

        // Cek apakah komentar dengan ID tersebut ada
        $checkQuery = "SELECT * FROM tb_komentar WHERE id_komentar = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$komentarId]);
        $komentar = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$komentar) {
            throw new Exception('Komentar tidak ditemukan');
        }

        // Hapus komentar dari database
        $deleteQuery = "DELETE FROM tb_komentar WHERE id_komentar = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $result = $deleteStmt->execute([$komentarId]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Komentar dari '{$nama}' berhasil dihapus"
            ]);
        } else {
            throw new Exception('Gagal menghapus komentar dari database');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Handle request
$request = $_POST['request'] ?? '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    switch ($request) {
        case 'logout':
            logout();
            break;

        case 'get_komentar_tersembunyi':
            checkAdminAccess();
            getKomentarTersembunyi($pdo);
            break;

        case 'get_komentar_ditampilkan':
            checkAdminAccess();
            getKomentarDitampilkan($pdo);
            break;

        case 'show_komentar':
            checkAdminAccess();
            showKomentar($pdo);
            break;

        case 'hide_komentar':
            checkAdminAccess();
            hideKomentar($pdo);
            break;

        case 'delete_komentar':
            checkAdminAccess();
            deleteKomentar($pdo);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Request tidak valid']);
            break;
    }
    exit();
}
?>