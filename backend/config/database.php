<?php
// backend/config/database.php

// Load .env file from project root
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$host     = $_ENV['DB_HOST']     ?? 'localhost';
$username = $_ENV['DB_USER']     ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$dbname   = $_ENV['DB_NAME']     ?? 'spacio_db';
$port     = $_ENV['DB_PORT']     ?? 3306;

// Create connection
$conn = new mysqli($host, $username, $password, $dbname, (int)$port);

// Check connection
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}

$conn->set_charset('utf8mb4');
?>