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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .sidebar ul li a { text-decoration: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <h4 style="color: #51cf66; margin: 10px 0;">Pelanggan</h4>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="belanja.php">Belanja</a></li>
        <li><a href="keranjang.php" class="active">Keranjang Saya</a></li>
        <li><a href="riwayat.php">Riwayat Pesanan</a></li>
        <li><a href="profile.php">Profil</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Keranjang Saya</h1>
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

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Kuantitas</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <form method="POST" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="id_keranjang" value="<?= $item['id_keranjang'] ?>">
                                        <input type="number" name="jumlah" class="form-control form-control-sm" value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['jumlah_stok'] ?>" style="width: 70px;">
                                        <button type="submit" name="update_keranjang" class="btn btn-outline-primary btn-sm w-auto">Update</button>
                                    </form>
                                    <?php if($item['jumlah'] > $item['jumlah_stok']): ?>
                                        <small class="text-danger d-block mt-1">Melebihi stok (<?= $item['jumlah_stok'] ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                                <td>
                                    <a href="keranjang.php?hapus=<?= $item['id_keranjang'] ?>" class="btn btn-danger btn-sm w-auto" onclick="return confirm('Hapus item ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($items) === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center">Keranjang Anda kosong. Yuk <a href="belanja.php">belanja</a> sekarang!</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0 bg-light">
                <h4 class="mb-3">Ringkasan Pesanan</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Item:</span>
                    <span><?= count($items) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">Total Harga:</span>
                    <span class="fw-bold fs-5 text-success">Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
                </div>
                
                <?php if(count($items) > 0): ?>
                <form action="checkout.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-select" required>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                    <button type="submit" name="checkout" class="btn btn-primary w-100 fw-bold py-2" onclick="return confirm('Lanjutkan ke pembayaran?')">Proses Checkout</button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary w-100 py-2" disabled>Proses Checkout</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
