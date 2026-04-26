-- Buat Database
CREATE DATABASE IF NOT EXISTS toko_atk;
USE toko_atk;

-- Buat Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat Tabel Barang
CREATE TABLE IF NOT EXISTS barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_barang VARCHAR(150) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    stok INT NOT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total DECIMAL(12, 2) NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Data Barang Contoh
INSERT INTO barang (nama_barang, kategori, stok, harga) VALUES
('Pulpen Standard', 'Alat Tulis', 50, 3000),
('Buku Tulis Sidu', 'Buku', 80, 5000),
('Penghapus Joyko', 'Alat Tulis', 35, 2000),
('Map Plastik', 'Perlengkapan Kantor', 25, 4000),
('Pensil HB', 'Alat Tulis', 100, 2500),
('Spidol Kecil', 'Alat Tulis', 60, 3500),
('Kertas A4 1 Rim', 'Kertas', 20, 50000),
('Stapler Mini', 'Perlengkapan Kantor', 15, 25000);
