<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$error = "";
$success = "";

if (!isset($_GET['id'])) {
    header("Location: produk.php");
    exit;
}
$id_produk = (int)$_GET['id'];

// Fetch current data first
$stmt = mysqli_prepare($conn, "SELECT * FROM produk WHERE id_produk=?");
mysqli_stmt_bind_param($stmt, "i", $id_produk);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$produk) {
    header("Location: produk.php");
    exit;
}

if (isset($_POST['edit'])) {
    $nama_produk = trim($_POST['nama_produk'] ?? "");
    $harga = trim($_POST['harga'] ?? "");
    $stok = trim($_POST['jumlah_stok'] ?? "");
    $gambar_name = $produk['gambar'];

    if ($nama_produk === "" || $harga === "" || $stok === "") {
        $error = "Semua field wajib diisi.";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $error = "Harga harus numerik dan tidak boleh minus.";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = "Stok harus numerik dan tidak boleh minus.";
    } else {
        // Handle file upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== 4) {
            $file = $_FILES['gambar'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];

            $allowed_ext = ['jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_error !== 0) {
                $error = "Terjadi kesalahan saat mengunggah gambar.";
            } elseif (!in_array($file_ext, $allowed_ext)) {
                $error = "Ekstensi file gambar tidak diizinkan. Hanya boleh jpg, jpeg, png.";
            } elseif ($file_size > 2000000) { // 2MB
                $error = "Ukuran gambar terlalu besar. Maksimal 2MB.";
            } else {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $new_file_name = uniqid('produk_') . '.' . $file_ext;
                $destination = 'uploads/' . $new_file_name;
                if (move_uploaded_file($file_tmp, $destination)) {
                    // Delete old image if exists
                    if ($produk['gambar'] && file_exists('uploads/' . $produk['gambar'])) {
                        unlink('uploads/' . $produk['gambar']);
                    }
                    $gambar_name = $new_file_name;
                } else {
                    $error = "Gagal memindahkan file gambar ke direktori tujuan.";
                }
            }
        }

        if (empty($error)) {
            $stmt = mysqli_prepare($conn, "UPDATE produk SET nama_produk=?, harga=?, jumlah_stok=?, gambar=? WHERE id_produk=?");
            mysqli_stmt_bind_param($stmt, "sdisi", $nama_produk, $harga, $stok, $gambar_name, $id_produk);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Produk berhasil diperbarui!";
                // Refresh data
                $produk['nama_produk'] = $nama_produk;
                $produk['harga'] = $harga;
                $produk['jumlah_stok'] = $stok;
                $produk['gambar'] = $gambar_name;
            } else {
                $error = "Gagal memperbarui produk.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Toko ATK</title>
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
            <li><a href="produk.php" class="active"><i data-lucide="package"></i> Data Barang</a></li>
            <li><a href="users.php"><i data-lucide="users"></i> Kelola User</a></li>
            <li><a href="verifikasi.php"><i data-lucide="check-square"></i> Verifikasi Pembayaran</a></li>
            <li><a href="laporan.php"><i data-lucide="file-bar-chart"></i> Laporan Penjualan</a></li>
            <li><a href="setting-toko.php"><i data-lucide="settings"></i> Setting Toko</a></li>
            <li><a href="logout.php" class="logout"><i data-lucide="log-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">
                <h1>Edit Produk</h1>
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
        <div class="form-container" style="max-width: 600px; margin: 0;">
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

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" value="<?= htmlspecialchars($produk['harga']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Stok</label>
                    <input type="number" name="jumlah_stok" class="form-control" min="0" value="<?= htmlspecialchars($produk['jumlah_stok']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Gambar Produk</label>
                    <?php if ($produk['gambar']): ?>
                        <div style="margin-bottom: 12px;">
                            <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="Gambar Produk" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 1px solid var(--border); display: block; object-fit: cover;">
                            <span style="font-size: 12px; color: var(--text-secondary);">Gambar saat ini</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png" style="padding: 10px;">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Pilih file baru jika ingin mengganti gambar produk (format JPG/JPEG/PNG, maks 2MB).</small>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" name="edit" class="btn btn-primary"><i data-lucide="save" style="width: 16px; height: 16px;"></i> Simpan Perubahan</button>
                    <a href="produk.php" class="btn btn-secondary"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali</a>
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
