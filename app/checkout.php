<?php
include "config.php";
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['checkout'])) {
    header("Location: keranjang.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$metode_pembayaran = $_POST['metode_pembayaran'] ?? 'cash';

// Validasi metode pembayaran
if (!in_array($metode_pembayaran, ['cash', 'transfer', 'qris'])) {
    die("Metode pembayaran tidak valid.");
}

// 1. Validasi keranjang dan hitung total kembali (keamanan)
$query_keranjang = "
    SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.jumlah_stok
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id_produk
    WHERE k.id_user = $id_user
";
$result_keranjang = mysqli_query($conn, $query_keranjang);

if (mysqli_num_rows($result_keranjang) == 0) {
    die("Keranjang kosong. Tidak bisa checkout.");
}

$total_belanja = 0;
$items = [];
while ($row = mysqli_fetch_assoc($result_keranjang)) {
    // Validasi stok ulang saat checkout
    if ($row['jumlah'] > $row['jumlah_stok']) {
        die("Gagal checkout: Stok produk '{$row['nama_produk']}' tidak mencukupi. (Stok sisa: {$row['jumlah_stok']})");
    }
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $total_belanja += $row['subtotal'];
    $items[] = $row;
}

// 2. Mulai Transaksi (mysqli_begin_transaction)
mysqli_begin_transaction($conn);

try {
    // 3. Insert ke tabel transaksi
    $stmt_trx = mysqli_prepare($conn, "INSERT INTO transaksi (id_user, total, metode_pembayaran) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_trx, "ids", $id_user, $total_belanja, $metode_pembayaran);
    mysqli_stmt_execute($stmt_trx);
    $id_transaksi = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_trx);

    // 4. Insert ke tabel detail_transaksi & kurangi stok produk
    $stmt_detail = mysqli_prepare($conn, "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)");
    $stmt_stok = mysqli_prepare($conn, "UPDATE produk SET jumlah_stok = jumlah_stok - ? WHERE id_produk = ?");

    foreach ($items as $item) {
        // Insert Detail
        mysqli_stmt_bind_param($stmt_detail, "iiid", $id_transaksi, $item['id_produk'], $item['jumlah'], $item['subtotal']);
        mysqli_stmt_execute($stmt_detail);

        // Update Stok
        mysqli_stmt_bind_param($stmt_stok, "ii", $item['jumlah'], $item['id_produk']);
        mysqli_stmt_execute($stmt_stok);
    }
    mysqli_stmt_close($stmt_detail);
    mysqli_stmt_close($stmt_stok);

    // 5. Hapus isi keranjang user ini
    $stmt_hapus = mysqli_prepare($conn, "DELETE FROM keranjang WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt_hapus, "i", $id_user);
    mysqli_stmt_execute($stmt_hapus);
    mysqli_stmt_close($stmt_hapus);

    // 6. Jika semua berhasil, Commit Transaksi
    mysqli_commit($conn);

    // Redirect ke halaman riwayat pesanan dengan pesan sukses
    $_SESSION['sukses_checkout'] = "Pesanan berhasil dibuat dengan nomor TRX-" . str_pad($id_transaksi, 5, '0', STR_PAD_LEFT);
    header("Location: riwayat.php");
    exit;

} catch (Exception $e) {
    // 7. Jika terjadi error di salah satu query, Rollback Transaksi
    mysqli_rollback($conn);
    die("Terjadi kesalahan sistem saat memproses pesanan Anda. Transaksi dibatalkan. Error: " . $e->getMessage());
}
?>
