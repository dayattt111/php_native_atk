<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$error = "";
$success = "";

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}
$id_user = (int)$_GET['id'];

// Prevent edit self status to nonaktif/pelanggan which could lock admin out
$is_self = ($id_user == $_SESSION['id_user']);

if (isset($_POST['edit'])) {
    $role = $_POST['role'] ?? "";
    $status = $_POST['status'] ?? "";

    if (!in_array($role, ['admin', 'pelanggan']) || !in_array($status, ['aktif', 'nonaktif'])) {
        $error = "Input tidak valid.";
    } elseif ($is_self && ($role !== 'admin' || $status !== 'aktif')) {
        $error = "Anda tidak bisa mengubah role atau menonaktifkan akun Anda sendiri.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET role=?, status=? WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, "ssi", $role, $status, $id_user);
        if (mysqli_stmt_execute($stmt)) {
            $success = "User berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui user.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch current data
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id_user=?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Toko ATK</title>
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
                <h1>Edit User</h1>
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

        <!-- FORM CONTAINER -->
        <div class="form-container" style="max-width: 600px; margin: 0;">
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

            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" disabled style="background: var(--bg-main); cursor: not-allowed; color: var(--text-secondary);">
                </div>
                <div class="form-group">
                    <label>Alamat Email</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: var(--bg-main); cursor: not-allowed; color: var(--text-secondary);">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" <?= $is_self ? 'disabled style="background: var(--bg-main); cursor: not-allowed;"' : '' ?>>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="pelanggan" <?= $user['role'] === 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Akun</label>
                    <select name="status" class="form-control" <?= $is_self ? 'disabled style="background: var(--bg-main); cursor: not-allowed;"' : '' ?>>
                        <option value="aktif" <?= $user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <?php if (!$is_self): ?>
                        <button type="submit" name="edit" class="btn btn-primary"><i data-lucide="save" style="width: 16px; height: 16px;"></i> Simpan Perubahan</button>
                    <?php else: ?>
                        <input type="hidden" name="role" value="admin">
                        <input type="hidden" name="status" value="aktif">
                    <?php endif; ?>
                    <a href="users.php" class="btn btn-secondary"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali</a>
                </div>
            </form>
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
