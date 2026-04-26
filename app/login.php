<?php
include "config.php";

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query untuk mencari user berdasarkan email
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>Login</h2>
        <p class="subtitle">Masuk ke aplikasi Toko ATK</p>

        <?php if ($error != "") : ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email Anda" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" name="login" class="btn">Login</button>
        </form>

        <p class="auth-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
    </div>
</div>

</body>
</html>