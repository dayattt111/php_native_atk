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
        (SELECT SUM(jumlah) FROM detail_transaksi dt JOIN transaksi t2 ON dt.id_transaksi=t2.id_transaksi ".($where_date ? "WHERE DATE(t2.tanggal) = CURDATE()" : "").") as produk_terjual_hari_ini
    FROM transaksi t $where_clause
");
// We need to fix the subquery logic if we want "produk terjual" based on the same filter
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
        <li><a href="laporan.php" class="active">Laporan Penjualan</a></li>
        <li><a href="setting-toko.php">Setting Toko</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Laporan Penjualan</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <div class="card p-4 shadow-sm border-0 mb-4">
        <form method="GET" class="d-flex gap-2 mb-4">
            <select name="filter" class="form-select w-auto">
                <option value="">Semua Waktu</option>
                <option value="hari_ini" <?= $filter === 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                <option value="minggu_ini" <?= $filter === 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini</option>
                <option value="bulan_ini" <?= $filter === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
            </select>
            <button type="submit" class="btn btn-primary w-auto">Filter</button>
        </form>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white p-3 shadow-sm border-0">
                    <h5>Total Penjualan</h5>
                    <h3>Rp <?= number_format($rekap['total_penjualan'] ?? 0, 0, ',', '.') ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-success text-white p-3 shadow-sm border-0">
                    <h5>Total Transaksi</h5>
                    <h3><?= number_format($rekap['total_transaksi'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-info text-white p-3 shadow-sm border-0">
                    <h5>Produk Terjual</h5>
                    <h3><?= number_format($rekap['produk_terjual'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-4 shadow-sm border-0">
        <h4>Daftar Transaksi</h4>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Detail Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result_transaksi)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><span class="badge bg-secondary"><?= strtoupper($row['metode_pembayaran']) ?></span></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td>
                            <ul class="mb-0 ps-3">
                                <?php
                                $id_trx = $row['id_transaksi'];
                                $q_detail = mysqli_query($conn, "SELECT p.nama_produk, dt.jumlah, dt.subtotal FROM detail_transaksi dt JOIN produk p ON dt.id_produk = p.id_produk WHERE dt.id_transaksi = $id_trx");
                                while($dt = mysqli_fetch_assoc($q_detail)):
                                ?>
                                <li><?= htmlspecialchars($dt['nama_produk']) ?> (<?= $dt['jumlah'] ?>x)</li>
                                <?php endwhile; ?>
                            </ul>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result_transaksi) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data transaksi pada periode ini.</td>
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
