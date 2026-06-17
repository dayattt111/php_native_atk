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
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="style/app.css">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            align-items: start;
        }
        @media screen and (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="pelanggan-layout">
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">
                <span>ATK</span> Berkah
            </a>
            <button class="navbar-toggle" id="navbarToggle" onclick="toggleNavbar()">
                <i data-lucide="menu"></i>
            </button>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                <li><a href="belanja.php"><i data-lucide="shopping-bag"></i> Belanja</a></li>
                <li><a href="keranjang.php"><i data-lucide="shopping-cart"></i> Keranjang Saya</a></li>
                <li><a href="riwayat.php"><i data-lucide="history"></i> Riwayat Pesanan</a></li>
                <li><a href="profile.php" class="active"><i data-lucide="user"></i> Profil</a></li>
                <li><a href="logout.php" class="logout-nav"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- CONTAINER -->
    <div class="container">
        <!-- TOPBAR -->
        <div class="topbar" style="margin-bottom: 24px;">
            <div class="topbar-title">
                <h1>Profil & Pengaturan Akun</h1>
                <p style="color: var(--text-secondary); margin-top: 4px;">Kelola informasi profil pribadi dan keamanan password Anda.</p>
            </div>
            <div class="topbar-info">
                <div class="topbar-avatar" style="box-shadow: 0 0 0 2px var(--primary);">
                    <?= strtoupper(substr($nama_session, 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- PROFILE GRID -->
        <div class="profile-grid">
            <!-- EDIT PROFILE -->
            <div class="form-container" style="max-width: 100%; margin: 0;">
                <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 24px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="user-check" style="width: 20px; height: 20px; color: var(--primary);"></i> Informasi Profil
                </h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user_data['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                    </div>
                    <button type="submit" name="update_profil" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                        <i data-lucide="save" style="width: 16px; height: 16px;"></i> Simpan Profil
                    </button>
                </form>
            </div>

            <!-- CHANGE PASSWORD -->
            <div class="form-container" style="max-width: 100%; margin: 0;">
                <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 24px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 20px; height: 20px; color: var(--warning);"></i> Perbarui Password
                </h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password saat ini" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" required>
                    </div>
                    <button type="submit" name="ubah_password" class="btn btn-primary" style="width: 100%; margin-top: 16px; background: var(--warning); border-color: var(--warning); color: var(--warning-text);">
                        <i data-lucide="key" style="width: 16px; height: 16px;"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Toggle horizontal navbar on mobile
    function toggleNavbar() {
        document.getElementById('navbarMenu').classList.toggle('open');
    }
</script>
</body>
</html>
