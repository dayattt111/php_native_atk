<?php
include "config.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <ul>
        <li><a href="#" class="active">Dashboard</a></li>
        <li><a href="#">Data Barang</a></li>
        <li><a href="#">Transaksi</a></li>
        <li><a href="#">Laporan</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Dashboard</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?></p>
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
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Pulpen Standard</td>
                    <td>Alat Tulis</td>
                    <td>50</td>
                    <td>Rp 3.000</td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Buku Tulis Sidu</td>
                    <td>Buku</td>
                    <td>80</td>
                    <td>Rp 5.000</td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Penghapus Joyko</td>
                    <td>Alat Tulis</td>
                    <td>35</td>
                    <td>Rp 2.000</td>
                </tr>

                <tr>
                    <td>4</td>
                    <td>Map Plastik</td>
                    <td>Perlengkapan Kantor</td>
                    <td>25</td>
                    <td>Rp 4.000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>