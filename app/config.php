<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "rara_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset utf8mb4
mysqli_set_charset($conn, "utf8mb4");
?>