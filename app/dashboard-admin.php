<?php
$nama = $_SESSION['nama'];

// Fetch live statistics
$q_barang = mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk");
$r_barang = mysqli_fetch_assoc($q_barang);
$total_barang = $r_barang['total'] ?? 0;

$q_trx = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transaksi WHERE DATE(tanggal) = CURDATE()");
$r_trx = mysqli_fetch_assoc($q_trx);
$total_trx = $r_trx['total'] ?? 0;

$q_stok = mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk WHERE jumlah_stok < 10");
$r_stok = mysqli_fetch_assoc($q_stok);
$total_stok = $r_stok['total'] ?? 0;

$q_sales = mysqli_query($conn, "SELECT SUM(total) AS total FROM transaksi WHERE status = 'Pembayaran Disetujui'");
$r_sales = mysqli_fetch_assoc($q_sales);
$total_sales = number_format($r_sales['total'] ?? 0, 0, ',', '.');

// Fetch latest products
$q_terbaru = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Toko ATK</title>
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
            <li><a href="dashboard.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
            <li><a href="produk.php"><i data-lucide="package"></i> Data Barang</a></li>
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
                <h1>Dashboard Admin</h1>
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
                    <h3>Total Barang</h3>
                    <div class="card-icon"><i data-lucide="package"></i></div>
                </div>
                <p><?= number_format($total_barang); ?></p>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3>Transaksi Hari Ini</h3>
                    <div class="card-icon" style="background: var(--success-light); color: var(--success);"><i data-lucide="shopping-cart"></i></div>
                </div>
                <p><?= number_format($total_trx); ?></p>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3>Stok Menipis</h3>
                    <div class="card-icon" style="background: var(--danger-light); color: var(--danger);"><i data-lucide="alert-triangle"></i></div>
                </div>
                <p><?= number_format($total_stok); ?></p>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Penjualan</h3>
                    <div class="card-icon" style="background: var(--warning-light); color: var(--warning);"><i data-lucide="dollar-sign"></i></div>
                </div>
                <p>Rp <?= $total_sales; ?></p>
            </div>
        </div>

        <!-- LATEST PRODUCTS TABLE -->
        <div class="table-section">
            <div class="table-header-flex">
                <h2>Barang ATK Terbaru</h2>
                <a href="produk.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($q_terbaru)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_produk']); ?></strong></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($row['jumlah_stok'] < 10): ?>
                                    <span class="badge badge-danger"><?= number_format($row['jumlah_stok']); ?> (Minim)</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= number_format($row['jumlah_stok']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit-produk.php?id=<?= $row['id_produk']; ?>" class="btn btn-secondary btn-sm"><i data-lucide="edit-2" style="width: 14px; height: 14px;"></i> Edit</a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        endwhile; 
                        if(mysqli_num_rows($q_terbaru) == 0): 
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada data barang.</td>
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
