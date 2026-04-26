<?php
include "config.php";

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak sesuai!";
    } else {
        $cekEmail = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if (mysqli_num_rows($cekEmail) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $query = mysqli_query($conn, "INSERT INTO users (nama, email, password) 
                                          VALUES ('$nama', '$email', '$passwordHash')");

            if ($query) {
                $success = "Register berhasil! Silakan login.";
            } else {
                $error = "Register gagal!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>Register</h2>
        <p class="subtitle">Buat akun baru Toko ATK</p>

        <?php if ($error != "") : ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success != "") : ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="konfirmasi" placeholder="Ulangi password" required>
            </div>

            <button type="submit" name="register" class="btn">Register</button>
        </form>

        <p class="auth-link">
            Sudah punya akun? <a href="login.php">Login</a>
        </p>
    </div>
</div>

</body>
</html>