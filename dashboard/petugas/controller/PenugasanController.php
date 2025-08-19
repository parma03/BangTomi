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
    if ($_SESSION['role'] !== 'petugas') {
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

// Penugasan Section
function getDataPenugasan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Get all penugasan data with kegiatan and petugas information
        $query = "SELECT 
                    p.id_penugasan,
                    p.id_kegiatan,
                    p.id_pegawai,
                    k.judul_kegiatan,
                    k.jadwal_kegiatan,
                    k.status_kegiatan,
                    u.nama as nama_petugas,
                    u.email as email_petugas,
                    u.nohp as nohp_petugas
                  FROM tb_penugasan p
                  LEFT JOIN tb_kegiatan k ON p.id_kegiatan = k.id_kegiatan
                  LEFT JOIN tb_user u ON p.id_pegawai = u.id
                  ORDER BY k.jadwal_kegiatan DESC, u.nama ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $penugasans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the output HTML
        ob_start();

        if (count($penugasans) > 0) {
?>
            <table id="penugasanTable" class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" style="width: 5%;">#</th>
                        <th scope="col" style="width: 25%;">Kegiatan</th>
                        <th scope="col" style="width: 20%;">Jadwal</th>
                        <th scope="col" style="width: 20%;">Petugas</th>
                        <th scope="col" style="width: 15%;">Kontak</th>
                        <th scope="col" style="width: 10%;">Status</th>
                        <!-- <th scope="col" style="width: 10%;">Aksi</th>  -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($penugasans as $penugasan) {
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <div class="ms-2">
                                    <strong><?php echo htmlspecialchars($penugasan['judul_kegiatan']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo date('d/m/Y H:i', strtotime($penugasan['jadwal_kegiatan'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-wrapper">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <?php echo strtoupper(substr($penugasan['nama_petugas'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($penugasan['nama_petugas']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($penugasan['email_petugas']); ?><br>
                                    <?php echo htmlspecialchars($penugasan['nohp_petugas']); ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($penugasan['status_kegiatan'] === "pending") { ?>
                                    <span class="badge bg-warning">
                                        Pending
                                    </span>
                                <?php } else { ?>
                                    <span class="badge bg-success">
                                        Selesai
                                    </span>
                                <?php } ?>
                            </td>
                            <!-- <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary btn-sm send-notification-btn"
                                        data-id="<?php echo $penugasan['id_penugasan']; ?>"
                                        data-name="<?php echo htmlspecialchars($penugasan['judul_kegiatan']); ?>"
                                        data-petugas="<?php echo htmlspecialchars($penugasan['nama_petugas']); ?>"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Kirim Notifikasi">
                                        <i class="bx bx-bell"></i>
                                    </button>

                                    <button type="button" class="btn btn-danger btn-sm delete-penugasan-btn"
                                        data-id="<?php echo $penugasan['id_penugasan']; ?>"
                                        data-name="<?php echo htmlspecialchars($penugasan['judul_kegiatan']); ?>"
                                        data-petugas="<?php echo htmlspecialchars($penugasan['nama_petugas']); ?>"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Penugasan">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td> -->
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
                    <i class="bx bx-info-circle me-2"></i>Belum ada data Penugasan.
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

function getKegiatanOptions($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $query = "SELECT id_kegiatan, judul_kegiatan, jadwal_kegiatan FROM tb_kegiatan ORDER BY jadwal_kegiatan ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $kegiatans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $kegiatans
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getPetugasOptions($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $query = "SELECT id, nama FROM tb_user WHERE role = 'petugas' ORDER BY nama ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $petugas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $petugas
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getJadwalKegiatan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $kegiatanId = $_POST['kegiatan_id'] ?? '';

        if (empty($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        $query = "SELECT judul_kegiatan, jadwal_kegiatan FROM tb_kegiatan WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$kegiatanId]);
        $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$kegiatan) {
            throw new Exception('Kegiatan tidak ditemukan');
        }

        $jadwalFormatted = date('d/m/Y H:i', strtotime($kegiatan['jadwal_kegiatan']));

        echo json_encode([
            'status' => 'success',
            'jadwal' => $jadwalFormatted
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function checkPetugasConflict($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $kegiatanId = $_POST['kegiatan_id'] ?? '';
        $petugasIds = $_POST['petugas_ids'] ?? [];

        if (empty($kegiatanId) || empty($petugasIds)) {
            echo json_encode([
                'status' => 'success',
                'has_conflict' => false
            ]);
            return;
        }

        // Konversi ke array jika string
        if (is_string($petugasIds)) {
            $petugasIds = json_decode($petugasIds, true);
        }

        if (!is_array($petugasIds)) {
            $petugasIds = [$petugasIds];
        }

        // Ambil jadwal kegiatan yang dipilih
        $query = "SELECT jadwal_kegiatan FROM tb_kegiatan WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$kegiatanId]);
        $selectedKegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$selectedKegiatan) {
            throw new Exception('Kegiatan tidak ditemukan');
        }

        $selectedJadwal = $selectedKegiatan['jadwal_kegiatan'];

        // Cek konflik untuk setiap petugas
        $conflictPetugas = [];

        foreach ($petugasIds as $petugasId) {
            $query = "SELECT DISTINCT
                        u.nama as nama_petugas,
                        k.judul_kegiatan,
                        k.jadwal_kegiatan
                      FROM tb_penugasan p
                      JOIN tb_kegiatan k ON p.id_kegiatan = k.id_kegiatan
                      JOIN tb_user u ON p.id_pegawai = u.id
                      WHERE p.id_pegawai = ? 
                      AND k.jadwal_kegiatan = ?
                      AND p.id_kegiatan != ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$petugasId, $selectedJadwal, $kegiatanId]);
            $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($conflicts)) {
                $conflictPetugas[] = [
                    'nama_petugas' => $conflicts[0]['nama_petugas'],
                    'kegiatan_konflik' => $conflicts[0]['judul_kegiatan']
                ];
            }
        }

        if (!empty($conflictPetugas)) {
            $conflictNames = array_map(function ($item) {
                return $item['nama_petugas'] . ' (sudah ditugaskan di: ' . $item['kegiatan_konflik'] . ')';
            }, $conflictPetugas);

            $message = 'Konflik jadwal ditemukan: ' . implode(', ', $conflictNames);

            echo json_encode([
                'status' => 'success',
                'has_conflict' => true,
                'message' => $message
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'has_conflict' => false
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

function addPenugasanWithNotification($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $kegiatanId = $_POST['id_kegiatan'] ?? '';
        $petugasIds = $_POST['id_pegawai'] ?? [];
        $sendNotification = $_POST['send_notification'] ?? 'yes'; // Default kirim notifikasi

        // Validasi input (sama seperti addPenugasan yang ada)
        if (empty($kegiatanId)) {
            throw new Exception('Kegiatan harus dipilih');
        }

        if (empty($petugasIds) || !is_array($petugasIds)) {
            throw new Exception('Minimal satu petugas harus dipilih');
        }

        // Validasi kegiatan exists
        $query = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$kegiatanId]);
        $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$kegiatan) {
            throw new Exception('Kegiatan tidak ditemukan');
        }

        // Cek konflik jadwal (sama seperti addPenugasan yang ada)
        $selectedJadwal = $kegiatan['jadwal_kegiatan'];
        $conflictPetugas = [];

        foreach ($petugasIds as $petugasId) {
            // Validasi petugas exists
            $query = "SELECT nama FROM tb_user WHERE id = ? AND role = 'petugas'";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$petugasId]);
            $petugas = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$petugas) {
                throw new Exception('Petugas dengan ID ' . $petugasId . ' tidak ditemukan');
            }

            // Cek konflik jadwal
            $query = "SELECT DISTINCT u.nama as nama_petugas, k.judul_kegiatan
                      FROM tb_penugasan p
                      JOIN tb_kegiatan k ON p.id_kegiatan = k.id_kegiatan
                      JOIN tb_user u ON p.id_pegawai = u.id
                      WHERE p.id_pegawai = ? AND k.jadwal_kegiatan = ? AND p.id_kegiatan != ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$petugasId, $selectedJadwal, $kegiatanId]);
            $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($conflicts)) {
                $conflictPetugas[] = $petugas['nama'] . ' (sudah ditugaskan di: ' . $conflicts[0]['judul_kegiatan'] . ')';
            }

            // Cek apakah petugas sudah ditugaskan di kegiatan yang sama
            $query = "SELECT COUNT(*) as count FROM tb_penugasan WHERE id_kegiatan = ? AND id_pegawai = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$kegiatanId, $petugasId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing['count'] > 0) {
                throw new Exception($petugas['nama'] . ' sudah ditugaskan di kegiatan ini');
            }
        }

        // Jika ada konflik, tolak
        if (!empty($conflictPetugas)) {
            throw new Exception('Konflik jadwal: ' . implode(', ', $conflictPetugas));
        }

        // Mulai transaksi
        $pdo->beginTransaction();

        // Insert penugasan untuk setiap petugas
        $insertQuery = "INSERT INTO tb_penugasan (id_kegiatan, id_pegawai) VALUES (?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);

        $successCount = 0;
        foreach ($petugasIds as $petugasId) {
            if ($insertStmt->execute([$kegiatanId, $petugasId])) {
                $successCount++;
            }
        }

        if ($successCount === count($petugasIds)) {
            $pdo->commit();

            // Kirim notifikasi jika diminta
            $notificationMessage = '';
            if ($sendNotification === 'yes') {
                try {
                    // Initialize Telegram Bot menggunakan koneksi dari koneksi.php
                    $telegramController = new NotificationTelegramController();

                    // Ambil daftar nama petugas untuk ditampilkan dalam notifikasi
                    $petugasNames = [];
                    foreach ($petugasIds as $petugasId) {
                        $query = "SELECT nama FROM tb_user WHERE id = ?";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([$petugasId]);
                        $petugasData = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($petugasData) {
                            $petugasNames[] = $petugasData['nama'];
                        }
                    }

                    // Buat custom message untuk group
                    $customMessage = "🔔 *PENUGASAN BARU*\n\n" .
                        "📋 *Kegiatan:* {$kegiatan['judul_kegiatan']}\n" .
                        "📞 *Narahubung:* {$kegiatan['narahubung_kegiatan']}\n" .
                        "📍 *Alamat:* {$kegiatan['alamat_kegiatan']}\n" .
                        "🗺️ *Lokasi:* {$kegiatan['lokasi_kegiatan']}\n" .
                        "📅 *Jadwal:* " . date('d/m/Y H:i', strtotime($kegiatan['jadwal_kegiatan'])) . "\n" .
                        "📝 *Deskripsi:* {$kegiatan['deksripsi_kegiatan']}\n" .
                        "👥 *Petugas yang Baru Ditugaskan:*\n";

                    foreach ($petugasNames as $index => $nama) {
                        $customMessage .= ($index + 1) . ". {$nama}\n";
                    }

                    $customMessage .= "\n💼 Mohon bersiap dan catat jadwal ini dengan baik!\n\n" .
                        "🔗 *Link Kehadiran:* {$kegiatan['kehadiran_kegiatan']}";

                    $notifResult = $telegramController->sendManualNotification($kegiatanId, $customMessage);

                    if ($notifResult['status'] === 'success') {
                        $notificationMessage = " Notifikasi berhasil dikirim ke group untuk {$notifResult['sent_count']} petugas.";
                    } else {
                        $notificationMessage = " Notifikasi gagal dikirim: {$notifResult['message']}";
                    }
                } catch (Exception $e) {
                    $notificationMessage = " Notifikasi gagal dikirim: " . $e->getMessage();
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => "Berhasil menambahkan {$successCount} penugasan petugas ke kegiatan \"{$kegiatan['judul_kegiatan']}\".{$notificationMessage}"
            ]);
        } else {
            $pdo->rollBack();
            throw new Exception('Gagal menambahkan beberapa penugasan');
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function deletePenugasan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $penugasanId = $_POST['penugasan_id'] ?? '';
        $kegiatanName = $_POST['kegiatan_name'] ?? '';
        $petugasName = $_POST['petugas_name'] ?? '';

        if (empty($penugasanId) || !is_numeric($penugasanId)) {
            throw new Exception('ID Penugasan tidak valid');
        }

        // Cek apakah penugasan dengan ID tersebut ada
        $checkQuery = "SELECT p.*, k.judul_kegiatan, u.nama as nama_petugas
                       FROM tb_penugasan p
                       JOIN tb_kegiatan k ON p.id_kegiatan = k.id_kegiatan
                       JOIN tb_user u ON p.id_pegawai = u.id
                       WHERE p.id_penugasan = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$penugasanId]);
        $penugasan = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$penugasan) {
            throw new Exception('Penugasan tidak ditemukan');
        }

        // Hapus penugasan dari database
        $deleteQuery = "DELETE FROM tb_penugasan WHERE id_penugasan = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $result = $deleteStmt->execute([$penugasanId]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Penugasan '{$petugasName}' dari kegiatan '{$kegiatanName}' berhasil dihapus"
            ]);
        } else {
            throw new Exception('Gagal menghapus penugasan dari database');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function sendManualNotification($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $penugasanId = $_POST['penugasan_id'] ?? '';

        if (empty($penugasanId) || !is_numeric($penugasanId)) {
            throw new Exception('ID Penugasan tidak valid');
        }

        // Ambil data penugasan dengan detail kegiatan
        $query = "SELECT p.*, k.*, u.nama as nama_petugas, u.nohp
                  FROM tb_penugasan p
                  JOIN tb_kegiatan k ON p.id_kegiatan = k.id_kegiatan
                  JOIN tb_user u ON p.id_pegawai = u.id
                  WHERE p.id_penugasan = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$penugasanId]);
        $penugasan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$penugasan) {
            throw new Exception('Data penugasan tidak ditemukan');
        }

        // Initialize Telegram Bot menggunakan koneksi dari koneksi.php
        $telegramController = new NotificationTelegramController();

        // Buat custom message untuk notifikasi manual ke group
        $customMessage = "📢 *NOTIFIKASI MANUAL KEGIATAN*\n\n" .
            "📋 *Kegiatan:* {$penugasan['judul_kegiatan']}\n" .
            "📞 *Narahubung:* {$penugasan['narahubung_kegiatan']}\n" .
            "📍 *Alamat:* {$penugasan['alamat_kegiatan']}\n" .
            "🗺️ *Lokasi:* {$penugasan['lokasi_kegiatan']}\n" .
            "📅 *Jadwal:* " . date('d/m/Y H:i', strtotime($penugasan['jadwal_kegiatan'])) . "\n" .
            "📝 *Deskripsi:* {$penugasan['deksripsi_kegiatan']}" .
            "⚠️ *Pengingat khusus untuk:* {$penugasan['nama_petugas']}\n\n" .
            "💼 Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\n\n" .
            "🔗 *Link Kehadiran:* {$penugasan['kehadiran_kegiatan']}";

        // Kirim notifikasi manual
        $result = $telegramController->sendManualNotification($penugasan['id_kegiatan'], $customMessage);

        if ($result['status'] === 'success') {
            echo json_encode([
                'status' => 'success',
                'message' => "Notifikasi berhasil dikirim ke group untuk kegiatan '{$penugasan['judul_kegiatan']}'. {$result['message']}"
            ]);
        } else {
            throw new Exception($result['message']);
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

        case 'get_penugasan':
            checkAdminAccess();
            getDataPenugasan($pdo);
            break;

        case 'get_kegiatan_options':
            checkAdminAccess();
            getKegiatanOptions($pdo);
            break;

        case 'get_petugas_options':
            checkAdminAccess();
            getPetugasOptions($pdo);
            break;

        case 'get_jadwal_kegiatan':
            checkAdminAccess();
            getJadwalKegiatan($pdo);
            break;

        case 'check_petugas_conflict':
            checkAdminAccess();
            checkPetugasConflict($pdo);
            break;

        case 'add_penugasan':
            checkAdminAccess();
            addPenugasanWithNotification($pdo); // Ganti dengan fungsi yang baru
            break;

        case 'send_notification':  // Case baru untuk notifikasi manual
            checkAdminAccess();
            sendManualNotification($pdo);
            break;

        case 'delete_penugasan':
            checkAdminAccess();
            deletePenugasan($pdo);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Request tidak valid']);
            break;
    }
    exit();
}
?>