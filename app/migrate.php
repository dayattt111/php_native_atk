<?php
include "config.php";

// Check if 'gambar' column already exists in 'produk'
$check_gambar = mysqli_query($conn, "SHOW COLUMNS FROM produk LIKE 'gambar'");
if (mysqli_num_rows($check_gambar) == 0) {
    $sql_gambar = "ALTER TABLE produk ADD COLUMN gambar VARCHAR(255) DEFAULT NULL";
    if (mysqli_query($conn, $sql_gambar)) {
        echo "Kolom 'gambar' berhasil ditambahkan ke tabel produk.<br>";
    } else {
        echo "Error menambahkan kolom gambar: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Kolom 'gambar' sudah ada pada tabel produk.<br>";
}

// Modify 'metode_pembayaran' enum to include 'qris'
$sql_metode = "ALTER TABLE transaksi MODIFY COLUMN metode_pembayaran ENUM('cash', 'transfer', 'qris') NOT NULL DEFAULT 'cash'";
if (mysqli_query($conn, $sql_metode)) {
    echo "Kolom 'metode_pembayaran' berhasil diperbarui untuk mendukung 'qris'.<br>";
} else {
    echo "Error memperbarui kolom metode_pembayaran: " . mysqli_error($conn) . "<br>";
}

echo "Migrasi Selesai.";
?>
