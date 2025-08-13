<?php
// cron_notification.php - File untuk dijalankan oleh cron job

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Include file yang diperlukan
require_once __DIR__ . '/db/koneksi.php';
require_once __DIR__ . '/kegiatan/controller/NotificationTelegramController.php';

// Log file path
$logFile = __DIR__ . '/logs/cron_notification.log';

// Pastikan direktori logs ada
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Function untuk menulis log
function writeLog($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

try {
    writeLog("=== CRON NOTIFICATION STARTED ===");

    // Gunakan koneksi dari koneksi.php
    $database = getMySQLiConnection();
    $botToken = getTelegramBotToken();
    $groupChatId = getTelegramGroupChatId();

    writeLog("Database connection established from koneksi.php");
    writeLog("Bot Token: " . substr($botToken, 0, 10) . "...");
    writeLog("Group Chat ID: " . $groupChatId);

    // Initialize Telegram Controller (akan otomatis menggunakan koneksi dari koneksi.php)
    $telegramController = new NotificationTelegramController();
    writeLog("Telegram controller initialized for group notifications");

    // Jalankan notifikasi terjadwal
    $result = $telegramController->sendScheduledNotifications();

    if ($result['status'] === 'success') {
        writeLog("SUCCESS: {$result['message']} - Processed: {$result['processed_count']} kegiatan");

        // Output untuk cron log
        echo "SUCCESS: Group notification cron job completed successfully\n";
        echo "Processed: {$result['processed_count']} kegiatan\n";
        echo "Message: {$result['message']}\n";
        echo "Group Chat ID: {$groupChatId}\n";
    } else {
        writeLog("ERROR: {$result['message']}");

        // Output untuk cron log
        echo "ERROR: Group notification cron job failed\n";
        echo "Message: {$result['message']}\n";

        // Exit dengan error code
        exit(1);
    }

    writeLog("Group notification process completed");
} catch (Exception $e) {
    $errorMessage = "FATAL ERROR: " . $e->getMessage();
    writeLog($errorMessage);

    // Output untuk cron log
    echo "FATAL ERROR: " . $e->getMessage() . "\n";

    // Exit dengan error code
    exit(1);
} finally {
    writeLog("=== CRON NOTIFICATION ENDED ===");
    writeLog(""); // Empty line untuk separator
}

// Optional: Cleanup old logs (hapus log lebih dari 30 hari)
function cleanupOldLogs()
{
    global $logFile;

    if (file_exists($logFile)) {
        $fileModTime = filemtime($logFile);
        $thirtyDaysAgo = time() - (30 * 24 * 60 * 60); // 30 hari dalam detik

        if ($fileModTime < $thirtyDaysAgo) {
            // Backup log lama sebelum dihapus
            $backupFile = $logFile . '.' . date('Y-m-d', $fileModTime) . '.bak';
            rename($logFile, $backupFile);
            writeLog("Old log file backed up to: " . basename($backupFile));
        }
    }
}

// Jalankan cleanup setiap hari Minggu (hari 0)
if (date('w') == 0) {
    cleanupOldLogs();
}
