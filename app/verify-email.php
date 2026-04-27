<?php
include "config.php";

$message = "";
$type    = "error";

if (!empty($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    // Cari user dengan token ini yang belum diverifikasi
    $query = mysqli_query($conn,
        "SELECT id_user, nama, email
         FROM users
         WHERE verification_token = '$token'
           AND status = 'nonaktif'
           AND email_verified_at IS NULL
         LIMIT 1"
    );

    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        // Aktifkan akun: set status aktif, catat waktu, hapus token
        $update = mysqli_query($conn,
            "UPDATE users
             SET status = 'aktif',
                 email_verified_at = NOW(),
                 verification_token = NULL
             WHERE id_user = {$user['id_user']}"
        );

        if ($update) {
            $type    = "success";
            $message = "Email Anda berhasil diverifikasi! Akun <strong>" . htmlspecialchars($user['email']) . "</strong> kini aktif.";
        } else {
            $message = "Terjadi kesalahan saat mengaktifkan akun. Coba lagi nanti.";
        }
    } else {
        $message = "Tautan verifikasi tidak valid atau sudah digunakan.";
    }
} else {
    $message = "Token verifikasi tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .verify-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .verify-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,.15);
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
            text-align: center;
        }
        .verify-icon { font-size: 64px; margin-bottom: 16px; display: block; }
        .verify-card h2 { margin: 0 0 12px; font-size: 24px; color: #2d2d2d; }
        .verify-card p { color: #555; line-height: 1.7; margin-bottom: 28px; }
        .btn-verify {
            display: inline-block;
            padding: 13px 32px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: opacity .2s;
        }
        .btn-verify:hover { opacity: .88; }
        .btn-secondary { background: #f0f0f0; color: #555; margin-left: 10px; }
        .btn-secondary:hover { background: #e0e0e0; }
    </style>
</head>
<body>

<div class="verify-container">
    <div class="verify-card">

        <?php if ($type === "success") : ?>
            <span class="verify-icon">✅</span>
            <h2>Verifikasi Berhasil!</h2>
            <p><?= $message; ?></p>
            <a href="login.php" class="btn-verify">Masuk ke Dashboard →</a>

        <?php else : ?>
            <span class="verify-icon">❌</span>
            <h2>Verifikasi Gagal</h2>
            <p><?= htmlspecialchars($message); ?></p>
            <a href="login.php" class="btn-verify">Ke Halaman Login</a>
            <a href="register.php" class="btn-verify btn-secondary">Daftar Ulang</a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
