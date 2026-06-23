<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama = $_SESSION['nama'];
$error = "";
$success = "";

if (!isset($_GET['id'])) {
    header("Location: riwayat.php");
    exit;
}

$id_transaksi = (int)$_GET['id'];

// Validasi kepemilikan transaksi dan status
$stmt_cek = mysqli_prepare($conn, "SELECT status, metode_pembayaran FROM transaksi WHERE id_transaksi = ? AND id_user = ?");
mysqli_stmt_bind_param($stmt_cek, "ii", $id_transaksi, $id_user);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);
$transaksi = mysqli_fetch_assoc($res_cek);
mysqli_stmt_close($stmt_cek);

if (!$transaksi) {
    header("Location: riwayat.php");
    exit;
}
if ($transaksi['status'] !== 'Menunggu Pembayaran' || !in_array($transaksi['metode_pembayaran'], ['transfer', 'qris'])) {
    header("Location: riwayat.php");
    exit;
}

// Proses Upload
if (isset($_POST['upload'])) {
    $file = $_FILES['bukti_transfer'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if ($file_error === 4) {
        $error = "Pilih file bukti transfer terlebih dahulu.";
    } elseif (!in_array($file_ext, $allowed_ext)) {
        $error = "Ekstensi file tidak diizinkan. Hanya boleh jpg, jpeg, png.";
    } elseif ($file_size > 2000000) { // 2MB
        $error = "Ukuran file terlalu besar. Maksimal 2MB.";
    } else {
        // Buat folder jika belum ada
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        $new_file_name = uniqid('bukti_') . '.' . $file_ext;
        $destination = 'uploads/' . $new_file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            // Update database
            $stmt = mysqli_prepare($conn, "UPDATE transaksi SET bukti_transfer = ?, status = 'Menunggu Verifikasi' WHERE id_transaksi = ?");
            mysqli_stmt_bind_param($stmt, "si", $new_file_name, $id_transaksi);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['sukses_checkout'] = "Bukti transfer berhasil diunggah. Menunggu verifikasi admin.";
                header("Location: riwayat.php");
                exit;
            } else {
                $error = "Gagal mengupdate database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Gagal mengunggah file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Bukti Pembayaran - Toko ATK</title>
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="style/app.css">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
                <li><a href="riwayat.php" class="active"><i data-lucide="history"></i> Riwayat Pesanan</a></li>
                <li><a href="profile.php"><i data-lucide="user"></i> Profil</a></li>
                <li><a href="logout.php" class="logout-nav"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- CONTAINER -->
    <div class="container">
        <!-- TOPBAR -->
        <div class="topbar" style="margin-bottom: 24px;">
            <div class="topbar-title">
                <h1>Unggah Bukti Pembayaran</h1>
                <p style="color: var(--text-secondary); margin-top: 4px;">Unggah foto/gambar struk transfer bank atau QRIS untuk transaksi Anda.</p>
            </div>
            <div class="topbar-info">
                <div class="topbar-avatar" style="box-shadow: 0 0 0 2px var(--primary);">
                    <?= strtoupper(substr($nama, 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- FORM CONTAINER -->
        <div class="form-container" style="max-width: 550px; margin: 0;">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);">
                Pesanan TRX-<?= str_pad($id_transaksi, 5, '0', STR_PAD_LEFT) ?>
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">Harap unggah bukti transfer/QRIS dalam format JPG, JPEG, atau PNG dengan ukuran file maksimal 2MB.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Pilih File Bukti Struk Transfer / QRIS</label>
                    <input class="form-control" type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png" required style="padding: 12px;">
                </div>
                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" name="upload" class="btn btn-primary" style="flex: 1;"><i data-lucide="upload-cloud" style="width: 16px; height: 16px;"></i> Unggah Sekarang</button>
                    <a href="riwayat.php" class="btn btn-secondary"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Batal</a>
                </div>
            </form>
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
