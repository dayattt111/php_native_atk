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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .sidebar ul li a { text-decoration: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <h4 style="color: #ff6b6b; margin: 10px 0;">Admin Dashboard</h4>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produk.php">Data Barang</a></li>
        <li><a href="users.php">Kelola User</a></li>
        <li><a href="laporan.php">Laporan Penjualan</a></li>
        <li><a href="setting-toko.php" class="active">Setting Toko</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Setting Toko</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <div class="card p-4 shadow-sm border-0" style="max-width: 600px;">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Toko</label>
                <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($setting['nama_toko'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Toko</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($setting['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($setting['telepon'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($setting['alamat'] ?? '') ?></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($setting['deskripsi'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" name="simpan" class="btn btn-primary w-auto">Simpan Pengaturan</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
