<?php
include "config.php";
require_once __DIR__ . '/helpers/mail.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error   = "";
$success = "";

if (isset($_POST['register'])) {
    $nama       = trim(mysqli_real_escape_string($conn, $_POST['nama']));
    $email      = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password   = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak sesuai!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $cekEmail = mysqli_query($conn, "SELECT id_user FROM users WHERE email = '$email'");

        if (mysqli_num_rows($cekEmail) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $token        = bin2hex(random_bytes(32)); // 64 karakter hex

            // Insert ke tabel users (status default 'nonaktif', token verifikasi diisi)
            $insert = mysqli_query($conn,
                "INSERT INTO users (nama, email, password, role, status, verification_token)
                 VALUES ('$nama', '$email', '$passwordHash', 'pelanggan', 'nonaktif', '$token')"
            );

            if ($insert) {
                // Kirim email verifikasi via Mailtrap
                $emailTerkirim = sendVerificationEmail($email, $nama, $token);

                if ($emailTerkirim) {
                    $success = "Registrasi berhasil! Silakan cek email <strong>$email</strong> dan klik tautan verifikasi untuk mengaktifkan akun Anda.";
                } else {
                    $success = "Registrasi berhasil, namun email verifikasi gagal dikirim. Hubungi admin.";
                }
            } else {
                $error = "Registrasi gagal: " . mysqli_error($conn);
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
            <div class="alert success"><?= $success; ?></div>
        <?php endif; ?>

        <?php if ($success == "") : ?>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required
                       value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="konfirmasi" placeholder="Ulangi password" required>
            </div>

            <button type="submit" name="register" class="btn">Register</button>
        </form>
        <?php endif; ?>

        <p class="auth-link">
            Sudah punya akun? <a href="login.php">Login</a>
        </p>
    </div>
</div>

</body>
</html>