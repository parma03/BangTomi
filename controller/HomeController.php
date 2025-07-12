<?php
// controller/HomeController.php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
if (!isset($pdo)) {
    include '../db/koneksi.php';
}

// Check request
$request = $_POST['request'] ?? '';

// Function to check if user is logged in
function checkLoginStatus()
{
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        return true;
    }
    return false;
}

// Function to get user data
function getUserData($pdo, $user_id)
{
    try {
        $query = "SELECT id, nama, email, nohp, role, photo_profile FROM tb_user WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Set default photo if empty
            if (empty($user['photo_profile'])) {
                $user['photo_profile'] = 'assets/img/avatars/1.png';
            }
            return $user;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Function to login user
function loginUser($pdo, $email, $password, $rememberMe = false)
{
    try {
        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception('Email dan password harus diisi');
        }

        // Check user in database
        $query = "SELECT * FROM tb_user WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('Email tidak ditemukan');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Password salah');
        }

        // Check if user is active (if you have status field)
        // if ($user['status'] !== 'active') {
        //     throw new Exception('Akun Anda tidak aktif');
        // }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Set remember me cookie if requested
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days

            // Store token in database (you might want to create a remember_tokens table)
            // For now, we'll skip this part
        }

        // Update last login time
        $updateQuery = "UPDATE tb_user SET updated_at = NOW() WHERE id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$user['id']]);

        // Return user data
        $userData = getUserData($pdo, $user['id']);

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $userData
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Function to logout user
function logoutUser()
{
    // Clear session
    session_unset();
    session_destroy();

    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    return [
        'success' => true,
        'message' => 'Logout berhasil'
    ];
}

// Function to update user profile
function updateUserProfile($pdo, $user_id, $data, $file = null)
{
    try {
        // Validate input
        if (empty($data['nama']) || empty($data['email']) || empty($data['nohp'])) {
            throw new Exception('Semua field wajib diisi');
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }

        // Check if email is already used by another user
        $checkEmail = $pdo->prepare("SELECT id FROM tb_user WHERE email = ? AND id != ?");
        $checkEmail->execute([$data['email'], $user_id]);
        if ($checkEmail->rowCount() > 0) {
            throw new Exception('Email sudah digunakan oleh user lain');
        }

        // Get current user data
        $currentUser = $pdo->prepare("SELECT * FROM tb_user WHERE id = ?");
        $currentUser->execute([$user_id]);
        $userData = $currentUser->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            throw new Exception('User tidak ditemukan');
        }

        // Validate password if changing
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

        // Handle photo upload
        $photo_profile = $userData['photo_profile'];
        if ($file && isset($file['photo_profile']) && $file['photo_profile']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['photo_profile']['type'], $allowedTypes)) {
                throw new Exception('Hanya file JPG dan PNG yang diperbolehkan');
            }

            if ($file['photo_profile']['size'] > $maxSize) {
                throw new Exception('Ukuran file maksimal 2MB');
            }

            // Create unique filename
            $extension = pathinfo($file['photo_profile']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $uploadPath = '../uploads/profiles/';

            // Create folder if not exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fullPath = $uploadPath . $filename;

            if (move_uploaded_file($file['photo_profile']['tmp_name'], $fullPath)) {
                // Delete old photo if exists and not default
                if (
                    !empty($userData['photo_profile']) &&
                    $userData['photo_profile'] !== 'assets/img/avatars/1.png' &&
                    file_exists('../' . $userData['photo_profile'])
                ) {
                    unlink('../' . $userData['photo_profile']);
                }

                $photo_profile = 'uploads/profiles/' . $filename;
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
            // Update session data
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['email'] = $data['email'];

            // Get updated user data
            $updatedUser = getUserData($pdo, $user_id);

            return [
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'user' => $updatedUser
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

// Fungsi untuk mengambil data app setting
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

// Fungsi untuk mengambil data kegiatan pending dengan petugas yang ditugaskan
function getPendingKegiatan($pdo)
{
    try {
        $sql = "SELECT 
                    k.id_kegiatan,
                    k.judul_kegiatan,
                    k.deksripsi_kegiatan,
                    k.jadwal_kegiatan,
                    k.thumbnails_kegiatan,
                    GROUP_CONCAT(CONCAT(u.nama, '|', u.nohp) SEPARATOR ';') as petugas_info
                FROM tb_kegiatan k
                LEFT JOIN tb_penugasan p ON k.id_kegiatan = p.id_kegiatan
                LEFT JOIN tb_user u ON p.id_pegawai = u.id
                WHERE k.status_kegiatan = 'pending'
                GROUP BY k.id_kegiatan, k.judul_kegiatan, k.deksripsi_kegiatan, k.jadwal_kegiatan, k.thumbnails_kegiatan
                ORDER BY k.jadwal_kegiatan ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process petugas info
        foreach ($result as &$kegiatan) {
            $petugas_array = [];
            if (!empty($kegiatan['petugas_info'])) {
                $petugas_list = explode(';', $kegiatan['petugas_info']);
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
            $kegiatan['petugas'] = $petugas_array;
        }

        return $result;
    } catch (Exception $e) {
        return [];
    }
}

// Handle AJAX requests only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($request)) {
    header('Content-Type: application/json');

    switch ($request) {
        case 'get_kegiatan':
            $kegiatan = getPendingKegiatan($pdo);
            echo json_encode([
                'success' => true,
                'data' => $kegiatan
            ]);
            break;

        case 'check_login':
            if (checkLoginStatus()) {
                $userData = getUserData($pdo, $_SESSION['user_id']);
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user' => $userData
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'logged_in' => false
                ]);
            }
            break;

        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['rememberMe']) ? true : false;

            $result = loginUser($pdo, $email, $password, $rememberMe);
            echo json_encode($result);
            break;

        case 'logout':
            $result = logoutUser();
            echo json_encode($result);
            break;

        case 'update_profile':
            if (!checkLoginStatus()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ]);
                break;
            }

            $result = updateUserProfile($pdo, $_SESSION['user_id'], $_POST, $_FILES);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Request tidak valid'
            ]);
            break;
    }
    exit();
}

?>