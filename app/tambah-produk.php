<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$error = "";
$success = "";

if (isset($_POST['tambah'])) {
    $nama_produk = trim($_POST['nama_produk'] ?? "");
    $harga = trim($_POST['harga'] ?? "");
    $stok = trim($_POST['jumlah_stok'] ?? "");

    if ($nama_produk === "" || $harga === "" || $stok === "") {
        $error = "Semua field wajib diisi.";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $error = "Harga harus numerik dan tidak boleh minus.";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = "Stok harus numerik dan tidak boleh minus.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO produk (nama_produk, harga, jumlah_stok) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sdi", $nama_produk, $harga, $stok);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Produk berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan produk.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Toko ATK</title>
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
        <li><a href="produk.php" class="active">Data Barang</a></li>
        <li><a href="users.php">Kelola User</a></li>
        <li><a href="laporan.php">Laporan Penjualan</a></li>
        <li><a href="setting-toko.php">Setting Toko</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Tambah Produk</h1>
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
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Harga (Rp)</label>
                <input type="number" name="harga" class="form-control" min="0" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Jumlah Stok</label>
                <input type="number" name="jumlah_stok" class="form-control" min="0" required>
            </div>
            <button type="submit" name="tambah" class="btn btn-primary w-auto">Simpan Produk</button>
            <a href="produk.php" class="btn btn-secondary w-auto">Batal</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
