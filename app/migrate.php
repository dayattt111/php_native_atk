<?php
include "config.php";

$sql = "
ALTER TABLE transaksi 
ADD COLUMN status ENUM('Menunggu Pembayaran', 'Menunggu Verifikasi', 'Pembayaran Disetujui', 'Ditolak') NOT NULL DEFAULT 'Menunggu Pembayaran',
ADD COLUMN bukti_transfer VARCHAR(255) DEFAULT NULL;
";

if (mysqli_query($conn, $sql)) {
    echo "Tabel transaksi berhasil di-alter (penambahan kolom status & bukti_transfer).<br>";
} else {
    echo "Error alter tabel: " . mysqli_error($conn) . "<br>";
}

echo "Migrasi Selesai.";
?>
