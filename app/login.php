<?php
include "config.php";

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error   = "";
$warning = "";

if (isset($_POST['login'])) {
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];

    // Query langsung ke tabel users
    $query = mysqli_query($conn,
        "SELECT * FROM users WHERE email = '$email' LIMIT 1"
    );

    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {
            // Cek status verifikasi email
            if ($user['status'] === 'nonaktif') {
                // Catat percobaan login gagal (belum verifikasi)
                mysqli_query($conn,
                    "INSERT INTO login (id_user, status) VALUES ({$user['id_user']}, 'gagal')"
                );
                $warning = "Akun Anda belum diverifikasi. Silakan cek email <strong>" . htmlspecialchars($email) . "</strong> dan klik tautan verifikasi.";
            } else {
                // Catat login berhasil ke tabel login (log)
                mysqli_query($conn,
                    "INSERT INTO login (id_user, status) VALUES ({$user['id_user']}, 'berhasil')"
                );

                // Set session
                $_SESSION['login']   = true;
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                header("Location: dashboard.php");
                exit;
            }
        } else {
            // Catat login gagal (password salah)
            mysqli_query($conn,
                "INSERT INTO login (id_user, status) VALUES ({$user['id_user']}, 'gagal')"
            );
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

        <?php if ($warning != "") : ?>
            <div class="alert warning"><?= $warning; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email Anda" required
                       value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
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