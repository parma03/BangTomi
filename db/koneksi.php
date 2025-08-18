<?php
// db/koneksi.php

// Database configuration
$host = 'localhost';
$dbname = 'db_tomi';
$username = 'root';
$password = '';

// Telegram Bot Token
$telegramBotToken = "7831626210:AAEGRBDhQmD12yVA9dSqn5r4kY_2neAAwkg";

// Telegram Group Chat ID (dari JSON response yang Anda berikan)
$telegramGroupChatId = "-4897067780";

// PDO Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// MySQLi Connection (for NotificationTelegramController)
$database = new mysqli($host, $username, $password, $dbname);

if ($database->connect_error) {
    die("MySQLi Connection failed: " . $database->connect_error);
}

// Set charset untuk MySQLi
$database->set_charset("utf8mb4");

// Function to get database connections
function getPDOConnection()
{
    global $pdo;
    return $pdo;
}

function getMySQLiConnection()
{
    global $database;
    return $database;
}

function getTelegramBotToken()
{
    global $telegramBotToken;
    return $telegramBotToken;
}

function getTelegramGroupChatId()
{
    global $telegramGroupChatId;
    return $telegramGroupChatId;
}
