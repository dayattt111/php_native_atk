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
                <h1>Data Barang</h1>
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

        <!-- STATS CARDS -->
        <div class="cards">
            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Jenis Produk</h3>
                    <div class="card-icon"><i data-lucide="box"></i></div>
                </div>
                <p><?= number_format($total_produk); ?></p>
            </div>
            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Keseluruhan Stok</h3>
                    <div class="card-icon" style="background: var(--success-light); color: var(--success);"><i data-lucide="layers"></i></div>
                </div>
                <p><?= number_format($total_stok); ?></p>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="table-section">
            <div class="table-header-flex">
                <h2>Daftar Produk ATK</h2>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <form method="GET" style="display: flex; gap: 8px;">
                        <div class="search-input-wrapper">
                            <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                            <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($search); ?>" style="padding-left: 36px; min-width: 220px;">
                        </div>
                        <button type="submit" class="btn btn-secondary"><i data-lucide="filter" style="width: 16px; height: 16px;"></i> Cari</button>
                    </form>
                    <a href="tambah-produk.php" class="btn btn-primary"><i data-lucide="plus" style="width: 16px; height: 16px;"></i> Tambah Produk</a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah Stok</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_produk']); ?></strong></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($row['jumlah_stok'] < 10): ?>
                                    <span class="badge badge-danger"><?= number_format($row['jumlah_stok']); ?> (Kritis)</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= number_format($row['jumlah_stok']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit-produk.php?id=<?= $row['id_produk']; ?>" class="btn btn-secondary btn-sm"><i data-lucide="edit" style="width: 14px; height: 14px;"></i> Edit</a>
                                    <a href="hapus-produk.php?id=<?= $row['id_produk']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')"><i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        endwhile; 
                        if(mysqli_num_rows($result) == 0): 
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">Tidak ada data produk ditemukan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
