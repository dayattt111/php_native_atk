<?php
$nama = $_SESSION['nama'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="sidebar">
    <h2>Toko ATK</h2>
    <h4 style="color: #51cf66; margin: 10px 0;">Pelanggan</h4>
    <ul>
        <li><a href="dashboard.php" class="active">Dashboard</a></li>
        <li><a href="#">Belanja</a></li>
        <li><a href="#">Keranjang Saya</a></li>
        <li><a href="#">Riwayat Pesanan</a></li>
        <li><a href="#">Profil</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Dashboard Pelanggan</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Pelanggan)</p>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Pesanan Aktif</h3>
            <p>2</p>
        </div>

        <div class="card">
            <h3>Total Pembelian</h3>
            <p>5</p>
        </div>

        <div class="card">
            <h3>Poin Reward</h3>
            <p>500</p>
        </div>

        <div class="card">
            <h3>Total Belanja</h3>
            <p>Rp 450.000</p>
        </div>
    </div>

    <div class="table-section">
        <h2>Pesanan Terbaru Anda</h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Pesanan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>ORD-001</td>
                    <td>2026-05-05</td>
                    <td>Rp 150.000</td>
                    <td><span style="color: green;">Terkirim</span></td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>ORD-002</td>
                    <td>2026-05-04</td>
                    <td>Rp 200.000</td>
                    <td><span style="color: orange;">Diproses</span></td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>ORD-003</td>
                    <td>2026-05-03</td>
                    <td>Rp 100.000</td>
                    <td><span style="color: green;">Terkirim</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
