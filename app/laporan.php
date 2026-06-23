<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$filter = $_GET['filter'] ?? '';

$where_date = "";
if ($filter === 'hari_ini') {
    $where_date = "DATE(t.tanggal) = CURDATE()";
} elseif ($filter === 'minggu_ini') {
    $where_date = "YEARWEEK(t.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'bulan_ini') {
    $where_date = "MONTH(t.tanggal) = MONTH(CURDATE()) AND YEAR(t.tanggal) = YEAR(CURDATE())";
}

$where_clause = $where_date ? "WHERE $where_date" : "";

// Rekapitulasi
$q_rekap = mysqli_query($conn, "
    SELECT 
        COUNT(t.id_transaksi) as total_transaksi,
        SUM(t.total) as total_penjualan,
        COALESCE(SUM((SELECT SUM(dt.jumlah) FROM detail_transaksi dt WHERE dt.id_transaksi = t.id_transaksi)), 0) as produk_terjual
    FROM transaksi t $where_clause
");
$rekap = mysqli_fetch_assoc($q_rekap);

// Data Transaksi
$query_transaksi = "
    SELECT t.*, u.nama as nama_pelanggan
    FROM transaksi t
    JOIN users u ON t.id_user = u.id_user
    $where_clause
    ORDER BY t.tanggal DESC
";
$result_transaksi = mysqli_query($conn, $query_transaksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Toko ATK</title>
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
            <li><a href="laporan.php" class="active"><i data-lucide="file-bar-chart"></i> Laporan Penjualan</a></li>
            <li><a href="setting-toko.php"><i data-lucide="settings"></i> Setting Toko</a></li>
            <li><a href="logout.php" class="logout"><i data-lucide="log-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">
                <h1>Laporan Penjualan</h1>
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

        <!-- FILTER BAR -->
        <div class="table-section" style="margin-bottom: 24px; padding: 20px;">
            <form method="GET" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <label style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">Periode Laporan:</label>
                <select name="filter" class="form-control" style="width: auto; min-width: 180px;">
                    <option value="">Semua Waktu</option>
                    <option value="hari_ini" <?= $filter === 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                    <option value="minggu_ini" <?= $filter === 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini</option>
                    <option value="bulan_ini" <?= $filter === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                </select>
                <button type="submit" class="btn btn-primary"><i data-lucide="sliders" style="width: 16px; height: 16px;"></i> Tampilkan</button>
                <a href="cetak-laporan.php?filter=<?= urlencode($filter) ?>" target="_blank" class="btn btn-secondary" style="background-color: #dc3545; border-color: #dc3545; color: white; display: inline-flex; align-items: center; gap: 8px;"><i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Cetak PDF</a>
            </form>
        </div>

        <!-- REKAP CARDS -->
        <div class="cards">
            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Omset Penjualan</h3>
                    <div class="card-icon" style="background: var(--success-light); color: var(--success);"><i data-lucide="dollar-sign"></i></div>
                </div>
                <p>Rp <?= number_format($rekap['total_penjualan'] ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Transaksi</h3>
                    <div class="card-icon"><i data-lucide="shopping-bag"></i></div>
                </div>
                <p><?= number_format($rekap['total_transaksi'] ?? 0) ?></p>
            </div>
            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Produk Terjual</h3>
                    <div class="card-icon" style="background: var(--info-light); color: var(--info-text);"><i data-lucide="package-check"></i></div>
                </div>
                <p><?= number_format($rekap['produk_terjual'] ?? 0) ?> pcs</p>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="table-section">
            <div class="table-header-flex">
                <h2>Rincian Riwayat Transaksi</h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaksi</th>
                            <th>Tanggal & Waktu</th>
                            <th>Nama Pelanggan</th>
                            <th>Metode</th>
                            <th>Total Pembayaran</th>
                            <th>Item Pembelian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($result_transaksi)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge badge-info">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></span></td>
                            <td><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                            <td>
                                <?php if ($row['metode_pembayaran'] === 'transfer'): ?>
                                    <span class="badge badge-warning">TRANSFER</span>
                                <?php else: ?>
                                    <span class="badge badge-success">CASH</span>
                                <?php endif; ?>
                            </td>
                            <td><strong>Rp <?= number_format($row['total'], 0, ',', '.') ?></strong></td>
                            <td>
                                <ul style="margin: 0; padding-left: 16px; font-size: 13px; color: var(--text-secondary); list-style-type: square;">
                                    <?php
                                    $id_trx = $row['id_transaksi'];
                                    $q_detail = mysqli_query($conn, "SELECT p.nama_produk, dt.jumlah, dt.subtotal FROM detail_transaksi dt JOIN produk p ON dt.id_produk = p.id_produk WHERE dt.id_transaksi = $id_trx");
                                    while($dt = mysqli_fetch_assoc($q_detail)):
                                    ?>
                                    <li><?= htmlspecialchars($dt['nama_produk']) ?> <span style="font-weight: 600;">(<?= $dt['jumlah'] ?>x)</span></li>
                                    <?php endwhile; ?>
                                </ul>
                            </td>
                        </tr>
                        <?php 
                        endwhile; 
                        if(mysqli_num_rows($result_transaksi) == 0): 
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">Tidak ada transaksi terdaftar untuk periode ini.</td>
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
