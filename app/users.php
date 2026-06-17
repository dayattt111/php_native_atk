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
    <!-- Bootstrap CSS -->
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
        <h1>Kelola User</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <div class="card p-4 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="fw-bold">Total User: <?= mysqli_num_rows($result) ?></div>
            <form method="GET" class="d-flex flex-wrap gap-2">
                <select name="role" class="form-select w-auto">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="pelanggan" <?= $roleFilter === 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                </select>
                <input type="text" name="search" class="form-control" style="width: 200px;" placeholder="Cari nama/email..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary w-auto">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <span class="badge <?= $row['role'] === 'admin' ? 'bg-danger' : 'bg-info' ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $row['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                        <td>
                            <a href="edit-user.php?id=<?= $row['id_user'] ?>" class="btn btn-warning btn-sm w-auto mb-1">Edit</a>
                            <?php if ($row['id_user'] != $_SESSION['id_user']): // jangan hapus diri sendiri ?>
                                <a href="hapus-user.php?id=<?= $row['id_user'] ?>" class="btn btn-danger btn-sm w-auto mb-1" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data user.</td>
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
