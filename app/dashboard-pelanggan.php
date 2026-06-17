<?php
$nama = $_SESSION['nama'];
$id_user = $_SESSION['id_user'];

// Fetch live user statistics
$q_aktif = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = $id_user AND status IN ('Menunggu Pembayaran', 'Menunggu Verifikasi')");
$r_aktif = mysqli_fetch_assoc($q_aktif);
$pesanan_aktif = $r_aktif['total'] ?? 0;

$q_total_trx = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = $id_user");
$r_total_trx = mysqli_fetch_assoc($q_total_trx);
$total_pembelian = $r_total_trx['total'] ?? 0;

$q_belanja = mysqli_query($conn, "SELECT SUM(total) AS total FROM transaksi WHERE id_user = $id_user AND status = 'Pembayaran Disetujui'");
$r_belanja = mysqli_fetch_assoc($q_belanja);
$total_belanja_raw = $r_belanja['total'] ?? 0;
$total_belanja = number_format($total_belanja_raw, 0, ',', '.');

// Calculate reward points (e.g. 10 points for every Rp 10.000 spent on approved transactions)
$poin_reward = floor($total_belanja_raw / 10000) * 10;

// Fetch latest orders
$q_orders = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_user = $id_user ORDER BY tanggal DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Toko ATK</title>
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
                <li><a href="dashboard.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                <li><a href="belanja.php"><i data-lucide="shopping-bag"></i> Belanja</a></li>
                <li><a href="keranjang.php"><i data-lucide="shopping-cart"></i> Keranjang Saya</a></li>
                <li><a href="riwayat.php"><i data-lucide="history"></i> Riwayat Pesanan</a></li>
                <li><a href="profile.php"><i data-lucide="user"></i> Profil</a></li>
                <li><a href="logout.php" class="logout-nav"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- CONTAINER -->
    <div class="container">
        <!-- WELCOME BANNER -->
        <div class="topbar" style="margin-bottom: 28px;">
            <div class="topbar-title">
                <h1 style="font-size: 24px;">Selamat Datang Kembali, <?= htmlspecialchars($nama); ?>!</h1>
                <p style="color: var(--text-secondary); margin-top: 4px;">Penuhi kebutuhan alat tulis kantor Anda dengan pelayanan terbaik kami.</p>
            </div>
            <div class="topbar-info">
                <div class="topbar-avatar" style="box-shadow: 0 0 0 2px var(--success);">
                    <?= strtoupper(substr($nama, 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="cards">
            <div class="card">
                <div class="card-header-flex">
                    <h3>Pesanan Aktif</h3>
                    <div class="card-icon" style="background: var(--warning-light); color: var(--warning);"><i data-lucide="clock"></i></div>
                </div>
                <p><?= number_format($pesanan_aktif); ?></p>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Pembelian</h3>
                    <div class="card-icon"><i data-lucide="shopping-cart"></i></div>
                </div>
                <p><?= number_format($total_pembelian); ?></p>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3>Poin Reward</h3>
                    <div class="card-icon" style="background: var(--primary-light); color: var(--primary);"><i data-lucide="gift"></i></div>
                </div>
                <p><?= number_format($poin_reward); ?></p>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3>Total Belanja Selesai</h3>
                    <div class="card-icon" style="background: var(--success-light); color: var(--success);"><i data-lucide="wallet"></i></div>
                </div>
                <p>Rp <?= $total_belanja; ?></p>
            </div>
        </div>

        <!-- RECENT ORDERS TABLE -->
        <div class="table-section">
            <div class="table-header-flex">
                <h2>Pesanan Terbaru Anda</h2>
                <a href="riwayat.php" class="btn btn-secondary btn-sm">Lihat Semua Pesanan</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Pesanan</th>
                            <th>Tanggal Transaksi</th>
                            <th>Total Pembayaran</th>
                            <th>Status Pesanan</th>
                            <th style="width: 150px;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($q_orders)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><span class="badge badge-info">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></span></td>
                            <td><?= date('d M Y, H:i', strtotime($row['tanggal'])); ?></td>
                            <td><strong>Rp <?= number_format($row['total'], 0, ',', '.'); ?></strong></td>
                            <td>
                                <?php
                                    $badge_class = 'badge-secondary';
                                    if ($row['status'] === 'Menunggu Pembayaran') $badge_class = 'badge-warning';
                                    elseif ($row['status'] === 'Menunggu Verifikasi') $badge_class = 'badge-info';
                                    elseif ($row['status'] === 'Pembayaran Disetujui') $badge_class = 'badge-success';
                                    elseif ($row['status'] === 'Ditolak') $badge_class = 'badge-danger';
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($row['status']) ?></span>
                            </td>
                            <td>
                                <a href="riwayat.php#trx-<?= $row['id_transaksi']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 10px;">
                                    <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i> Riwayat
                                </a>
                            </td>
                        </tr>
                        <?php 
                        endwhile; 
                        if(mysqli_num_rows($q_orders) == 0): 
                        ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">Anda belum pernah memesan produk.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
