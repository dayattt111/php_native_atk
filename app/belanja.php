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

if (isset($_POST['tambah_keranjang'])) {
    $id_produk = (int)$_POST['id_produk'];
    $jumlah = (int)$_POST['jumlah'];

    // Cek stok produk
    $q_produk = mysqli_query($conn, "SELECT jumlah_stok FROM produk WHERE id_produk = $id_produk");
    $produk = mysqli_fetch_assoc($q_produk);

    if (!$produk) {
        $error = "Produk tidak ditemukan.";
    } elseif ($jumlah <= 0) {
        $error = "Jumlah harus lebih dari 0.";
    } else {
        // Cek jumlah di keranjang saat ini
        $q_keranjang = mysqli_query($conn, "SELECT jumlah FROM keranjang WHERE id_user = $id_user AND id_produk = $id_produk");
        $keranjang = mysqli_fetch_assoc($q_keranjang);
        $jumlah_sekarang = $keranjang ? $keranjang['jumlah'] : 0;
        
        if ($jumlah_sekarang + $jumlah > $produk['jumlah_stok']) {
            $error = "Maaf, jumlah total di keranjang melebihi stok yang tersedia.";
        } else {
            // Insert or Update keranjang
            $stmt = mysqli_prepare($conn, "
                INSERT INTO keranjang (id_user, id_produk, jumlah) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE jumlah = jumlah + ?
            ");
            mysqli_stmt_bind_param($stmt, "iiii", $id_user, $id_produk, $jumlah, $jumlah);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Produk berhasil ditambahkan ke keranjang.";
            } else {
                $error = "Gagal menambahkan produk ke keranjang.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Ambil data produk
$search = $_GET['search'] ?? '';
$where = "WHERE jumlah_stok > 0";
if ($search) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where .= " AND nama_produk LIKE '%$search_esc%'";
}
$query_produk = "SELECT * FROM produk $where ORDER BY id_produk DESC";
$result_produk = mysqli_query($conn, $query_produk);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja - Toko ATK</title>
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
                <li><a href="belanja.php" class="active"><i data-lucide="shopping-bag"></i> Belanja</a></li>
                <li><a href="keranjang.php"><i data-lucide="shopping-cart"></i> Keranjang Saya</a></li>
                <li><a href="riwayat.php"><i data-lucide="history"></i> Riwayat Pesanan</a></li>
                <li><a href="profile.php"><i data-lucide="user"></i> Profil</a></li>
                <li><a href="logout.php" class="logout-nav"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- CONTAINER -->
    <div class="container">
        <!-- HEADER & SEARCH -->
        <div class="topbar" style="margin-bottom: 24px; padding: 20px;">
            <div class="topbar-title">
                <h1 style="font-size: 22px;">Katalog Produk ATK</h1>
                <p style="color: var(--text-secondary); margin-top: 4px;">Pilih dan pesan barang kebutuhan Anda dengan mudah.</p>
            </div>
            <div style="min-width: 250px;">
                <form method="GET" style="display: flex; gap: 8px;">
                    <div class="search-input-wrapper">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                        <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 36px;">
                    </div>
                    <button type="submit" class="btn btn-secondary">Cari</button>
                </form>
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

        <!-- PRODUCT CATALOG GRID -->
        <div class="catalog-grid">
            <?php 
            while($row = mysqli_fetch_assoc($result_produk)): 
            ?>
            <div class="product-card">
                <div class="product-card-img" style="display: flex; align-items: center; justify-content: center; height: 160px; background: #fafafa; border-bottom: 1px solid var(--border); overflow: hidden; border-top-left-radius: var(--radius); border-top-right-radius: var(--radius);">
                    <?php if ($row['gambar'] && file_exists('uploads/' . $row['gambar'])): ?>
                        <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <?php else: ?>
                        <i data-lucide="image" style="width: 44px; height: 44px; color: var(--primary); stroke-width: 1.5;"></i>
                    <?php endif; ?>
                </div>
                <h3 class="product-card-title"><?= htmlspecialchars($row['nama_produk']) ?></h3>
                <div class="product-card-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></div>
                <div class="product-card-stock">Tersedia: <strong><?= $row['jumlah_stok'] ?> pcs</strong></div>
                
                <div class="product-card-action">
                    <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">
                        <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?= $row['jumlah_stok'] ?>" style="width: 70px; text-align: center; padding: 6px;">
                        <button type="submit" name="tambah_keranjang" class="btn btn-primary" style="flex: 1; padding: 8px 12px; font-size: 13px;">
                            <i data-lucide="shopping-cart" style="width: 14px; height: 14px;"></i> + Keranjang
                        </button>
                    </form>
                </div>
            </div>
            <?php 
            endwhile; 
            if(mysqli_num_rows($result_produk) == 0): 
            ?>
            <div style="grid-column: 1 / -1;">
                <div class="alert alert-warning" style="margin: 0; text-align: center; justify-content: center;">
                    <i data-lucide="info" style="width: 18px; height: 18px;"></i>
                    Tidak ada produk yang sesuai dengan kriteria pencarian Anda.
                </div>
            </div>
            <?php endif; ?>
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
