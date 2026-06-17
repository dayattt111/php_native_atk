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
        <li><a href="keranjang.php">Keranjang Saya</a></li>
        <li><a href="riwayat.php" class="active">Riwayat Pesanan</a></li>
        <li><a href="profile.php">Profil</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Riwayat Pesanan</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Pelanggan)</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nomor Transaksi</th>
                        <th>Tanggal</th>
                        <th>Metode Pembayaran</th>
                        <th>Total Belanja</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result_transaksi)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                        <td><span class="badge bg-secondary"><?= strtoupper($row['metode_pembayaran']) ?></span></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td>
                            <?php
                                $badge_class = 'bg-secondary';
                                if ($row['status'] === 'Menunggu Pembayaran') $badge_class = 'bg-warning text-dark';
                                elseif ($row['status'] === 'Menunggu Verifikasi') $badge_class = 'bg-info text-dark';
                                elseif ($row['status'] === 'Pembayaran Disetujui') $badge_class = 'bg-success';
                                elseif ($row['status'] === 'Ditolak') $badge_class = 'bg-danger';
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($row['status']) ?></span>
                        </td>
                        <td>
                            <?php if($row['status'] === 'Menunggu Pembayaran' && $row['metode_pembayaran'] === 'transfer'): ?>
                                <a href="upload-bukti.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-warning btn-sm text-dark w-auto mb-1 d-block">Unggah Bukti</a>
                            <?php endif; ?>
                            <!-- Tombol untuk memicu modal -->
                            <button type="button" class="btn btn-info btn-sm text-white w-auto d-block" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id_transaksi'] ?>">
                                Lihat Detail
                            </button>

                            <!-- Modal Detail Transaksi -->
                            <div class="modal fade" id="modalDetail<?= $row['id_transaksi'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></h5>
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
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($dt['nama_produk']) ?></h6>
                                                        <small class="text-muted"><?= $dt['jumlah'] ?> x Rp <?= number_format($dt['harga'], 0, ',', '.') ?></small>
                                                    </div>
                                                    <span class="fw-bold">Rp <?= number_format($dt['subtotal'], 0, ',', '.') ?></span>
                                                </li>
                                                <?php endwhile; ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-light mt-2">
                                                    <span class="fw-bold fs-6">TOTAL KESELURUHAN</span>
                                                    <span class="fw-bold fs-6 text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
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
                        <td colspan="7" class="text-center">Anda belum memiliki riwayat pesanan.</td>
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
