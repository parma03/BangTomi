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

    // Konfigurasi bot Telegram
    $botToken = "8483301260:AAFNldm582v3C0nteKirm-gnE8p1_xSzhS4"; // Ganti dengan token bot Anda

    // Konversi PDO ke MySQLi untuk NotificationTelegramController
    $database = new mysqli(
        $_ENV['DB_HOST'] ?? "localhost",
        $_ENV['DB_USER'] ?? "root",
        $_ENV['DB_PASS'] ?? "",
        $_ENV['DB_NAME'] ?? "db_tomi"
    );

    if ($database->connect_error) {
        throw new Exception("Connection failed: " . $database->connect_error);
    }

    writeLog("Database connection established");

    // Initialize Telegram Controller
    $telegramController = new NotificationTelegramController($database, $botToken);
    writeLog("Telegram controller initialized");

    // Jalankan notifikasi terjadwal
    $result = $telegramController->sendScheduledNotifications();

    if ($result['status'] === 'success') {
        writeLog("SUCCESS: {$result['message']} - Processed: {$result['processed_count']} kegiatan");

        // Output untuk cron log
        echo "SUCCESS: Notification cron job completed successfully\n";
        echo "Processed: {$result['processed_count']} kegiatan\n";
        echo "Message: {$result['message']}\n";
    } else {
        writeLog("ERROR: {$result['message']}");

        // Output untuk cron log
        echo "ERROR: Notification cron job failed\n";
        echo "Message: {$result['message']}\n";

        // Exit dengan error code
        exit(1);
    }

    // Tutup koneksi database
    $database->close();
    writeLog("Database connection closed");
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
