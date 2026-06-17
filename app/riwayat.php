<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$id_user = $_SESSION['id_user'];
$success = "";
if (isset($_SESSION['sukses_checkout'])) {
    $success = $_SESSION['sukses_checkout'];
    unset($_SESSION['sukses_checkout']);
}

// Ambil riwayat transaksi
$query_transaksi = "
    SELECT * FROM transaksi 
    WHERE id_user = $id_user 
    ORDER BY tanggal DESC
";
$result_transaksi = mysqli_query($conn, $query_transaksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko ATK</title>
    <!-- Bootstrap CSS for Modal & Grids -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="style/app.css">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Scoped overrides to resolve conflicts between Bootstrap and app.css */
        .pelanggan-layout a {
            text-decoration: none;
        }
        .modal-content {
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-xl);
        }
        .modal-header {
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
        }
        .modal-body {
            padding: 24px;
        }
        .list-group-item {
            border-color: var(--border);
        }
        /* Highlight specific row if anchored */
        tr:target {
            background-color: var(--primary-light) !important;
            animation: highlight-pulse 2s ease-out;
        }
        @keyframes highlight-pulse {
            0% { background-color: var(--primary-light); }
            100% { background-color: transparent; }
        }
    </style>
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
                <li><a href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                <li><a href="belanja.php"><i data-lucide="shopping-bag"></i> Belanja</a></li>
                <li><a href="keranjang.php"><i data-lucide="shopping-cart"></i> Keranjang Saya</a></li>
                <li><a href="riwayat.php" class="active"><i data-lucide="history"></i> Riwayat Pesanan</a></li>
                <li><a href="profile.php"><i data-lucide="user"></i> Profil</a></li>
                <li><a href="logout.php" class="logout-nav"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- CONTAINER -->
    <div class="container">
        <!-- TOPBAR -->
        <div class="topbar" style="margin-bottom: 24px;">
            <div class="topbar-title">
                <h1>Riwayat Pesanan Anda</h1>
                <p style="color: var(--text-secondary); margin-top: 4px;">Pantau status pembayaran dan pengiriman belanjaan Anda.</p>
            </div>
            <div class="topbar-info">
                <div class="topbar-avatar" style="box-shadow: 0 0 0 2px var(--primary);">
                    <?= strtoupper(substr($nama, 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- SUCCESS NOTIFICATION -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- TABLE SECTION -->
        <div class="table-section">
            <div class="table-header-flex">
                <h2>Daftar Riwayat Belanja</h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal Transaksi</th>
                            <th>Metode Pembayaran</th>
                            <th>Total Tagihan</th>
                            <th>Status Pesanan</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($result_transaksi)): 
                        ?>
                        <tr id="trx-<?= $row['id_transaksi'] ?>">
                            <td><?= $no++ ?></td>
                            <td><span class="badge badge-info">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></span></td>
                            <td><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></td>
                            <td>
                                <?php if ($row['metode_pembayaran'] === 'transfer'): ?>
                                    <span class="badge badge-warning">TRANSFER</span>
                                <?php else: ?>
                                    <span class="badge badge-success">CASH</span>
                                <?php endif; ?>
                            </td>
                            <td><strong>Rp <?= number_format($row['total'], 0, ',', '.') ?></strong></td>
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
                                <div class="btn-group">
                                    <?php if($row['status'] === 'Menunggu Pembayaran' && $row['metode_pembayaran'] === 'transfer'): ?>
                                        <a href="upload-bukti.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-primary btn-sm" style="background: var(--warning); border-color: var(--warning); color: var(--warning-text);">
                                            <i data-lucide="upload" style="width: 14px; height: 14px;"></i> Bukti
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id_transaksi'] ?>">
                                        <i data-lucide="list" style="width: 14px; height: 14px;"></i> Detail
                                    </button>
                                </div>

                                <!-- Modal Detail Transaksi -->
                                <div class="modal fade" id="modalDetail<?= $row['id_transaksi'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" style="font-weight: 700; color: var(--text-primary);">Detail Transaksi TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-group list-group-flush">
                                                    <?php
                                                    $id_trx = $row['id_transaksi'];
                                                    $q_detail = mysqli_query($conn, "
                                                        SELECT p.nama_produk, p.harga, dt.jumlah, dt.subtotal 
                                                        FROM detail_transaksi dt 
                                                        JOIN produk p ON dt.id_produk = p.id_produk 
                                                        WHERE dt.id_transaksi = $id_trx
                                                    ");
                                                    while($dt = mysqli_fetch_assoc($q_detail)):
                                                    ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <div>
                                                            <h6 class="mb-0" style="font-size: 14px; font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($dt['nama_produk']) ?></h6>
                                                            <small class="text-muted"><?= $dt['jumlah'] ?> pcs x Rp <?= number_format($dt['harga'], 0, ',', '.') ?></small>
                                                        </div>
                                                        <span style="font-size: 14px; font-weight: 600; color: var(--text-primary);">Rp <?= number_format($dt['subtotal'], 0, ',', '.') ?></span>
                                                    </li>
                                                    <?php endwhile; ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 mt-3" style="border-top: 1px solid var(--border); padding-top: 16px;">
                                                        <span style="font-weight: 700; font-size: 15px;">TOTAL BAYAR:</span>
                                                        <span style="font-weight: 700; font-size: 16px; color: var(--primary);">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="modal-footer" style="border-top: 1px solid var(--border); padding: 12px 24px;">
                                                <button type="button" class="btn btn-secondary w-auto" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result_transaksi) == 0): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">Anda belum memiliki riwayat pesanan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
