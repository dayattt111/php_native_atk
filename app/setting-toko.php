<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$error = "";
$success = "";

if (isset($_POST['simpan'])) {
    $nama_toko = trim($_POST['nama_toko'] ?? "");
    $alamat = trim($_POST['alamat'] ?? "");
    $telepon = trim($_POST['telepon'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $deskripsi = trim($_POST['deskripsi'] ?? "");

    if ($nama_toko === "" || $alamat === "" || $telepon === "" || $email === "") {
        $error = "Semua field (kecuali deskripsi) wajib diisi.";
    } else {
        // Cek apakah data setting sudah ada
        $cek = mysqli_query($conn, "SELECT * FROM setting_toko LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            // Update
            $row_setting = mysqli_fetch_assoc($cek);
            $id_setting = $row_setting['id_setting'];
            $stmt = mysqli_prepare($conn, "UPDATE setting_toko SET nama_toko=?, alamat=?, telepon=?, email=?, deskripsi=? WHERE id_setting=?");
            mysqli_stmt_bind_param($stmt, "sssssi", $nama_toko, $alamat, $telepon, $email, $deskripsi, $id_setting);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Pengaturan toko berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui pengaturan toko.";
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert
            $stmt = mysqli_prepare($conn, "INSERT INTO setting_toko (nama_toko, alamat, telepon, email, deskripsi) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $nama_toko, $alamat, $telepon, $email, $deskripsi);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Pengaturan toko berhasil disimpan!";
            } else {
                $error = "Gagal menyimpan pengaturan toko.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch current data
$query = mysqli_query($conn, "SELECT * FROM setting_toko LIMIT 1");
$setting = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Toko - Toko ATK</title>
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="style/app.css">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="admin-layout">
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">A</div>
            <div>
                <span class="sidebar-logo-text">ATK Berkah</span>
                <span class="sidebar-logo-sub">Admin Panel</span>
            </div>
        </div>
        <ul>
            <li><a href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
            <li><a href="produk.php"><i data-lucide="package"></i> Data Barang</a></li>
            <li><a href="users.php"><i data-lucide="users"></i> Kelola User</a></li>
            <li><a href="verifikasi.php"><i data-lucide="check-square"></i> Verifikasi Pembayaran</a></li>
            <li><a href="laporan.php"><i data-lucide="file-bar-chart"></i> Laporan Penjualan</a></li>
            <li><a href="setting-toko.php" class="active"><i data-lucide="settings"></i> Setting Toko</a></li>
            <li><a href="logout.php" class="logout"><i data-lucide="log-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">
                <h1>Setting Toko</h1>
            </div>
            <div class="topbar-info">
                <div class="topbar-user">
                    <span class="topbar-user-name"><?= htmlspecialchars($nama); ?></span>
                    <span class="topbar-user-role">Administrator</span>
                </div>
                <div class="topbar-avatar">
                    <?= strtoupper(substr($nama, 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- FORM CONTAINER -->
        <div class="form-container" style="max-width: 650px; margin: 0;">
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

            <form method="POST">
                <div class="form-group">
                    <label>Nama Toko</label>
                    <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($setting['nama_toko'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Toko</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($setting['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($setting['telepon'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($setting['alamat'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Deskripsi Singkat Toko</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($setting['deskripsi'] ?? '') ?></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" name="simpan" class="btn btn-primary"><i data-lucide="save" style="width: 16px; height: 16px;"></i> Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i data-lucide="menu"></i>
</button>

<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Toggle sidebar visibility on mobile
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }
</script>
</body>
</html>
