<?php
// controller/NotificationTelegramController.php

class NotificationTelegramController
{
    private $db;
    private $botToken;
    private $groupChatId;
    private $apiUrl;

    public function __construct($database = null, $telegramBotToken = null, $telegramGroupChatId = null)
    {
        // Jika parameter tidak diberikan, ambil dari koneksi.php
        if ($database === null || $telegramBotToken === null || $telegramGroupChatId === null) {
            require_once '../../../db/koneksi.php';
            $this->db = $database ?: getMySQLiConnection();
            $this->botToken = $telegramBotToken ?: getTelegramBotToken();
            $this->groupChatId = $telegramGroupChatId ?: getTelegramGroupChatId();
        } else {
            $this->db = $database;
            $this->botToken = $telegramBotToken;
            $this->groupChatId = $telegramGroupChatId;
        }

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
     * Memproses notifikasi untuk satu kegiatan (MODIFIED)
     */
    private function processKegiatanNotification($kegiatan, $today)
    {
        // Ambil petugas yang ditugaskan untuk kegiatan ini
        $petugasList = $this->getAssignedPetugas($kegiatan['id_kegiatan']);

        if (empty($petugasList)) {
            error_log("Tidak ada petugas yang ditugaskan untuk kegiatan ID: " . $kegiatan['id_kegiatan']);
            return;
        }

        // Tentukan jenis notifikasi berdasarkan selisih hari
        $daysDiff = $kegiatan['days_diff'];
        $notificationType = $this->getNotificationType($daysDiff);

        // Buat pesan notifikasi untuk group dengan daftar petugas
        $message = $this->createGroupNotificationMessage($kegiatan, $petugasList, $notificationType, $daysDiff);

        // Kirim ke group chat
        $this->sendTelegramGroupMessage($message, $kegiatan, $petugasList);
    }

    /**
     * Mengambil daftar petugas yang ditugaskan untuk kegiatan tertentu
     * Modified: Tidak lagi memerlukan telegram_chat_id individual
     */
    private function getAssignedPetugas($idKegiatan)
    {
        $sql = "SELECT u.id, u.nama, u.email, u.nohp, u.role
                FROM tb_penugasan p
                JOIN tb_user u ON p.id_pegawai = u.id
                WHERE p.id_kegiatan = ? 
                AND u.role IN ('petugas', 'staf acara')";

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
     * Membuat pesan notifikasi untuk group (NEW METHOD)
     */
    private function createGroupNotificationMessage($kegiatan, $petugasList, $notificationType, $daysDiff)
    {
        $judulKegiatan = $kegiatan['judul_kegiatan'];
        $deskripsiKegiatan = $kegiatan['deksripsi_kegiatan'];
        $jadwalKegiatan = date('d/m/Y H:i', strtotime($kegiatan['jadwal_kegiatan']));
        $linkKehadiran = $kegiatan['kehadiran_kegiatan'] ?? 'Belum tersedia';

        // Format daftar petugas dengan mention atau nama
        $petugasText = '';
        foreach ($petugasList as $index => $petugas) {
            $petugasText .= ($index + 1) . '. ' . $petugas['nama'];
            if (!empty($petugas['nohp'])) {
                $petugasText .= ' (' . $petugas['nohp'] . ')';
            }
            $petugasText .= "\n";
        }

        $messages = [
            'reminder_1_days' => "🔔 *PENGINGAT TUGAS - 1 HARI*\n\n" .
                "📋 *Kegiatan:* {$judulKegiatan}\n" .
                "📅 *Jadwal:* {$jadwalKegiatan}\n" .
                "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
                "👥 *Petugas yang Ditugaskan:*\n{$petugasText}\n" .
                "⚠️ Mohon untuk mempersiapkan diri dan koordinasi dengan tim.\n\n" .
                "🔗 *Link Kehadiran:* {$linkKehadiran}",

            'reminder_3_days' => "⚠️ *PENGINGAT TUGAS - 3 HARI LAGI*\n\n" .
                "📋 *Kegiatan:* {$judulKegiatan}\n" .
                "📅 *Jadwal:* {$jadwalKegiatan}\n" .
                "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
                "👥 *Petugas yang Ditugaskan:*\n{$petugasText}\n" .
                "⏰ Pastikan semua petugas sudah siap dan tidak ada bentrok jadwal!\n\n" .
                "🔗 *Link Kehadiran:* {$linkKehadiran}",

            'today' => "🚨 *HARI INI - KEGIATAN BERLANGSUNG*\n\n" .
                "📋 *Kegiatan:* {$judulKegiatan}\n" .
                "📅 *Jadwal:* {$jadwalKegiatan}\n" .
                "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
                "👥 *Petugas yang Bertugas Hari Ini:*\n{$petugasText}\n" .
                "💼 Mohon semua petugas hadir tepat waktu dan bawa perlengkapan yang diperlukan!\n\n" .
                "🔗 *Link Kehadiran:* {$linkKehadiran}"
        ];

        return isset($messages[$notificationType]) ? $messages[$notificationType] : '';
    }

    /**
     * Mengirim pesan ke Telegram Group (NEW METHOD)
     */
    private function sendTelegramGroupMessage($message, $kegiatan, $petugasList)
    {
        try {
            $data = [
                'chat_id' => $this->groupChatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true
            ];

            $response = $this->makeApiRequest('sendMessage', $data);

            // Log hasil pengiriman untuk setiap petugas
            foreach ($petugasList as $petugas) {
                $this->logNotification($kegiatan['id_kegiatan'], $petugas['id'], $this->groupChatId, $response);
            }

            return $response;
        } catch (Exception $e) {
            error_log("Error sending telegram group message: " . $e->getMessage());

            // Log error untuk setiap petugas
            foreach ($petugasList as $petugas) {
                $this->logNotification($kegiatan['id_kegiatan'], $petugas['id'], $this->groupChatId, ['error' => $e->getMessage()]);
            }

            return false;
        }
    }

    /**
     * Melakukan request ke Telegram API
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
                throw new Exception("Chat not found - Group chat tidak ditemukan atau bot belum ditambahkan ke group: " . $errorDesc);
            }

            throw new Exception('Telegram API Error: ' . $errorDesc);
        }

        return $decodedResponse;
    }

    /**
     * Mencatat log notifikasi
     * Modified: recipient sekarang adalah group chat ID
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
     */
    private function createLogTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS tb_notification_log (
                    id_log bigint(11) NOT NULL AUTO_INCREMENT,
                    id_kegiatan bigint(11) NOT NULL,
                    id_petugas int(11) NOT NULL,
                    recipient varchar(50) NOT NULL COMMENT 'Group Chat ID',
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
     * Mengirim notifikasi manual untuk kegiatan tertentu (MODIFIED)
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
                throw new Exception("Tidak ada petugas yang ditugaskan untuk kegiatan ini");
            }

            // Gunakan custom message atau buat message default untuk group
            $message = $customMessage ?: $this->createManualGroupNotificationMessage($kegiatan, $petugasList);

            try {
                $result = $this->sendTelegramGroupMessage($message, $kegiatan, $petugasList);

                if ($result && isset($result['ok']) && $result['ok']) {
                    return [
                        'status' => 'success',
                        'message' => "Notifikasi berhasil dikirim ke group untuk " . count($petugasList) . " petugas",
                        'sent_count' => count($petugasList),
                        'failed_count' => 0,
                        'total_petugas' => count($petugasList),
                        'errors' => []
                    ];
                } else {
                    throw new Exception($result['description'] ?? 'Unknown error');
                }
            } catch (Exception $e) {
                return [
                    'status' => 'error',
                    'message' => "Gagal mengirim notifikasi ke group: " . $e->getMessage(),
                    'sent_count' => 0,
                    'failed_count' => count($petugasList),
                    'total_petugas' => count($petugasList),
                    'errors' => [$e->getMessage()]
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Membuat pesan notifikasi manual untuk group (NEW METHOD)
     */
    private function createManualGroupNotificationMessage($kegiatan, $petugasList)
    {
        $judulKegiatan = $kegiatan['judul_kegiatan'];
        $deskripsiKegiatan = $kegiatan['deksripsi_kegiatan'];
        $jadwalKegiatan = date('d/m/Y H:i', strtotime($kegiatan['jadwal_kegiatan']));
        $linkKehadiran = $kegiatan['kehadiran_kegiatan'] ?? 'Belum tersedia';

        // Format daftar petugas
        $petugasText = '';
        foreach ($petugasList as $index => $petugas) {
            $petugasText .= ($index + 1) . '. ' . $petugas['nama'];
            if (!empty($petugas['nohp'])) {
                $petugasText .= ' (' . $petugas['nohp'] . ')';
            }
            $petugasText .= "\n";
        }

        return "📢 *NOTIFIKASI KEGIATAN*\n\n" .
            "📋 *Kegiatan:* {$judulKegiatan}\n" .
            "📅 *Jadwal:* {$jadwalKegiatan}\n" .
            "📝 *Deskripsi:* {$deskripsiKegiatan}\n\n" .
            "👥 *Petugas yang Ditugaskan:*\n{$petugasText}\n" .
            "💼 Mohon semua petugas untuk mempersiapkan diri dengan baik.\n\n" .
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
}
