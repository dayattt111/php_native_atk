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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .sidebar ul li a { text-decoration: none; }
        .product-card { transition: transform 0.2s; }
        .product-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <h4 style="color: #51cf66; margin: 10px 0;">Pelanggan</h4>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="belanja.php" class="active">Belanja</a></li>
        <li><a href="keranjang.php">Keranjang Saya</a></li>
        <li><a href="riwayat.php">Riwayat Pesanan</a></li>
        <li><a href="profile.php">Profil</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Belanja</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Pelanggan)</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-4">
        <form method="GET" class="d-flex" style="max-width: 400px;">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary w-auto">Cari</button>
        </form>
    </div>

    <div class="row">
        <?php while($row = mysqli_fetch_assoc($result_produk)): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0 product-card p-3">
                <div class="card-body p-0 d-flex flex-column">
                    <h5 class="card-title fw-bold"><?= htmlspecialchars($row['nama_produk']) ?></h5>
                    <p class="card-text text-success fw-bold mb-1">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
                    <p class="card-text text-muted small mb-3">Stok: <?= $row['jumlah_stok'] ?></p>
                    
                    <div class="mt-auto">
                        <form method="POST" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">
                            <input type="number" name="jumlah" class="form-control form-control-sm" value="1" min="1" max="<?= $row['jumlah_stok'] ?>" style="width: 70px;">
                            <button type="submit" name="tambah_keranjang" class="btn btn-primary btn-sm w-100">+ Keranjang</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if(mysqli_num_rows($result_produk) == 0): ?>
        <div class="col-12">
            <div class="alert alert-info">Tidak ada produk yang tersedia saat ini.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
