<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_user = (int)$_GET['id'];
    
    // Prevent delete self
    if ($id_user !== $_SESSION['id_user']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header("Location: users.php");
exit;
?>
