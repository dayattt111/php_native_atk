<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
    
    // Delete product
    $stmt = mysqli_prepare($conn, "DELETE FROM produk WHERE id_produk=?");
    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: produk.php");
exit;
?>
