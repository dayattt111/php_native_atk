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
        <li><a href="users.php" class="active">Kelola User</a></li>
        <li><a href="laporan.php">Laporan Penjualan</a></li>
        <li><a href="setting-toko.php">Setting Toko</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Edit User</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <div class="card p-4 shadow-sm border-0" style="max-width: 600px;">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" <?= $is_self ? 'disabled' : '' ?>>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="pelanggan" <?= $user['role'] === 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Status Akun</label>
                <select name="status" class="form-select" <?= $is_self ? 'disabled' : '' ?>>
                    <option value="aktif" <?= $user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            
            <?php if (!$is_self): ?>
                <button type="submit" name="edit" class="btn btn-warning w-auto text-dark">Simpan Perubahan</button>
            <?php else: ?>
                <input type="hidden" name="role" value="admin">
                <input type="hidden" name="status" value="aktif">
            <?php endif; ?>
            <a href="users.php" class="btn btn-secondary w-auto">Kembali</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
