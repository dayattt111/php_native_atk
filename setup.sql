-- =====================================================
-- DATABASE PENJUALAN ATK
-- =====================================================

DROP DATABASE IF EXISTS penjualan_atk;
CREATE DATABASE penjualan_atk;
USE penjualan_atk;

-- =====================================================
-- 1. TABEL USERS
-- Menyimpan data akun dan profil pengguna
-- =====================================================
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
    -- Kolom untuk verifikasi email
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'nonaktif',
    verification_token VARCHAR(64) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 2. TABEL LOGIN
-- Menyimpan riwayat login user
-- Relasi:
-- users.id_user -> login.id_user
-- Jenis relasi: One-to-Many
-- =====================================================
CREATE TABLE login (
    id_login INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    waktu_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('berhasil', 'gagal') NOT NULL DEFAULT 'berhasil',

    CONSTRAINT fk_login_users
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. TABEL PRODUK
-- Menyimpan data produk ATK
-- =====================================================
CREATE TABLE produk (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    nama_produk VARCHAR(100) NOT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    jumlah_stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. TABEL KERANJANG
-- Menyimpan produk yang dimasukkan user ke keranjang
-- Relasi:
-- users.id_user     -> keranjang.id_user
-- produk.id_produk  -> keranjang.id_produk
-- Jenis relasi:
-- users ke keranjang  = One-to-Many
-- produk ke keranjang = One-to-Many
-- =====================================================
CREATE TABLE keranjang (
    id_keranjang INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_keranjang_users
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_keranjang_produk
        FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY unique_user_produk (id_user, id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. TABEL TRANSAKSI
-- Menyimpan transaksi utama
-- Relasi:
-- users.id_user -> transaksi.id_user
-- Jenis relasi: One-to-Many
-- =====================================================
CREATE TABLE transaksi (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('cash', 'transfer') NOT NULL DEFAULT 'cash',

    CONSTRAINT fk_transaksi_users
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. TABEL DETAIL TRANSAKSI
-- Menyimpan rincian produk dalam setiap transaksi
-- Relasi:
-- transaksi.id_transaksi -> detail_transaksi.id_transaksi
-- produk.id_produk       -> detail_transaksi.id_produk
-- Jenis relasi:
-- transaksi ke detail_transaksi = One-to-Many
-- produk ke detail_transaksi    = One-to-Many
-- =====================================================
CREATE TABLE detail_transaksi (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,

    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_detail_produk
        FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERT DATA CONTOH USERS
-- Password contoh: password
-- Untuk aplikasi PHP asli, password sebaiknya dibuat dengan password_hash()
-- =====================================================
-- status 'aktif' dan email_verified_at diisi agar data contoh bisa langsung login
INSERT INTO users (nama, email, password, role, status, email_verified_at) VALUES
('Admin Toko', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCxmU8Eb.jXMxnpkpOWK', 'admin', 'aktif', NOW()),
('User Demo', 'user@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCxmU8Eb.jXMxnpkpOWK', 'pelanggan', 'aktif', NOW());

-- =====================================================
-- INSERT DATA CONTOH LOGIN
-- =====================================================
INSERT INTO login (id_user, status) VALUES
(1, 'berhasil'),
(2, 'berhasil');

-- =====================================================
-- INSERT DATA CONTOH PRODUK
-- =====================================================
INSERT INTO produk (nama_produk, harga, jumlah_stok) VALUES
('Pulpen Standard', 3000, 50),
('Buku Tulis Sidu', 5000, 80),
('Penghapus Joyko', 2000, 35),
('Map Plastik', 4000, 25),
('Pensil HB', 2500, 100),
('Spidol Kecil', 3500, 60),
('Kertas A4 1 Rim', 50000, 20),
('Stapler Mini', 25000, 15);

-- =====================================================
-- INSERT DATA CONTOH KERANJANG
-- =====================================================
INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES
(2, 1, 2),
(2, 2, 3);

-- =====================================================
-- INSERT DATA CONTOH TRANSAKSI
-- =====================================================
INSERT INTO transaksi (id_user, total, metode_pembayaran) VALUES
(2, 21000, 'cash');

-- =====================================================
-- INSERT DATA CONTOH DETAIL TRANSAKSI
-- Transaksi 1:
-- Pulpen Standard 2 x 3000 = 6000
-- Buku Tulis Sidu 3 x 5000 = 15000
-- Total = 21000
-- =====================================================
INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES
(1, 1, 2, 6000),
(1, 2, 3, 15000);

-- =====================================================
-- ALTER TABLE (jalankan ini jika database sudah ada
-- dan ingin menambah kolom verifikasi tanpa reset data)
-- =====================================================
-- ALTER TABLE users
--     ADD COLUMN status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'nonaktif' AFTER role,
--     ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL AFTER status,
--     ADD COLUMN email_verified_at DATETIME DEFAULT NULL AFTER verification_token;
--
-- UPDATE users SET status = 'aktif', email_verified_at = NOW();
-- (jalankan baris di atas hanya untuk mengaktifkan user lama yang sudah ada)