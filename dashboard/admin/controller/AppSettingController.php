<?php
// appsetting/controller/AppSettingController.php

// Pastikan koneksi database tersedia
if (!isset($pdo)) {
    require_once '../../../db/koneksi.php';
}

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
        header('Location: ../../kegiatan/index.php');
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
                'logo' => 'favicon.ico',
                'name_header' => '',
                'description_header' => '',
                'background_header' => '',
                'video_header' => ''
            ];
        }

        return $result;
    } catch (Exception $e) {
        // Jika ada error, return default values
        return [
            'id_appsetting' => 0,
            'name' => 'Sumbar Protokol INTEGRETED',
            'logo' => 'favicon.ico',
            'name_header' => '',
            'description_header' => '',
            'background_header' => '',
            'video_header' => ''
        ];
    }
}

function updateAppSetting($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $appSettingId = $_POST['id_appsetting'] ?? '';
        $appName = trim($_POST['app_name'] ?? '');
        $headerName = trim($_POST['header_name'] ?? '');
        $headerDescription = trim($_POST['header_description'] ?? '');

        // Validasi input wajib
        if (empty($appName)) {
            throw new Exception('Nama aplikasi harus diisi');
        }

        // Ambil data app setting saat ini
        $currentData = getAppSetting($pdo);

        // Handle upload logo
        $logoFilename = $currentData['logo'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoFilename = handleFileUpload($_FILES['logo'], 'logo', '../../../assets/img/favicon/', [
                'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
                'max_size' => 2 * 1024 * 1024, // 2MB
                'prefix' => 'logo_'
            ]);

            // Hapus logo lama jika ada dan bukan default
            if (
                !empty($currentData['logo']) &&
                $currentData['logo'] !== 'favicon.ico' &&
                file_exists('../../../assets/img/favicon/' . $currentData['logo'])
            ) {
                unlink('../../../assets/img/favicon/' . $currentData['logo']);
            }
        }

        // Handle upload background header
        $backgroundFilename = $currentData['background_header'] ?? '';
        if (isset($_FILES['background_header']) && $_FILES['background_header']['error'] === UPLOAD_ERR_OK) {
            $backgroundFilename = handleFileUpload($_FILES['background_header'], 'background', '../../../assets/img/appsetting/', [
                'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'prefix' => 'bg_'
            ]);

            // Hapus background lama jika ada
            if (
                !empty($currentData['background_header']) &&
                file_exists('../../../assets/img/appsetting/' . $currentData['background_header'])
            ) {
                unlink('../../../assets/img/appsetting/' . $currentData['background_header']);
            }
        }

        // Handle upload video header
        $videoFilename = $currentData['video_header'] ?? '';
        if (isset($_FILES['video_header']) && $_FILES['video_header']['error'] === UPLOAD_ERR_OK) {
            $videoFilename = handleFileUpload($_FILES['video_header'], 'video', '../../../assets/img/appsetting/', [
                'allowed_types' => ['video/mp4', 'video/avi', 'video/mov', 'video/quicktime'],
                'max_size' => 100 * 1024 * 1024, // 100MB
                'prefix' => 'video_'
            ]);

            // Hapus video lama jika ada
            if (
                !empty($currentData['video_header']) &&
                file_exists('../../../assets/img/appsetting/' . $currentData['video_header'])
            ) {
                unlink('../../../assets/img/appsetting/' . $currentData['video_header']);
            }
        }

        // Update atau insert data
        if (!empty($appSettingId) && $appSettingId > 0) {
            // Update existing record
            $sql = "UPDATE tb_appsetting SET 
                        name = ?, 
                        logo = ?, 
                        name_header = ?, 
                        description_header = ?, 
                        background_header = ?, 
                        video_header = ?
                    WHERE id_appsetting = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $appName,
                $logoFilename,
                $headerName,
                $headerDescription,
                $backgroundFilename,
                $videoFilename,
                $appSettingId
            ]);
        } else {
            // Insert new record
            $sql = "INSERT INTO tb_appsetting 
                        (name, logo, name_header, description_header, background_header, video_header) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $appName,
                $logoFilename,
                $headerName,
                $headerDescription,
                $backgroundFilename,
                $videoFilename
            ]);
        }

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Pengaturan aplikasi berhasil diupdate'
            ]);
        } else {
            throw new Exception('Gagal menyimpan pengaturan aplikasi');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function handleFileUpload($file, $type, $uploadPath, $options = [])
{
    $allowedTypes = $options['allowed_types'] ?? [];
    $maxSize = $options['max_size'] ?? 2 * 1024 * 1024;
    $prefix = $options['prefix'] ?? '';

    // Validasi tipe file
    if (!in_array($file['type'], $allowedTypes)) {
        $typeString = implode(', ', array_map(function ($type) {
            return strtoupper(str_replace(['image/', 'video/'], '', $type));
        }, $allowedTypes));
        throw new Exception("Hanya file {$typeString} yang diperbolehkan untuk {$type}");
    }

    // Validasi ukuran file
    if ($file['size'] > $maxSize) {
        $maxSizeMB = $maxSize / 1024 / 1024;
        throw new Exception("Ukuran file {$type} maksimal {$maxSizeMB}MB");
    }

    // Buat nama file unik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . time() . '_' . uniqid() . '.' . $extension;

    // Buat folder jika belum ada
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $fullPath = $uploadPath . $filename;

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return $filename;
    } else {
        throw new Exception("Gagal upload file {$type}");
    }
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

// Handle request
$request = $_POST['request'] ?? '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    switch ($request) {
        case 'logout':
            logout();
            break;

        case 'update_app_setting':
            checkAdminAccess();
            updateAppSetting($pdo);
            break;

        case 'update_profile':
            checkAdminAccess();
            $result = updateProfile($pdo, $_SESSION['user_id'], $_POST, $_FILES);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Request tidak valid']);
            break;
    }
    exit();
}
