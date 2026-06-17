<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$success = "";
$error = "";

// Aksi verifikasi
if (isset($_POST['aksi_verifikasi'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    $status_baru = $_POST['status_baru'];

    if (in_array($status_baru, ['Pembayaran Disetujui', 'Ditolak'])) {
        $stmt = mysqli_prepare($conn, "UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
        mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_transaksi);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Status transaksi TRX-" . str_pad($id_transaksi, 5, '0', STR_PAD_LEFT) . " berhasil diperbarui menjadi " . $status_baru . ".";
        } else {
            $error = "Gagal memperbarui status transaksi.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Ambil semua transaksi dengan bukti transfer, utamakan yang "Menunggu Verifikasi"
$query = "
    SELECT t.*, u.nama as nama_pelanggan, u.email 
    FROM transaksi t 
    JOIN users u ON t.id_user = u.id_user 
    WHERE t.metode_pembayaran = 'transfer' 
    ORDER BY CASE WHEN t.status = 'Menunggu Verifikasi' THEN 1 ELSE 2 END, t.tanggal DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - Toko ATK</title>
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
        <li><a href="verifikasi.php" class="active">Verifikasi Pembayaran</a></li>
        <li><a href="laporan.php">Laporan Penjualan</a></li>
        <li><a href="setting-toko.php">Setting Toko</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Verifikasi Pembayaran</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nomor Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?><br><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
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
                            <button type="button" class="btn btn-primary btn-sm w-auto mb-1 d-block" data-bs-toggle="modal" data-bs-target="#modalAksi<?= $row['id_transaksi'] ?>">
                                Detail & Aksi
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Aksi Verifikasi -->
                    <div class="modal fade" id="modalAksi<?= $row['id_transaksi'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?> - <?= htmlspecialchars($row['nama_pelanggan']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row">
                                    <div class="col-md-6 mb-3">
                                        <h6>Bukti Transfer</h6>
                                        <?php if ($row['bukti_transfer']): ?>
                                            <a href="uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" target="_blank">
                                                <img src="uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" alt="Bukti Transfer" class="img-fluid rounded border mb-2" style="max-height: 300px;">
                                            </a>
                                            <p class="text-muted small">Klik gambar untuk memperbesar</p>
                                        <?php else: ?>
                                            <div class="alert alert-warning">Pelanggan belum mengunggah bukti transfer.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6>Detail Pesanan</h6>
                                        <ul class="list-group list-group-flush mb-3">
                                            <?php
                                            $id_trx = $row['id_transaksi'];
                                            $q_detail = mysqli_query($conn, "SELECT p.nama_produk, dt.jumlah, dt.subtotal FROM detail_transaksi dt JOIN produk p ON dt.id_produk = p.id_produk WHERE dt.id_transaksi = $id_trx");
                                            while($dt = mysqli_fetch_assoc($q_detail)):
                                            ?>
                                            <li class="list-group-item d-flex justify-content-between px-0">
                                                <small><?= htmlspecialchars($dt['nama_produk']) ?> (<?= $dt['jumlah'] ?>x)</small>
                                                <small class="fw-bold">Rp <?= number_format($dt['subtotal'], 0, ',', '.') ?></small>
                                            </li>
                                            <?php endwhile; ?>
                                        </ul>
                                        <div class="d-flex justify-content-between mb-4">
                                            <span class="fw-bold">Total:</span>
                                            <span class="fw-bold text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                                        </div>

                                        <?php if ($row['status'] === 'Menunggu Verifikasi'): ?>
                                        <h6>Aksi Verifikasi</h6>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi'] ?>">
                                            <button type="submit" name="aksi_verifikasi" value="Ditolak" class="btn btn-danger w-50">Tolak</button>
                                            <button type="submit" name="aksi_verifikasi" value="Pembayaran Disetujui" class="btn btn-success w-50">Setujui</button>
                                            <input type="hidden" name="status_baru" id="status_baru_<?= $row['id_transaksi'] ?>" value="">
                                            <script>
                                                // Small JS to handle multi submit button value in pure PHP
                                                document.querySelectorAll('#modalAksi<?= $row['id_transaksi'] ?> button[name="aksi_verifikasi"]').forEach(btn => {
                                                    btn.addEventListener('click', function(e) {
                                                        document.getElementById('status_baru_<?= $row['id_transaksi'] ?>').value = this.value;
                                                    });
                                                });
                                            </script>
                                        </form>
                                        <?php else: ?>
                                            <div class="alert alert-info">Status Saat Ini: <strong><?= htmlspecialchars($row['status']) ?></strong></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data transaksi transfer.</td>
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
