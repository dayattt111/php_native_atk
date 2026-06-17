<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];

// Search & Filter Logic
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$conditions = [];
if ($search) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(nama LIKE '%$search_esc%' OR email LIKE '%$search_esc%')";
}
if ($roleFilter && in_array($roleFilter, ['admin', 'pelanggan'])) {
    $conditions[] = "role = '$roleFilter'";
}

$where = "";
if (count($conditions) > 0) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

// Get Users List
$query = "SELECT * FROM users $where ORDER BY id_user DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Toko ATK</title>
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="style/app.css">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
            <li><a href="users.php" class="active"><i data-lucide="users"></i> Kelola User</a></li>
            <li><a href="verifikasi.php"><i data-lucide="check-square"></i> Verifikasi Pembayaran</a></li>
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
                <h1>Kelola User</h1>
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

        <!-- TABLE SECTION -->
        <div class="table-section">
            <div class="table-header-flex">
                <div>
                    <h2>Daftar Pengguna Sistem</h2>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Total Terdaftar: <strong><?= mysqli_num_rows($result); ?></strong> user</p>
                </div>
                <div>
                    <form method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <select name="role" class="form-control" style="width: auto; min-width: 130px;">
                            <option value="">Semua Role</option>
                            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="pelanggan" <?= $roleFilter === 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                        </select>
                        <div class="search-input-wrapper">
                            <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama/email..." value="<?= htmlspecialchars($search); ?>" style="padding-left: 36px; min-width: 200px;">
                        </div>
                        <button type="submit" class="btn btn-secondary"><i data-lucide="sliders" style="width: 16px; height: 16px;"></i> Filter</button>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Alamat Email</th>
                            <th>Role</th>
                            <th>Status Email</th>
                            <th>Tanggal Daftar</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if ($row['role'] === 'admin'): ?>
                                    <span class="badge badge-danger"><?= ucfirst($row['role']); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-info"><?= ucfirst($row['role']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'aktif'): ?>
                                    <span class="badge badge-success"><?= ucfirst($row['status']); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= ucfirst($row['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit-user.php?id=<?= $row['id_user']; ?>" class="btn btn-secondary btn-sm"><i data-lucide="edit" style="width: 14px; height: 14px;"></i> Edit</a>
                                    <?php if ($row['id_user'] != $_SESSION['id_user']): ?>
                                        <a href="hapus-user.php?id=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')"><i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Hapus</a>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--bg-main); color: var(--text-muted); padding: 6px 12px;">Aktif Logged In</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        endwhile; 
                        if(mysqli_num_rows($result) == 0): 
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">Tidak ada data pengguna ditemukan.</td>
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
