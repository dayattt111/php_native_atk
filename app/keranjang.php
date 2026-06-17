<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$id_user = $_SESSION['id_user'];
$error = "";
$success = "";

// Aksi Update Keranjang
if (isset($_POST['update_keranjang'])) {
    $id_keranjang = (int)$_POST['id_keranjang'];
    $jumlah_baru = (int)$_POST['jumlah'];

    // Cek stok produk yang berelasi
    $stmt = mysqli_prepare($conn, "SELECT p.jumlah_stok FROM keranjang k JOIN produk p ON k.id_produk = p.id_produk WHERE k.id_keranjang = ? AND k.id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_keranjang, $id_user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data_stok = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($data_stok) {
        if ($jumlah_baru <= 0) {
            // Hapus jika 0
            mysqli_query($conn, "DELETE FROM keranjang WHERE id_keranjang = $id_keranjang AND id_user = $id_user");
            $success = "Item dihapus dari keranjang.";
        } elseif ($jumlah_baru > $data_stok['jumlah_stok']) {
            $error = "Jumlah melebih stok yang tersedia (Maksimal: {$data_stok['jumlah_stok']}).";
        } else {
            // Update jumlah
            $stmt = mysqli_prepare($conn, "UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ? AND id_user = ?");
            mysqli_stmt_bind_param($stmt, "iii", $jumlah_baru, $id_keranjang, $id_user);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Keranjang diperbarui.";
            } else {
                $error = "Gagal memperbarui keranjang.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Aksi Hapus Item
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_hapus, $id_user);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: keranjang.php");
        exit;
    }
    mysqli_stmt_close($stmt);
}

// Ambil isi keranjang
$query_keranjang = "
    SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.jumlah_stok
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id_produk
    WHERE k.id_user = $id_user
";
$result_keranjang = mysqli_query($conn, $query_keranjang);
$total_belanja = 0;
$items = [];
while ($row = mysqli_fetch_assoc($result_keranjang)) {
    $items[] = $row;
    $total_belanja += ($row['harga'] * $row['jumlah']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Saya - Toko ATK</title>
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
                <li><a href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                <li><a href="belanja.php"><i data-lucide="shopping-bag"></i> Belanja</a></li>
                <li><a href="keranjang.php" class="active"><i data-lucide="shopping-cart"></i> Keranjang Saya</a></li>
                <li><a href="riwayat.php"><i data-lucide="history"></i> Riwayat Pesanan</a></li>
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
                <h1>Keranjang Belanja</h1>
                <p style="color: var(--text-secondary); margin-top: 4px;">Kelola barang yang ingin Anda beli sebelum melakukan checkout.</p>
            </div>
            <div class="topbar-info">
                <div class="topbar-avatar" style="box-shadow: 0 0 0 2px var(--primary);">
                    <?= strtoupper(substr($nama, 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- ALERTS -->
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

        <!-- CART LAYOUT -->
        <div class="cart-layout">
            <!-- CART LIST -->
            <div class="table-section" style="padding: 20px;">
                <h2 style="margin-bottom: 16px;">Daftar Item Belanja</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Produk</th>
                                <th>Harga Satuan</th>
                                <th style="width: 220px;">Jumlah Pesanan</th>
                                <th>Subtotal</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['nama_produk']) ?></strong>
                                    <?php if($item['jumlah'] > $item['jumlah_stok']): ?>
                                        <span class="badge badge-danger" style="margin-top: 4px; display: inline-block;">Stok tidak cukup</span>
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                        <input type="hidden" name="id_keranjang" value="<?= $item['id_keranjang'] ?>">
                                        <input type="number" name="jumlah" class="form-control" value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['jumlah_stok'] ?>" style="width: 80px; padding: 6px; text-align: center;">
                                        <button type="submit" name="update_keranjang" class="btn btn-secondary btn-sm" style="padding: 6px 12px;"><i data-lucide="refresh-cw" style="width: 12px; height: 12px;"></i> Update</button>
                                    </form>
                                </td>
                                <td><strong style="color: var(--text-primary);">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <a href="keranjang.php?hapus=<?= $item['id_keranjang'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item ini?')">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($items) === 0): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                    Keranjang belanja Anda kosong. <a href="belanja.php" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Mulai belanja sekarang!</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CART SUMMARY -->
            <div class="cart-summary">
                <h3>Ringkasan Transaksi</h3>
                <div class="cart-summary-row">
                    <span>Total Jenis Produk:</span>
                    <strong><?= count($items) ?> item</strong>
                </div>
                <div class="cart-summary-row">
                    <span>Biaya Pengiriman:</span>
                    <strong style="color: var(--success-text);">GRATIS</strong>
                </div>
                <div class="cart-summary-row total">
                    <span>Total Tagihan:</span>
                    <span>Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
                </div>
                
                <?php if(count($items) > 0): ?>
                <form action="checkout.php" method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Pilih Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-control" required>
                            <option value="cash">Tunai di Toko (COD / Cash)</option>
                            <option value="transfer">Transfer Bank Mandiri / BCA</option>
                        </select>
                    </div>
                    <button type="submit" name="checkout" class="btn btn-primary w-100" style="padding: 12px;" onclick="return confirm('Apakah Anda yakin ingin checkout pesanan ini?')">
                        <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i> Lanjutkan Checkout
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary w-100" style="padding: 12px; cursor: not-allowed;" disabled>
                    <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i> Lanjutkan Checkout
                </button>
                <?php endif; ?>
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
