<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];

// Search Logic
$search = $_GET['search'] ?? '';
$where = "";
if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $where = "WHERE nama_produk LIKE '%$search%'";
}

// Get Total Produk & Stok
$queryTotal = mysqli_query($conn, "SELECT COUNT(*) as total_produk, SUM(jumlah_stok) as total_stok FROM produk $where");
$dataTotal = mysqli_fetch_assoc($queryTotal);
$total_produk = $dataTotal['total_produk'] ?? 0;
$total_stok = $dataTotal['total_stok'] ?? 0;

// Get Produk List
$query = "SELECT * FROM produk $where ORDER BY id_produk DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Toko ATK</title>
    <!-- Bootstrap CSS -->
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
        <h1>Data Barang</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3 shadow-sm border-0">
                <h5>Total Produk</h5>
                <h3 class="text-primary"><?= number_format($total_produk) ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 shadow-sm border-0">
                <h5>Total Stok</h5>
                <h3 class="text-success"><?= number_format($total_stok) ?></h3>
            </div>
        </div>
    </div>

    <div class="card p-4 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <a href="tambah-produk.php" class="btn btn-primary w-auto mb-2">+ Tambah Produk</a>
            <form method="GET" class="d-flex mb-2">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary w-auto">Cari</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><?= number_format($row['jumlah_stok']) ?></td>
                        <td>
                            <a href="edit-produk.php?id=<?= $row['id_produk'] ?>" class="btn btn-warning btn-sm w-auto">Edit</a>
                            <a href="hapus-produk.php?id=<?= $row['id_produk'] ?>" class="btn btn-danger btn-sm w-auto" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data produk.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
