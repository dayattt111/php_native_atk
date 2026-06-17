<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

$nama_session = $_SESSION['nama'];
$id_user = $_SESSION['id_user'];
$error = "";
$success = "";

// Aksi Update Profil
if (isset($_POST['update_profil'])) {
    $nama_baru = trim($_POST['nama']);
    $email_baru = trim($_POST['email']);

    if ($nama_baru === "" || $email_baru === "") {
        $error = "Nama dan Email wajib diisi.";
    } elseif (!filter_var($email_baru, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek email duplikat
        $stmt_cek = mysqli_prepare($conn, "SELECT id_user FROM users WHERE email = ? AND id_user != ?");
        mysqli_stmt_bind_param($stmt_cek, "si", $email_baru, $id_user);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        
        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Email sudah digunakan oleh akun lain.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, email = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $nama_baru, $email_baru, $id_user);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['nama'] = $nama_baru;
                $_SESSION['email'] = $email_baru;
                $success = "Profil berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui profil.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt_cek);
    }
}

// Aksi Ubah Password
if (isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    // Ambil data user saat ini untuk verifikasi password lama
    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!password_verify($password_lama, $user['password'])) {
        $error = "Password lama tidak sesuai.";
    } elseif ($password_baru === "" || strlen($password_baru) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } elseif ($password_baru !== $konfirmasi) {
        $error = "Konfirmasi password baru tidak cocok.";
    } else {
        $hash_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt_pass = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_pass, "si", $hash_password, $id_user);
        if (mysqli_stmt_execute($stmt_pass)) {
            $success = "Password berhasil diubah.";
        } else {
            $error = "Gagal mengubah password.";
        }
        mysqli_stmt_close($stmt_pass);
    }
}

// Fetch current user data
$stmt = mysqli_prepare($conn, "SELECT nama, email FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Toko ATK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .sidebar ul li a { text-decoration: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <h4 style="color: #51cf66; margin: 10px 0;">Pelanggan</h4>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="belanja.php">Belanja</a></li>
        <li><a href="keranjang.php">Keranjang Saya</a></li>
        <li><a href="riwayat.php">Riwayat Pesanan</a></li>
        <li><a href="profile.php" class="active">Profil</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Profil Saya</h1>
        <p>Halo, <?= htmlspecialchars($_SESSION['nama']); ?> (Pelanggan)</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" style="max-width: 800px;">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" style="max-width: 800px;">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row" style="max-width: 800px;">
        <div class="col-md-6 mb-4">
            <div class="card p-4 shadow-sm border-0 h-100">
                <h4 class="mb-4">Informasi Profil</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user_data['nama']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                    </div>
                    <button type="submit" name="update_profil" class="btn btn-primary w-100">Simpan Profil</button>
                </form>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card p-4 shadow-sm border-0 h-100">
                <h4 class="mb-4">Ubah Password</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" class="form-control" required>
                    </div>
                    <button type="submit" name="ubah_password" class="btn btn-warning w-100 text-dark fw-bold">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
