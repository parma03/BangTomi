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

// Kegiatan Section
function getDataKegiatan($pdo)
{
    try {
        // Pastikan $pdo tersedia
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Get all kegiatan data with user information
        $query = "SELECT * FROM tb_kegiatan ORDER BY judul_kegiatan ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $kegiatans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the output HTML
        ob_start();

        if (count($kegiatans) > 0) {
?>
            <table id="kegiatanTable" class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" style="width: 5%;">#</th>
                        <th scope="col" style="width: 20%;">Judul Kegiatan</th>
                        <th scope="col" style="width: 20%;">Jadwal Kegiatan</th>
                        <th scope="col" style="width: 20%;">Status Kegiatan</th>
                        <th scope="col" style="width: 10%;">Created At</th>
                        <th scope="col" style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($kegiatans as $kegiatan) {
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <div class="ms-2">
                                    <strong><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($kegiatan['jadwal_kegiatan']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($kegiatan['status_kegiatan'] === "pending") { ?>
                                    <span class="badge bg-warning">
                                        <?php echo htmlspecialchars($kegiatan['status_kegiatan']); ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="badge bg-success">
                                        <?php echo htmlspecialchars($kegiatan['status_kegiatan']); ?>
                                    </span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($kegiatan['created_at']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-info view-kegiatan-btn"
                                        data-id="<?php echo $kegiatan['id_kegiatan']; ?>" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Lihat Detail">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary kehadiran-kegiatan-btn"
                                        data-id="<?php echo $kegiatan['id_kegiatan']; ?>" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Kehadiran Kegiatan">
                                        <i class="bx bx-calendar"></i>
                                    </button>
                                    <?php if ($kegiatan['status_kegiatan'] === 'pending') { ?>
                                        <button type="button" class="btn btn-success complete-kegiatan-btn"
                                            data-id="<?php echo $kegiatan['id_kegiatan']; ?>"
                                            data-name="<?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Selesaikan Kegiatan">
                                            <i class="bx bx-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning edit-kegiatan-btn"
                                            data-id="<?php echo $kegiatan['id_kegiatan']; ?>" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Edit Kegiatan">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                    <?php } ?>
                                    <button type="button" class="btn btn-danger delete-kegiatan-btn"
                                        data-id="<?php echo $kegiatan['id_kegiatan']; ?>"
                                        data-name="<?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Kegiatan">
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
                    <i class="bx bx-info-circle me-2"></i>Belum ada data Kegiatan.
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

function getDetailKegiatan($pdo, $kegiatanId)
{
    try {
        // Pastikan $pdo tersedia
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Validasi kegiatan ID
        if (empty($kegiatanId) || !is_numeric($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        // Query menggunakan PDO (bukan mysqli)
        $query = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$kegiatanId]);
        $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($kegiatan) {
            ob_start();
        ?>
            <!-- Responsive Layout -->
            <div class="row g-3">
                <!-- Thumbnail Section - Display media preview -->
                <div class="col-12 col-lg-4">
                    <div class="text-center">
                        <div class="mb-3">
                            <?php
                            $thumbnails = $kegiatan['thumbnails_kegiatan'];
                            if (!empty($thumbnails)) {
                                // Cek apakah file adalah video atau gambar
                                $fileExtension = strtolower(pathinfo($thumbnails, PATHINFO_EXTENSION));
                                $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                                if (in_array($fileExtension, $videoExtensions)) {
                                    // Tampilkan video player
                                    echo '<video class="img-fluid rounded shadow" style="max-height: 200px; cursor: pointer;" controls onclick="openMediaPreview(\'' . htmlspecialchars($thumbnails) . '\', \'video\')">';
                                    echo '<source src="../../assets/img/thumb/' . htmlspecialchars($thumbnails) . '" type="video/' . $fileExtension . '">';
                                    echo 'Browser Anda tidak mendukung video.';
                                    echo '</video>';
                                } else if (in_array($fileExtension, $imageExtensions)) {
                                    // Tampilkan gambar
                                    echo '<img src="../../assets/img/thumb/' . htmlspecialchars($thumbnails) . '" class="img-fluid rounded shadow" style="max-height: 200px; cursor: pointer;" onclick="openMediaPreview(\'' . htmlspecialchars($thumbnails) . '\', \'image\')" alt="Thumbnail Kegiatan">';
                                } else {
                                    // File tidak dikenali
                                    echo '<div class="alert alert-warning">';
                                    echo '<i class="bx bx-file me-2"></i>File media tidak dapat ditampilkan';
                                    echo '</div>';
                                }
                            } else {
                                // Tidak ada thumbnail
                                echo '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">';
                                echo '<i class="bx bx-image text-muted" style="font-size: 3rem;"></i>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <h5 class="mt-2 mb-2"><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></h5>
                        <span
                            class="badge bg-<?php echo $kegiatan['status_kegiatan'] == 'selesai' ? 'success' : 'warning'; ?> fs-6">
                            <?php echo ucfirst($kegiatan['status_kegiatan']); ?>
                        </span>
                    </div>
                </div>

                <!-- Details Section -->
                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bx bx-info-circle me-2"></i>Informasi Detail Kegiatan
                            </h6>

                            <!-- Responsive Table - Stack on mobile -->
                            <div class="table-responsive">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" style="width: 35%;">
                                                <i class="bx bx-hash me-1 text-muted"></i>ID Kegiatan:
                                            </td>
                                            <td><?php echo htmlspecialchars($kegiatan['id_kegiatan']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-bookmark me-1 text-muted"></i>Judul:
                                            </td>
                                            <td class="text-break">
                                                <?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-detail me-1 text-muted"></i>Deskripsi:
                                            </td>
                                            <td class="text-break">
                                                <small><?php echo nl2br(htmlspecialchars($kegiatan['deksripsi_kegiatan'])); ?></small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-calendar me-1 text-muted"></i>Jadwal:
                                            </td>
                                            <td>
                                                <?php
                                                if (!empty($kegiatan['jadwal_kegiatan'])) {
                                                    echo date('d M Y H:i', strtotime($kegiatan['jadwal_kegiatan']));
                                                } else {
                                                    echo 'Belum ditentukan';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-user-check me-1 text-muted"></i>Kehadiran:
                                            </td>
                                            <td class="text-break">
                                                <?php
                                                if (!empty($kegiatan['kehadiran_kegiatan'])) {
                                                    echo '<a href="' . htmlspecialchars($kegiatan['kehadiran_kegiatan']) . '" target="_blank" class="btn btn-sm btn-outline-primary">';
                                                    echo '<i class="bx bx-link-external me-1"></i>Lihat SpreadSheets';
                                                    echo '</a>';
                                                } else {
                                                    echo '<span class="text-muted">Belum tersedia</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">
                                                <i class="bx bx-calendar-plus me-1 text-muted"></i>Dibuat:
                                            </td>
                                            <td>
                                                <small>
                                                    <?php
                                                    if (!empty($kegiatan['created_at'])) {
                                                        echo date('d M Y H:i', strtotime($kegiatan['created_at']));
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
                                                    if (!empty($kegiatan['updated_at'])) {
                                                        echo date('d M Y H:i', strtotime($kegiatan['updated_at']));
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

            <!-- Media Preview Modal -->
            <div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mediaPreviewModalLabel">Preview Media</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="mediaPreviewContent"></div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function openMediaPreview(mediaUrl, mediaType) {
                    const modalContent = document.getElementById('mediaPreviewContent');

                    if (mediaType === 'video') {
                        modalContent.innerHTML = `
                        <video class="img-fluid" controls style="max-width: 100%; max-height: 70vh;">
                            <source src="../../assets/img/thumb/${mediaUrl}" type="video/mp4">
                            Browser Anda tidak mendukung video.
                        </video>
                    `;
                    } else if (mediaType === 'image') {
                        modalContent.innerHTML = `
                        <img src="../../assets/img/thumb/${mediaUrl}" class="img-fluid" style="max-width: 100%; max-height: 70vh;" alt="Preview">
                    `;
                    }

                    // Show modal using Bootstrap 5
                    const modal = new bootstrap.Modal(document.getElementById('mediaPreviewModal'));
                    modal.show();
                }
            </script>
        <?php
            $html = ob_get_clean();
            echo json_encode(['status' => 'success', 'html' => $html]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data kegiatan tidak ditemukan']);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getKegiatanById($pdo, $kegiatanId)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        if (empty($kegiatanId) || !is_numeric($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        $query = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$kegiatanId]);
        $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($kegiatan) {
            echo json_encode([
                'status' => 'success',
                'data' => $kegiatan
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Data kegiatan tidak ditemukan'
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

function addKegiatan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Validasi input
        $judul_kegiatan = $_POST['judul_kegiatan'] ?? '';
        $jadwal_kegiatan = $_POST['jadwal_kegiatan'] ?? '';
        $deksripsi_kegiatan = $_POST['deksripsi_kegiatan'] ?? '';
        $kehadiran_kegiatan = $_POST['kehadiran_kegiatan'] ?? '';

        // Validasi input wajib
        if (empty($judul_kegiatan) || empty($jadwal_kegiatan) || empty($kehadiran_kegiatan)) {
            throw new Exception('Semua field wajib diisi');
        }

        // Handle upload foto profile
        $photo_profile = null;
        if (isset($_FILES['thumbnailImg']) && $_FILES['thumbnailImg']['error'] === UPLOAD_ERR_OK) {
            $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
            $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm', 'video/mkv'];
            $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);

            $maxSize = 50 * 1024 * 1024; // 50MB untuk mendukung video

            // Validasi tipe file
            if (!in_array($_FILES['thumbnailImg']['type'], $allowedTypes)) {
                throw new Exception('Format file tidak valid! Gunakan gambar (JPG, PNG, GIF, WEBP, BMP) atau video (MP4, AVI, MOV, WMV, FLV, WEBM, MKV)');
            }

            // Validasi ukuran file
            if ($_FILES['thumbnailImg']['size'] > $maxSize) {
                throw new Exception('Ukuran file terlalu besar! Maksimal 50MB');
            }

            // Buat nama file unik
            $extension = pathinfo($_FILES['thumbnailImg']['name'], PATHINFO_EXTENSION);
            $filename = 'kegiatan_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = '../../../assets/img/thumb/';

            // Buat folder jika belum ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fullPath = $uploadPath . $filename;

            if (move_uploaded_file($_FILES['thumbnailImg']['tmp_name'], $fullPath)) {
                $photo_profile = $filename;
            } else {
                throw new Exception('Gagal upload Thumbnail');
            }
        }

        // Insert data kegiatan baru
        $sql = "INSERT INTO tb_kegiatan (judul_kegiatan, deksripsi_kegiatan, jadwal_kegiatan, kehadiran_kegiatan, thumbnails_kegiatan, status_kegiatan, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $judul_kegiatan,
            $deksripsi_kegiatan,
            $jadwal_kegiatan,
            $kehadiran_kegiatan,
            $photo_profile
        ]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Kegiatan berhasil ditambahkan'
            ]);
        } else {
            throw new Exception('Gagal menambahkan kegiatan');
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

function deleteKegiatan($pdo, $kegiatanId, $kegiatanName)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        if (empty($kegiatanId) || !is_numeric($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        if (empty($kegiatanName)) {
            throw new Exception('Nama Kegiatan tidak valid');
        }

        // Cek apakah kegiatan dengan ID tersebut ada
        $checkQuery = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$kegiatanId]);
        $kegiatan = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$kegiatan) {
            throw new Exception('Kegiatan tidak ditemukan');
        }

        // Cek apakah ini kegiatan terakhir (opsional - untuk mencegah hapus kegiatan terakhir)
        $countQuery = "SELECT COUNT(*) as total FROM tb_kegiatan";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute();
        $totalKegiatan = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalKegiatan <= 1) {
            throw new Exception('Tidak dapat menghapus kegiatan terakhir! Sistem harus memiliki minimal 1 kegiatan.');
        }

        // Hapus foto profile jika ada
        if (!empty($kegiatan['thumbnails_kegiatan'])) {
            $photoPath = '../../../assets/img/thumb/' . $kegiatan['thumbnails_kegiatan'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        // Hapus kegiatan dari database
        $deleteQuery = "DELETE FROM tb_kegiatan WHERE id_kegiatan = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $result = $deleteStmt->execute([$kegiatanId]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Kegiatan '{$kegiatanName}' berhasil dihapus"
            ]);
        } else {
            throw new Exception('Gagal menghapus kegiatan dari database');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}
function updateKegiatan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // Ambil data dari POST
        $kegiatanId = $_POST['idKegiatan'] ?? '';
        $judul_kegiatan = $_POST['judul_kegiatan'] ?? '';
        $jadwal_kegiatan = $_POST['jadwal_kegiatan'] ?? '';
        $deksripsi_kegiatan = $_POST['deksripsi_kegiatan'] ?? '';
        $kehadiran_kegiatan = $_POST['kehadiran_kegiatan'] ?? '';
        $removeExisting = $_POST['removeExistingKegiatan'] ?? '0';

        // Validasi ID kegiatan
        if (empty($kegiatanId) || !is_numeric($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        // Validasi input wajib
        if (empty($judul_kegiatan) || empty($jadwal_kegiatan) || empty($kehadiran_kegiatan)) {
            throw new Exception('Semua field wajib diisi');
        }

        // Ambil data kegiatan saat ini
        $currentQuery = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
        $currentStmt = $pdo->prepare($currentQuery);
        $currentStmt->execute([$kegiatanId]);
        $currentKegiatan = $currentStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentKegiatan) {
            throw new Exception('Data kegiatan tidak ditemukan');
        }

        // Handle foto profile
        $photo_profile = $currentKegiatan['thumbnails_kegiatan'];

        // Jika ada request untuk hapus foto existing
        if ($removeExisting === '1') {
            if (!empty($currentKegiatan['thumbnails_kegiatan'])) {
                $oldPhotoPath = '../../../assets/img/thumb/' . $currentKegiatan['thumbnails_kegiatan'];
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            $photo_profile = null;
        }

        // Handle upload foto baru
        if (isset($_FILES['thumbnailImg']) && $_FILES['thumbnailImg']['error'] === UPLOAD_ERR_OK) {
            $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
            $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm', 'video/mkv'];
            $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);

            $maxSize = 50 * 1024 * 1024; // 50MB untuk mendukung video

            // Validasi tipe file
            if (!in_array($_FILES['thumbnailImg']['type'], $allowedTypes)) {
                throw new Exception('Format file tidak valid! Gunakan gambar (JPG, PNG, GIF, WEBP, BMP) atau video (MP4, AVI, MOV, WMV, FLV, WEBM, MKV)');
            }

            // Validasi ukuran file
            if ($_FILES['thumbnailImg']['size'] > $maxSize) {
                throw new Exception('Ukuran file terlalu besar! Maksimal 50MB');
            }

            // Buat nama file unik
            $extension = pathinfo($_FILES['thumbnailImg']['name'], PATHINFO_EXTENSION);
            $filename = 'kegiatan_' . $kegiatanId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = '../../../assets/img/thumb/';

            // Buat folder jika belum ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fullPath = $uploadPath . $filename;

            if (move_uploaded_file($_FILES['thumbnailImg']['tmp_name'], $fullPath)) {
                // Hapus foto lama jika ada
                if (!empty($currentKegiatan['thumbnails_kegiatan'])) {
                    $oldPhotoPath = '../../../assets/img/thumb/' . $currentKegiatan['thumbnails_kegiatan'];
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
        $sql = "UPDATE tb_kegiatan SET deksripsi_kegiatan = ?, jadwal_kegiatan = ?, judul_kegiatan = ?, kehadiran_kegiatan = ?, thumbnails_kegiatan = ?, updated_at = NOW() WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $deksripsi_kegiatan,
            $jadwal_kegiatan,
            $judul_kegiatan,
            $kehadiran_kegiatan,
            $photo_profile,
            $kegiatanId
        ]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Data kegiatan berhasil diupdate'
            ]);
        } else {
            throw new Exception('Gagal mengupdate data kegiatan');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function getKehadiranKegiatan($pdo, $kegiatanId)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        if (empty($kegiatanId) || !is_numeric($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        // Get kegiatan data
        $query = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$kegiatanId]);
        $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$kegiatan) {
            throw new Exception('Data kegiatan tidak ditemukan');
        }

        // Check if kehadiran_kegiatan URL exists
        if (empty($kegiatan['kehadiran_kegiatan'])) {
            throw new Exception('Link kehadiran belum tersedia untuk kegiatan ini');
        }

        // Convert Google Sheets URL to JSON format
        $jsonUrl = convertSheetsUrlToJson($kegiatan['kehadiran_kegiatan']);

        // Fetch JSON data
        $jsonData = fetchJsonData($jsonUrl);

        if ($jsonData === false) {
            throw new Exception('Gagal mengambil data kehadiran dari Google Sheets');
        }

        // Parse JSON data
        $kehadiranData = parseKehadiranJson($jsonData);

        ob_start();
        ?>
        <div class="modal fade" id="kehadiranModal" tabindex="-1" aria-labelledby="kehadiranModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="kehadiranModalLabel">
                            <i class="bx bx-user-check me-2"></i>Data Kehadiran:
                            <?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-check-circle display-4 mb-2"></i>
                                        <h3 class="mb-1"><?php echo $kehadiranData['stats']['hadir']; ?></h3>
                                        <p class="mb-0 small">Hadir</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-warning text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-user-voice display-4 mb-2"></i>
                                        <h3 class="mb-1"><?php echo $kehadiranData['stats']['diwakili']; ?></h3>
                                        <p class="mb-0 small">Diwakili</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-group display-4 mb-2"></i>
                                        <h3 class="mb-1"><?php echo $kehadiranData['stats']['total']; ?></h3>
                                        <p class="mb-0 small">Total Konfirmasi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-secondary text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-calendar display-4 mb-2"></i>
                                        <h3 class="mb-1"><?php echo date('d/m/Y', strtotime($kegiatan['jadwal_kegiatan'])); ?>
                                        </h3>
                                        <p class="mb-0 small">Tanggal Kegiatan</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter dan Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="searchKehadiran"
                                        placeholder="Cari instansi atau nama...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="Hadir">Hadir</option>
                                    <option value="Diwakili">Diwakili</option>
                                </select>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="kehadiranTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 25%;">Instansi/Lembaga</th>
                                        <th style="width: 20%;">Pimpinan</th>
                                        <th style="width: 15%;">Status</th>
                                        <th style="width: 20%;">Perwakilan</th>
                                        <th style="width: 15%;">Kontak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($kehadiranData['data'] as $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                                        style="width: 35px; height: 35px; font-size: 12px;">
                                                        <?php echo strtoupper(substr($row['instansi'], 0, 2)); ?>
                                                    </div>
                                                    <div>
                                                        <strong class="text-truncate"
                                                            style="max-width: 200px; display: inline-block;"
                                                            title="<?php echo htmlspecialchars($row['instansi']); ?>">
                                                            <?php echo htmlspecialchars($row['instansi']); ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                    title="<?php echo htmlspecialchars($row['pimpinan']); ?>">
                                                    <?php echo htmlspecialchars($row['pimpinan']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (strtolower($row['status']) === 'hadir') { ?>
                                                    <span class="badge bg-success">
                                                        <i class="bx bx-check me-1"></i>Hadir
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="badge bg-warning">
                                                        <i class="bx bx-user-voice me-1"></i>Diwakili
                                                    </span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['perwakilan'])) { ?>
                                                    <small>
                                                        <strong><?php echo htmlspecialchars($row['perwakilan']); ?></strong><br>
                                                        <em
                                                            class="text-muted"><?php echo htmlspecialchars($row['jabatan_perwakilan']); ?></em>
                                                    </small>
                                                <?php } else { ?>
                                                    <span class="text-muted">-</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['kontak'])) { ?>
                                                    <a href="tel:<?php echo htmlspecialchars($row['kontak']); ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-phone me-1"></i><?php echo htmlspecialchars($row['kontak']); ?>
                                                    </a>
                                                <?php } else { ?>
                                                    <span class="text-muted">-</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="me-auto">
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Data diperbarui secara real-time dari Google Sheets
                            </small>
                        </div>
                        <a href="<?php echo htmlspecialchars($kegiatan['kehadiran_kegiatan']); ?>" target="_blank"
                            class="btn btn-success">
                            <i class="bx bx-link-external me-1"></i>Buka Google Sheets
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Initialize search and filter
                $('#searchKehadiran').on('keyup', function() {
                    filterTable();
                });

                $('#filterStatus').on('change', function() {
                    filterTable();
                });

                function filterTable() {
                    const searchValue = $('#searchKehadiran').val().toLowerCase();
                    const statusFilter = $('#filterStatus').val();

                    $('#kehadiranTable tbody tr').each(function() {
                        const row = $(this);
                        const instansi = row.find('td:nth-child(2)').text().toLowerCase();
                        const pimpinan = row.find('td:nth-child(3)').text().toLowerCase();
                        const status = row.find('td:nth-child(4) .badge').text().trim();

                        let showRow = true;

                        // Filter by search
                        if (searchValue && !instansi.includes(searchValue) && !pimpinan.includes(searchValue)) {
                            showRow = false;
                        }

                        // Filter by status
                        if (statusFilter && status !== statusFilter) {
                            showRow = false;
                        }

                        row.toggle(showRow);
                    });
                }
            });
        </script>
<?php
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

function convertSheetsUrlToJson($sheetsUrl)
{
    // Extract sheet ID from various Google Sheets URL formats
    $pattern = '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
    preg_match($pattern, $sheetsUrl, $matches);

    if (!isset($matches[1])) {
        throw new Exception('Invalid Google Sheets URL format');
    }

    $sheetId = $matches[1];

    // Extract gid (sheet tab ID) if present
    $gid = '0'; // default to first sheet
    if (preg_match('/[#&]gid=([0-9]+)/', $sheetsUrl, $gidMatches)) {
        $gid = $gidMatches[1];
    }

    // Return JSON API URL
    // return "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values/Sheet1?key=YOUR_API_KEY";

    // Alternative: Use the simpler JSON export (no API key needed but limited)
    return "https://docs.google.com/spreadsheets/d/{$sheetId}/gviz/tq?tqx=out:json&gid={$gid}";
}

function fetchJsonData($jsonUrl)
{
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $jsonUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $data === false) {
            throw new Exception("HTTP Error: {$httpCode}, cURL Error: {$error}");
        }

        // If using Google Visualization API (gviz), clean the response
        if (strpos($jsonUrl, 'gviz/tq') !== false) {
            // Remove the callback wrapper: google.visualization.Query.setResponse(...)
            $data = preg_replace('/^.*?google\.visualization\.Query\.setResponse\((.*)\);?$/s', '$1', $data);
        }

        $jsonData = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        return $jsonData;
    } catch (Exception $e) {
        error_log("fetchJsonData Error: " . $e->getMessage());
        return false;
    }
}

function parseKehadiranJson($jsonData)
{
    $result = [
        'data' => [],
        'stats' => [
            'total' => 0,
            'hadir' => 0,
            'diwakili' => 0
        ]
    ];

    try {
        // Handle different JSON formats
        $rows = [];

        if (isset($jsonData['values'])) {
            // Google Sheets API v4 format
            $rows = $jsonData['values'];
        } elseif (isset($jsonData['table']['rows'])) {
            // Google Visualization API format
            $rows = array_map(function ($row) {
                return array_map(function ($cell) {
                    return isset($cell['v']) ? $cell['v'] : '';
                }, $row['c'] ?? []);
            }, $jsonData['table']['rows']);
        }

        // Skip header row (index 0)
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Ensure we have enough columns
            while (count($row) < 11) {
                $row[] = '';
            }

            $status = trim($row[3] ?? '');
            $perwakilan = '';
            $jabatanPerwakilan = '';
            $kontak = '';

            // Determine contact info based on status
            if (strtolower($status) === 'diwakili') {
                $perwakilan = trim($row[4] ?? '');
                $jabatanPerwakilan = trim($row[5] ?? '');
                $kontak = trim($row[8] ?? '');
            } else {
                $kontak = trim($row[11] ?? '');
            }

            $rowData = [
                'timestamp' => trim($row[0] ?? ''),
                'instansi' => trim($row[1] ?? ''),
                'pimpinan' => trim($row[2] ?? ''),
                'status' => $status,
                'perwakilan' => $perwakilan,
                'jabatan_perwakilan' => $jabatanPerwakilan,
                'kontak' => $kontak
            ];

            // Only add rows with valid data
            if (!empty($rowData['instansi']) && !empty($rowData['status'])) {
                $result['data'][] = $rowData;
                $result['stats']['total']++;

                if (strtolower($status) === 'hadir') {
                    $result['stats']['hadir']++;
                } elseif (strtolower($status) === 'diwakili') {
                    $result['stats']['diwakili']++;
                }
            }
        }
    } catch (Exception $e) {
        error_log("parseKehadiranJson Error: " . $e->getMessage());
        throw new Exception('Gagal memproses data JSON: ' . $e->getMessage());
    }

    return $result;
}

function selesaikanKegiatan($pdo)
{
    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $kegiatanId = $_POST['kegiatanIdSelesai'] ?? null;

        if (empty($kegiatanId) || !is_numeric($kegiatanId)) {
            throw new Exception('ID Kegiatan tidak valid');
        }

        // Cek apakah kegiatan ada dan belum selesai
        $checkQuery = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ? AND status_kegiatan != 'selesai'";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$kegiatanId]);
        $kegiatan = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$kegiatan) {
            throw new Exception('Kegiatan tidak ditemukan atau sudah selesai');
        }

        // Validasi file upload
        if (!isset($_FILES['dokumentasiFiles']) || empty($_FILES['dokumentasiFiles']['name'][0])) {
            throw new Exception('Minimal upload 1 file dokumentasi');
        }

        $uploadPath = '../../../assets/img/dokumentasi/';

        // Buat folder jika belum ada
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploadedFiles = [];
        $allowedTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'video/mp4',
            'video/avi',
            'video/mov',
            'video/wmv',
            'video/webm'
        ];
        $maxSize = 50 * 1024 * 1024; // 50MB

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Process multiple files
            $files = $_FILES['dokumentasiFiles'];
            $fileCount = count($files['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    // Validasi tipe file
                    if (!in_array($files['type'][$i], $allowedTypes)) {
                        throw new Exception("File {$files['name'][$i]} memiliki format yang tidak didukung");
                    }

                    // Validasi ukuran file
                    if ($files['size'][$i] > $maxSize) {
                        throw new Exception("File {$files['name'][$i]} terlalu besar (max 50MB)");
                    }

                    // Generate unique filename
                    $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $filename = 'dokumentasi_' . $kegiatanId . '_' . time() . '_' . $i . '.' . $extension;
                    $fullPath = $uploadPath . $filename;

                    // Upload file
                    if (move_uploaded_file($files['tmp_name'][$i], $fullPath)) {
                        $uploadedFiles[] = $filename;

                        // Insert ke tb_record_kegiatan
                        $insertRecordQuery = "INSERT INTO tb_record_kegiatan (id_kegiatan, record_kegiatan) VALUES (?, ?)";
                        $insertRecordStmt = $pdo->prepare($insertRecordQuery);
                        $insertRecordStmt->execute([$kegiatanId, $filename]);
                    } else {
                        throw new Exception("Gagal upload file {$files['name'][$i]}");
                    }
                }
            }

            if (empty($uploadedFiles)) {
                throw new Exception('Tidak ada file yang berhasil diupload');
            }

            // Update status kegiatan menjadi selesai
            $updateQuery = "UPDATE tb_kegiatan SET status_kegiatan = 'selesai', updated_at = NOW() WHERE id_kegiatan = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateResult = $updateStmt->execute([$kegiatanId]);

            if (!$updateResult) {
                throw new Exception('Gagal update status kegiatan');
            }

            // Commit transaction
            $pdo->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Kegiatan berhasil diselesaikan dengan ' . count($uploadedFiles) . ' file dokumentasi'
            ]);
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollback();

            // Hapus file yang sudah terupload jika ada error
            foreach ($uploadedFiles as $file) {
                if (file_exists($uploadPath . $file)) {
                    unlink($uploadPath . $file);
                }
            }

            throw $e;
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

        case 'get_kegiatan':
            checkAdminAccess();
            getDataKegiatan($pdo);
            break;

        case 'get_kegiatan_detail':
            checkAdminAccess();
            $kegiatanId = $_POST['kegiatan_id'] ?? null;
            if ($kegiatanId) {
                getDetailKegiatan($pdo, $kegiatanId);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID Kegiatan tidak ditemukan']);
            }
            break;

        case 'add_kegiatan':
            checkAdminAccess();
            addKegiatan($pdo);
            break;

        case 'get_kegiatan_by_id':
            checkAdminAccess();
            $kegiatanId = $_POST['kegiatan_id'] ?? null;
            if ($kegiatanId) {
                getKegiatanById($pdo, $kegiatanId);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID Kegiatan tidak ditemukan']);
            }
            break;

        case 'delete_kegiatan':
            checkAdminAccess();
            $kegiatanId = $_POST['kegiatan_id'] ?? null;
            $kegiatanName = $_POST['kegiatan_name'] ?? null;
            if ($kegiatanId && $kegiatanName) {
                deleteKegiatan($pdo, $kegiatanId, $kegiatanName);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data kegiatan tidak lengkap']);
            }
            break;

        case 'update_kegiatan':
            checkAdminAccess();
            updateKegiatan($pdo);
            break;

        case 'get_kehadiran_kegiatan':
            checkAdminAccess();
            $kegiatanId = $_POST['kegiatan_id'] ?? null;
            if ($kegiatanId) {
                getKehadiranKegiatan($pdo, $kegiatanId);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID Kegiatan tidak ditemukan']);
            }
            break;

        case 'selesaikan_kegiatan':
            checkAdminAccess();
            selesaikanKegiatan($pdo);
            break;


        default:
            echo json_encode(['success' => false, 'message' => 'Request tidak valid']);
            break;
    }
    exit();
}
?>