<?php
include "config.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Routing berdasarkan role
$role = $_SESSION['role'];

if ($role === 'admin') {
    include 'dashboard-admin.php';
} elseif ($role === 'pelanggan') {
    include 'dashboard-pelanggan.php';
} else {
    header("Location: logout.php");
    exit;
}
?>