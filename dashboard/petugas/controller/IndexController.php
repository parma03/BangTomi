<?php
// kegiatan/controller/KegiatanController.php

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
    if ($_SESSION['role'] !== 'petugas') {
        // Jika bukan admin, redirect ke halaman unauthorized atau halaman utama
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

// Dashboard Index Controller
function getDashboardStats($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $stats = [];

        // Total Kegiatan
        $query = "SELECT COUNT(*) as total FROM tb_kegiatan";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $stats['total_kegiatan'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Kegiatan Pending
        $query = "SELECT COUNT(*) as total FROM tb_kegiatan WHERE status_kegiatan = 'pending'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $stats['kegiatan_pending'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Kegiatan Selesai
        $query = "SELECT COUNT(*) as total FROM tb_kegiatan WHERE status_kegiatan = 'selesai'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $stats['kegiatan_selesai'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Kegiatan Hari Ini
        $query = "SELECT COUNT(*) as total FROM tb_kegiatan WHERE DATE(jadwal_kegiatan) = CURDATE()";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $stats['kegiatan_hari_ini'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Kegiatan Minggu Ini
        $query = "SELECT COUNT(*) as total FROM tb_kegiatan WHERE WEEK(jadwal_kegiatan, 1) = WEEK(CURDATE(), 1) AND YEAR(jadwal_kegiatan) = YEAR(CURDATE())";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $stats['kegiatan_minggu_ini'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        echo json_encode(['status' => 'success', 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getCalendarData($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $filter = $_POST['filter'] ?? 'month';
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');

        $whereClause = '';

        switch ($filter) {
            case 'day':
                $whereClause = "WHERE DATE(k.jadwal_kegiatan) = '$startDate'";
                break;
            case 'week':
                $whereClause = "WHERE k.jadwal_kegiatan BETWEEN '$startDate' AND '$endDate'";
                break;
            case 'month':
                $whereClause = "WHERE k.jadwal_kegiatan BETWEEN '$startDate' AND '$endDate'";
                break;
            default:
                $whereClause = "WHERE k.jadwal_kegiatan BETWEEN '$startDate' AND '$endDate'";
        }

        $query = "SELECT 
                    k.id_kegiatan,
                    k.judul_kegiatan,
                    k.deksripsi_kegiatan,
                    k.jadwal_kegiatan,
                    k.status_kegiatan,
                    k.thumbnails_kegiatan,
                    COUNT(p.id_penugasan) as jumlah_petugas,
                    GROUP_CONCAT(
                        CONCAT(u.nama, '|', u.email, '|', COALESCE(u.photo_profile, ''), '|', u.nohp)
                        SEPARATOR ';;'
                    ) as petugas_data
                  FROM tb_kegiatan k
                  LEFT JOIN tb_penugasan p ON k.id_kegiatan = p.id_kegiatan
                  LEFT JOIN tb_user u ON p.id_pegawai = u.id
                  $whereClause
                  GROUP BY k.id_kegiatan
                  ORDER BY k.jadwal_kegiatan ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format data untuk kalender
        $calendarEvents = [];
        foreach ($events as $event) {
            // Format petugas data
            $petugasInfo = [];
            if (!empty($event['petugas_data'])) {
                $petugasArray = explode(';;', $event['petugas_data']);
                foreach ($petugasArray as $petugas) {
                    $petugasDetail = explode('|', $petugas);
                    if (count($petugasDetail) >= 4) {
                        $petugasInfo[] = [
                            'nama' => $petugasDetail[0],
                            'email' => $petugasDetail[1],
                            'photo_profile' => $petugasDetail[2],
                            'nohp' => $petugasDetail[3]
                        ];
                    }
                }
            }

            $calendarEvents[] = [
                'id' => $event['id_kegiatan'],
                'title' => $event['judul_kegiatan'],
                'start' => $event['jadwal_kegiatan'],
                'description' => $event['deksripsi_kegiatan'],
                'status' => $event['status_kegiatan'],
                'petugas_count' => $event['jumlah_petugas'],
                'petugas_info' => $petugasInfo,
                'backgroundColor' => $event['status_kegiatan'] === 'selesai' ? '#28a745' : '#ffc107',
                'borderColor' => $event['status_kegiatan'] === 'selesai' ? '#1e7e34' : '#e0a800',
                'textColor' => $event['status_kegiatan'] === 'selesai' ? '#ffffff' : '#212529'
            ];
        }

        echo json_encode(['status' => 'success', 'events' => $calendarEvents]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getEventDetails($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $eventId = $_POST['event_id'] ?? '';

        if (empty($eventId)) {
            throw new Exception('ID Event tidak valid');
        }

        $query = "SELECT 
                    k.id_kegiatan,
                    k.judul_kegiatan,
                    k.deksripsi_kegiatan,
                    k.jadwal_kegiatan,
                    k.status_kegiatan,
                    k.kehadiran_kegiatan,
                    k.thumbnails_kegiatan
                  FROM tb_kegiatan k
                  WHERE k.id_kegiatan = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            throw new Exception('Event tidak ditemukan');
        }

        // Get assigned petugas with detailed info
        $query = "SELECT 
                    u.id,
                    u.nama,
                    u.email,
                    u.nohp,
                    u.photo_profile,
                    u.role
                  FROM tb_penugasan p
                  JOIN tb_user u ON p.id_pegawai = u.id
                  WHERE p.id_kegiatan = ?
                  ORDER BY u.nama ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$eventId]);
        $petugas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $event['petugas'] = $petugas;

        echo json_encode(['status' => 'success', 'event' => $event]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getUpcomingEvents($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $query = "SELECT 
                    k.id_kegiatan,
                    k.judul_kegiatan,
                    k.jadwal_kegiatan,
                    k.status_kegiatan,
                    COUNT(p.id_penugasan) as jumlah_petugas
                  FROM tb_kegiatan k
                  LEFT JOIN tb_penugasan p ON k.id_kegiatan = p.id_kegiatan
                  WHERE k.jadwal_kegiatan >= NOW()
                  GROUP BY k.id_kegiatan
                  ORDER BY k.jadwal_kegiatan ASC
                  LIMIT 5";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'events' => $events]);
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
        case 'get_dashboard_stats':
            getDashboardStats($pdo);
            break;

        case 'get_calendar_data':
            getCalendarData($pdo);
            break;

        case 'get_event_details':
            getEventDetails($pdo);
            break;

        case 'get_upcoming_events':
            getUpcomingEvents($pdo);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Request tidak valid']);
            break;
    }
    exit();
}
