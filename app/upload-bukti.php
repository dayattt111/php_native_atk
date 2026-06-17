<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama = $_SESSION['nama'];
$error = "";
$success = "";

if (!isset($_GET['id'])) {
    header("Location: riwayat.php");
    exit;
}

$id_transaksi = (int)$_GET['id'];

// Validasi kepemilikan transaksi dan status
$stmt_cek = mysqli_prepare($conn, "SELECT status, metode_pembayaran FROM transaksi WHERE id_transaksi = ? AND id_user = ?");
mysqli_stmt_bind_param($stmt_cek, "ii", $id_transaksi, $id_user);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);
$transaksi = mysqli_fetch_assoc($res_cek);
mysqli_stmt_close($stmt_cek);

if (!$transaksi) {
    header("Location: riwayat.php");
    exit;
}
if ($transaksi['status'] !== 'Menunggu Pembayaran' || $transaksi['metode_pembayaran'] !== 'transfer') {
    header("Location: riwayat.php");
    exit;
}

// Proses Upload
if (isset($_POST['upload'])) {
    $file = $_FILES['bukti_transfer'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if ($file_error === 4) {
        $error = "Pilih file bukti transfer terlebih dahulu.";
    } elseif (!in_array($file_ext, $allowed_ext)) {
        $error = "Ekstensi file tidak diizinkan. Hanya boleh jpg, jpeg, png.";
    } elseif ($file_size > 2000000) { // 2MB
        $error = "Ukuran file terlalu besar. Maksimal 2MB.";
    } else {
        // Buat folder jika belum ada
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        $new_file_name = uniqid('bukti_') . '.' . $file_ext;
        $destination = 'uploads/' . $new_file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            // Update database
            $stmt = mysqli_prepare($conn, "UPDATE transaksi SET bukti_transfer = ?, status = 'Menunggu Verifikasi' WHERE id_transaksi = ?");
            mysqli_stmt_bind_param($stmt, "si", $new_file_name, $id_transaksi);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['sukses_checkout'] = "Bukti transfer berhasil diunggah. Menunggu verifikasi admin.";
                header("Location: riwayat.php");
                exit;
            } else {
                $error = "Gagal mengupdate database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Gagal mengunggah file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Bukti Pembayaran - Toko ATK</title>
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
        <h1>Unggah Bukti</h1>
        <p>Halo, <?= htmlspecialchars($nama); ?> (Pelanggan)</p>
    </div>

    <div class="card p-4 shadow-sm border-0" style="max-width: 500px;">
        <h4 class="mb-4">TRX-<?= str_pad($id_transaksi, 5, '0', STR_PAD_LEFT) ?></h4>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Silakan pilih file bukti transfer Anda (JPG/PNG, maks 2MB)</label>
                <input class="form-control" type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png" required>
            </div>
            <button type="submit" name="upload" class="btn btn-primary w-100 mb-2">Unggah Sekarang</button>
            <a href="riwayat.php" class="btn btn-secondary w-100">Batal</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
