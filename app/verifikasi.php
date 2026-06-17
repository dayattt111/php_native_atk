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
    <!-- Bootstrap CSS for Modals & Grids -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="style/app.css">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Scoped overrides to resolve conflicts between Bootstrap and app.css */
        .admin-layout a {
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
    </style>
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
            <li><a href="verifikasi.php" class="active"><i data-lucide="check-square"></i> Verifikasi Pembayaran</a></li>
            <li><a href="laporan.php"><i data-lucide="file-bar-chart"></i> Laporan Penjualan</a></li>
            <li><a href="setting-toko.php"><i data-lucide="settings"></i> Setting Toko</a></li>
            <li><a href="logout.php" class="logout"><i data-lucide="log-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">
                <h1>Verifikasi Pembayaran</h1>
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

        <!-- ALERTS -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- TABLE SECTION -->
        <div class="table-section">
            <div class="table-header-flex">
                <h2>Pembayaran Transfer Masuk</h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Transaksi</th>
                            <th>Nama Pelanggan</th>
                            <th>Tanggal & Waktu</th>
                            <th>Total Tagihan</th>
                            <th>Status Pembayaran</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge badge-info">TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong><br>
                                <span style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></span>
                            </td>
                            <td><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></td>
                            <td><strong style="color: var(--success-text);">Rp <?= number_format($row['total'], 0, ',', '.') ?></strong></td>
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
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAksi<?= $row['id_transaksi'] ?>">
                                    <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Detail & Verifikasi
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Aksi Verifikasi -->
                        <div class="modal fade" id="modalAksi<?= $row['id_transaksi'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" style="font-weight: 700; color: var(--text-primary);">
                                            Verifikasi Transaksi TRX-<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body row">
                                        <div class="col-md-6 mb-3">
                                            <h6 style="font-weight: 600; margin-bottom: 12px; color: var(--text-secondary);">Bukti Transfer Pelanggan</h6>
                                            <?php if ($row['bukti_transfer']): ?>
                                                <a href="uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" target="_blank">
                                                    <img src="uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" alt="Bukti Transfer" class="img-fluid rounded border mb-2" style="max-height: 320px; object-fit: contain; width: 100%;">
                                                </a>
                                                <p class="text-muted small" style="text-align: center;"><i data-lucide="zoom-in" style="width: 12px; height: 12px; vertical-align: middle;"></i> Klik gambar untuk memperbesar</p>
                                            <?php else: ?>
                                                <div class="alert alert-warning" style="margin: 0;">
                                                    <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                                                    Pelanggan belum mengunggah bukti transfer.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <h6 style="font-weight: 600; margin-bottom: 12px; color: var(--text-secondary);">Detail Pesanan</h6>
                                            <ul class="list-group list-group-flush mb-3">
                                                <?php
                                                $id_trx = $row['id_transaksi'];
                                                $q_detail = mysqli_query($conn, "SELECT p.nama_produk, dt.jumlah, dt.subtotal FROM detail_transaksi dt JOIN produk p ON dt.id_produk = p.id_produk WHERE dt.id_transaksi = $id_trx");
                                                while($dt = mysqli_fetch_assoc($q_detail)):
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <div>
                                                        <span style="font-size: 14px; font-weight: 500;"><?= htmlspecialchars($dt['nama_produk']) ?></span>
                                                        <span style="font-size: 12px; color: var(--text-muted); display: block;"><?= $dt['jumlah'] ?> pcs</span>
                                                    </div>
                                                    <span style="font-size: 14px; font-weight: 600; color: var(--text-primary);">Rp <?= number_format($dt['subtotal'], 0, ',', '.') ?></span>
                                                </li>
                                                <?php endwhile; ?>
                                            </ul>
                                            <div class="d-flex justify-content-between mb-4" style="border-top: 1px solid var(--border); padding-top: 16px;">
                                                <span style="font-weight: 700; font-size: 15px;">Total Tagihan:</span>
                                                <span style="font-weight: 700; font-size: 16px; color: var(--primary);">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                                            </div>

                                            <?php if ($row['status'] === 'Menunggu Verifikasi'): ?>
                                            <h6 style="font-weight: 600; margin-bottom: 12px; color: var(--text-secondary);">Aksi Persetujuan</h6>
                                            <form method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi'] ?>">
                                                <button type="submit" name="aksi_verifikasi" value="Ditolak" class="btn btn-danger w-50"><i data-lucide="x" style="width: 16px; height: 16px;"></i> Tolak</button>
                                                <button type="submit" name="aksi_verifikasi" value="Pembayaran Disetujui" class="btn btn-primary w-50" style="background: var(--success); border-color: var(--success);"><i data-lucide="check" style="width: 16px; height: 16px;"></i> Setujui</button>
                                                <input type="hidden" name="status_baru" id="status_baru_<?= $row['id_transaksi'] ?>" value="">
                                                <script>
                                                    document.querySelectorAll('#modalAksi<?= $row['id_transaksi'] ?> button[name="aksi_verifikasi"]').forEach(btn => {
                                                        btn.addEventListener('click', function(e) {
                                                            document.getElementById('status_baru_<?= $row['id_transaksi'] ?>').value = this.value;
                                                        });
                                                    });
                                                </script>
                                            </form>
                                            <?php else: ?>
                                                <div class="alert alert-warning" style="margin: 0;">
                                                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                                                    Status Pembayaran: <strong><?= htmlspecialchars($row['status']) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">Tidak ada transaksi transfer masuk.</td>
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

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
