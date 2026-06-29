<?php
session_start();

// Load Composer autoloader & .env
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Database connection (Membaca dari file .env lokal atau Variables Railway)
$host = $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$user = $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$db   = $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?: 'db_atk_2';
$port = $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset utf8mb4
mysqli_set_charset($conn, "utf8mb4");
?>