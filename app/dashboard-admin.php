<?php
$nama = $_SESSION['nama'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <h4 style="color: #ff6b6b; margin: 10px 0;">Admin Dashboard</h4>
    <ul>
        <li><a href="dashboard.php" class="active">Dashboard</a></li>
        <li><a href="#">Data Barang</a></li>
        <li><a href="#">Kelola User</a></li>
        <li><a href="#">Laporan Penjualan</a></li>
        <li><a href="#">Setting Toko</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Dashboard Admin</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Admin)</p>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Barang</h3>
            <p>120</p>
        </div>

        <div class="card">
            <h3>Transaksi Hari Ini</h3>
            <p>15</p>
        </div>

        <div class="card">
            <h3>Stok Menipis</h3>
            <p>8</p>
        </div>

        <div class="card">
            <h3>Total Penjualan</h3>
            <p>Rp 2.500.000</p>
        </div>
    </div>

    <div class="table-section">
        <h2>Barang ATK Terbaru</h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Pulpen Standard</td>
                    <td>Alat Tulis</td>
                    <td>50</td>
                    <td>Rp 3.000</td>
                    <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Buku Tulis Sidu</td>
                    <td>Buku</td>
                    <td>80</td>
                    <td>Rp 5.000</td>
                    <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Penghapus Joyko</td>
                    <td>Alat Tulis</td>
                    <td>35</td>
                    <td>Rp 2.000</td>
                    <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                </tr>

                <tr>
                    <td>4</td>
                    <td>Map Plastik</td>
                    <td>Perlengkapan Kantor</td>
                    <td>25</td>
                    <td>Rp 4.000</td>
                    <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
