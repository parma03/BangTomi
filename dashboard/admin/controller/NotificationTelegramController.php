<?php
// controller/NotificationTelegramController.php

class NotificationTelegramController
{
    private $db;
    private $botToken;
    private $apiUrl;

    public function __construct($database, $telegramBotToken)
    {
        $this->db = $database;
        $this->botToken = $telegramBotToken;
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }

    /**
     * Mengirim notifikasi berdasarkan jadwal kegiatan
     * Dipanggil melalui cron job setiap hari
     */
    public function sendScheduledNotifications()
    {
        try {
            $today = date('Y-m-d');

            // Ambil kegiatan yang perlu dikirim notifikasi
            $kegiatanList = $this->getKegiatanForNotification($today);

            foreach ($kegiatanList as $kegiatan) {
                $this->processKegiatanNotification($kegiatan, $today);
            }

            return [
                'status' => 'success',
                'message' => 'Notifikasi berhasil diproses',
                'processed_count' => count($kegiatanList)
            ];
        } catch (Exception $e) {
            error_log("Error in sendScheduledNotifications: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mengambil kegiatan yang perlu dikirim notifikasi berdasarkan tanggal
     */
    private function getKegiatanForNotification($today)
    {
        $sql = "SELECT k.*, 
                       DATEDIFF(DATE(k.jadwal_kegiatan), ?) as days_diff
                FROM tb_kegiatan k 
                WHERE k.status_kegiatan = 'pending' 
                AND (
                    DATEDIFF(DATE(k.jadwal_kegiatan), ?) = 1 OR  -- 1 hari sebelum
                    DATEDIFF(DATE(k.jadwal_kegiatan), ?) = 3 OR   -- 3 hari sebelum  
                    DATE(k.jadwal_kegiatan) = ?                   -- hari H
                )
                ORDER BY k.jadwal_kegiatan ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ssss', $today, $today, $today, $today);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Memproses notifikasi untuk satu kegiatan
     */
    private function processKegiatanNotification($kegiatan, $today)
    {
        // Ambil petugas yang ditugaskan dengan telegram_chat_id
        $petugasList = $this->getAssignedPetugas($kegiatan['id_kegiatan']);

        if (empty($petugasList)) {
            error_log("Tidak ada petugas yang ditugaskan untuk kegiatan ID: " . $kegiatan['id_kegiatan']);
            return;
        }

        // Tentukan jenis notifikasi berdasarkan selisih hari
        $daysDiff = $kegiatan['days_diff'];
        $notificationType = $this->getNotificationType($daysDiff);

        // Buat pesan notifikasi
        $message = $this->createNotificationMessage($kegiatan, $notificationType, $daysDiff);

        // Kirim ke setiap petugas
        foreach ($petugasList as $petugas) {
            $this->sendTelegramMessage($petugas, $message, $kegiatan);
        }
    }

    /**
     * Mengambil daftar petugas yang ditugaskan untuk kegiatan tertentu
     * Modified: Include telegram_chat_id
     */
    private function getAssignedPetugas($idKegiatan)
    {
        $sql = "SELECT u.id, u.nama, u.email, u.nohp, u.role, u.telegram_chat_id
                FROM tb_penugasan p
                JOIN tb_user u ON p.id_pegawai = u.id
                WHERE p.id_kegiatan = ? 
                AND u.role IN ('petugas', 'staf acara')
                AND (
                    (u.telegram_chat_id IS NOT NULL AND u.telegram_chat_id != '') OR
                    (u.nohp IS NOT NULL AND u.nohp != '')
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idKegiatan);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Menentukan jenis notifikasi berdasarkan selisih hari
     */
    private function getNotificationType($daysDiff)
    {
        switch ($daysDiff) {
            case 1:
                return 'reminder_1_days';
            case 3:
                return 'reminder_3_days';
            case 0:
                return 'today';
            default:
                return 'unknown';
        }
    }

    /**
     * Membuat pesan notifikasi
     */
    private function createNotificationMessage($kegiatan, $notificationType, $daysDiff)
    {
        $judulKegiatan = $kegiatan['judul_kegiatan'];
        $deskripsiKegiatan = substr($kegiatan['deksripsi_kegiatan'] ?? '', 0, 150) . '...';
        $jadwalKegiatan = date('d/m/Y H:i', strtotime($kegiatan['jadwal_kegiatan']));
        $linkKehadiran = $kegiatan['kehadiran_kegiatan'] ?? 'Belum tersedia';

        $messages = [
            'reminder_1_days' => "🔔 *PENGINGAT TUGAS - 1 HARI*\n\n" .
                "Halo! Anda memiliki tugas kegiatan dalam 1 hari:\n\n" .
                "📋 *Kegiatan:* {$judulKegiatan}\n" .
                "📅 *Jadwal:* {$jadwalKegiatan}\n" .
                "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
                "Mohon untuk mempersiapkan diri dan koordinasi dengan tim.\n\n" .
                "🔗 *Link Kehadiran:* {$linkKehadiran}",

            'reminder_3_days' => "⚠️ *PENGINGAT TUGAS - 3 HARI LAGI*\n\n" .
                "Kegiatan Anda akan berlangsung dalam 3 hari:\n\n" .
                "📋 *Kegiatan:* {$judulKegiatan}\n" .
                "📅 *Jadwal:* {$jadwalKegiatan}\n" .
                "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
                "⏰ Pastikan Anda sudah siap dan tidak ada bentrok jadwal!\n\n" .
                "🔗 *Link Kehadiran:* {$linkKehadiran}",

            'today' => "🚨 *HARI INI - KEGIATAN BERLANGSUNG*\n\n" .
                "Kegiatan Anda hari ini:\n\n" .
                "📋 *Kegiatan:* {$judulKegiatan}\n" .
                "📅 *Jadwal:* {$jadwalKegiatan}\n" .
                "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
                "💼 Jangan lupa hadir tepat waktu dan bawa perlengkapan yang diperlukan!\n\n" .
                "🔗 *Link Kehadiran:* {$linkKehadiran}"
        ];

        return isset($messages[$notificationType]) ? $messages[$notificationType] : '';
    }

    /**
     * Mengirim pesan ke Telegram
     * Modified: Handle chat_id lebih baik dan tambah error handling
     */
    private function sendTelegramMessage($petugas, $message, $kegiatan)
    {
        try {
            $chatId = $this->getChatId($petugas);

            if (!$chatId) {
                $this->logNotification($kegiatan['id_kegiatan'], $petugas['id'], $petugas['nohp'] ?? '', ['error' => 'No valid chat_id or phone number']);
                return false;
            }

            $data = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true
            ];

            $response = $this->makeApiRequest('sendMessage', $data);

            // Log hasil pengiriman
            $this->logNotification($kegiatan['id_kegiatan'], $petugas['id'], $chatId, $response);

            return $response;
        } catch (Exception $e) {
            error_log("Error sending telegram message to {$petugas['nama']}: " . $e->getMessage());
            $this->logNotification($kegiatan['id_kegiatan'], $petugas['id'], $petugas['nohp'] ?? '', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Mendapatkan chat_id yang valid
     * Priority: telegram_chat_id > formatted phone number
     */
    private function getChatId($petugas)
    {
        // Prioritas pertama: gunakan telegram_chat_id jika ada
        if (!empty($petugas['telegram_chat_id'])) {
            return $petugas['telegram_chat_id'];
        }

        // Prioritas kedua: format nomor HP
        if (!empty($petugas['nohp'])) {
            return $this->formatPhoneNumber($petugas['nohp']);
        }

        return null;
    }

    /**
     * Format nomor HP untuk Telegram
     */
    private function formatPhoneNumber($nohp)
    {
        // Hapus karakter non-numeric
        $phone = preg_replace('/[^0-9]/', '', $nohp);

        // Jika dimulai dengan 08, ganti dengan 628
        if (substr($phone, 0, 2) == '08') {
            $phone = '628' . substr($phone, 2);
        }
        // Jika dimulai dengan 8, tambahkan 62
        elseif (substr($phone, 0, 1) == '8') {
            $phone = '62' . $phone;
        }
        // Jika tidak dimulai dengan 62, tambahkan 62
        elseif (substr($phone, 0, 2) != '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Melakukan request ke Telegram API
     * Modified: Better error handling
     */
    private function makeApiRequest($method, $data)
    {
        $url = $this->apiUrl . $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_error($ch)) {
            curl_close($ch);
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode !== 200) {
            throw new Exception("HTTP Error {$httpCode}: " . ($decodedResponse['description'] ?? 'Unknown error'));
        }

        if (!isset($decodedResponse['ok']) || !$decodedResponse['ok']) {
            $errorDesc = $decodedResponse['description'] ?? 'Unknown error';

            // Specific handling for chat not found
            if (strpos($errorDesc, 'chat not found') !== false) {
                throw new Exception("Chat not found - User belum memulai chat dengan bot atau chat_id tidak valid: " . $errorDesc);
            }

            throw new Exception('Telegram API Error: ' . $errorDesc);
        }

        return $decodedResponse;
    }

    /**
     * Mencatat log notifikasi
     * Modified: Better logging dengan status detail
     */
    private function logNotification($idKegiatan, $idPetugas, $recipient, $response)
    {
        $status = 'failed';
        $error_detail = '';

        if (isset($response['ok']) && $response['ok']) {
            $status = 'sent';
        } elseif (isset($response['error'])) {
            $error_detail = $response['error'];
        } elseif (isset($response['description'])) {
            $error_detail = $response['description'];
        }

        $responseData = json_encode($response);
        $timestamp = date('Y-m-d H:i:s');

        // Jika tabel log belum ada, buat terlebih dahulu
        $this->createLogTableIfNotExists();

        $sql = "INSERT INTO tb_notification_log 
                (id_kegiatan, id_petugas, recipient, status, response_data, error_detail, sent_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iisssss', $idKegiatan, $idPetugas, $recipient, $status, $responseData, $error_detail, $timestamp);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error logging notification: " . $e->getMessage());
        }
    }

    /**
     * Membuat tabel log jika belum ada
     * Modified: Tambah kolom error_detail dan ubah nohp menjadi recipient
     */
    private function createLogTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS tb_notification_log (
                    id_log bigint(11) NOT NULL AUTO_INCREMENT,
                    id_kegiatan bigint(11) NOT NULL,
                    id_petugas int(11) NOT NULL,
                    recipient varchar(50) NOT NULL COMMENT 'Chat ID atau nomor HP',
                    status enum('sent','failed') NOT NULL,
                    response_data text,
                    error_detail text,
                    sent_at timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id_log),
                    KEY idx_kegiatan (id_kegiatan),
                    KEY idx_petugas (id_petugas),
                    KEY idx_status (status),
                    KEY idx_sent_at (sent_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log("Error creating log table: " . $e->getMessage());
        }
    }

    /**
     * Mengirim notifikasi manual untuk kegiatan tertentu
     */
    public function sendManualNotification($idKegiatan, $customMessage = null)
    {
        try {
            $sql = "SELECT * FROM tb_kegiatan WHERE id_kegiatan = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $idKegiatan);
            $stmt->execute();
            $kegiatan = $stmt->get_result()->fetch_assoc();

            if (!$kegiatan) {
                throw new Exception("Kegiatan tidak ditemukan");
            }

            $petugasList = $this->getAssignedPetugas($idKegiatan);

            if (empty($petugasList)) {
                throw new Exception("Tidak ada petugas yang ditugaskan atau tidak memiliki kontak Telegram yang valid");
            }

            $message = $customMessage ?: $this->createManualNotificationMessage($kegiatan);

            $sentCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($petugasList as $petugas) {
                try {
                    $result = $this->sendTelegramMessage($petugas, $message, $kegiatan);
                    if ($result && isset($result['ok']) && $result['ok']) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                        $errors[] = "Gagal kirim ke {$petugas['nama']}: " . ($result['description'] ?? 'Unknown error');
                    }
                } catch (Exception $e) {
                    $failedCount++;
                    $errors[] = "Error kirim ke {$petugas['nama']}: " . $e->getMessage();
                }
            }

            $message = "Notifikasi dikirim ke {$sentCount} petugas";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} gagal";
            }

            return [
                'status' => $sentCount > 0 ? 'success' : 'error',
                'message' => $message,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_petugas' => count($petugasList),
                'errors' => $errors
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Membuat pesan notifikasi manual
     */
    private function createManualNotificationMessage($kegiatan)
    {
        $judulKegiatan = $kegiatan['judul_kegiatan'];
        $deskripsiKegiatan = substr($kegiatan['deksripsi_kegiatan'] ?? '', 0, 150) . '...';
        $jadwalKegiatan = date('d/m/Y H:i', strtotime($kegiatan['jadwal_kegiatan']));
        $linkKehadiran = $kegiatan['kehadiran_kegiatan'] ?? 'Belum tersedia';

        return "📢 *NOTIFIKASI KEGIATAN*\n\n" .
            "📋 *Kegiatan:* {$judulKegiatan}\n" .
            "📅 *Jadwal:* {$jadwalKegiatan}\n" .
            "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
            "Mohon untuk mempersiapkan diri dengan baik.\n\n" .
            "🔗 *Link Kehadiran:* {$linkKehadiran}";
    }

    /**
     * Mendapatkan statistik notifikasi
     */
    public function getNotificationStats($startDate = null, $endDate = null)
    {
        $whereClause = "";
        $params = [];
        $types = "";

        if ($startDate && $endDate) {
            $whereClause = "WHERE sent_at BETWEEN ? AND ?";
            $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
            $types = "ss";
        }

        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    DATE(sent_at) as date
                FROM tb_notification_log 
                {$whereClause}
                GROUP BY status, DATE(sent_at)
                ORDER BY date DESC";

        try {
            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Method baru untuk mendaftarkan chat_id user
     * Dipanggil ketika user mengirim /start ke bot
     */
    public function registerUserChatId($chatId, $phoneNumber = null, $username = null)
    {
        try {
            // Cari user berdasarkan nomor HP atau username
            $sql = "UPDATE tb_user SET telegram_chat_id = ? WHERE ";
            $params = [$chatId];
            $types = "s";

            if ($phoneNumber) {
                // Format nomor HP untuk pencarian
                $formattedPhone = $this->formatPhoneNumber($phoneNumber);
                $sql .= "(nohp = ? OR nohp = ? OR nohp = ?)";
                $params = array_merge($params, [$phoneNumber, $formattedPhone, substr($formattedPhone, 2)]);
                $types .= "sss";
            } elseif ($username) {
                $sql .= "email LIKE ? OR nama LIKE ?";
                $params = array_merge($params, ["%{$username}%", "%{$username}%"]);
                $types .= "ss";
            } else {
                return false;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $success = $stmt->execute();

            return $success && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error registering chat ID: " . $e->getMessage());
            return false;
        }
    }
}

// Database connection
$database = new mysqli(
    "localhost",    // Host database
    "root",         // Username database  
    "",             // Password database
    "db_tomi"       // Nama database
);

// Contoh penggunaan:
/*
// Inisialisasi
$botToken = "8483301260:AAFNldm582v3C0nteKirm-gnE8p1_xSzhS4";
$notificationController = new NotificationTelegramController($database, $botToken);

// Untuk cron job harian
$result = $notificationController->sendScheduledNotifications();
echo json_encode($result);

// Untuk notifikasi manual
$result = $notificationController->sendManualNotification(1);
echo json_encode($result);

// setup cronjob
// 0 8 * * * php /path/to/your/cron_notification.php
*/