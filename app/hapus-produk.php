<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
    
    // Get image filename
    $stmt_get = mysqli_prepare($conn, "SELECT gambar FROM produk WHERE id_produk=?");
    mysqli_stmt_bind_param($stmt_get, "i", $id_produk);
    mysqli_stmt_execute($stmt_get);
    $res_get = mysqli_stmt_get_result($stmt_get);
    $produk = mysqli_fetch_assoc($res_get);
    mysqli_stmt_close($stmt_get);

    if ($produk) {
        if ($produk['gambar'] && file_exists('uploads/' . $produk['gambar'])) {
            unlink('uploads/' . $produk['gambar']);
        }
        
        // Delete product
        $stmt = mysqli_prepare($conn, "DELETE FROM produk WHERE id_produk=?");
        mysqli_stmt_bind_param($stmt, "i", $id_produk);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header("Location: produk.php");
exit;
?>
