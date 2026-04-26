# Aplikasi Toko ATK - PHP Native

Aplikasi web sederhana untuk manajemen Toko ATK (Alat Tulis Kantor) menggunakan PHP Native dan MySQL.

## 📋 Fitur

- ✅ Sistem Login & Register
- ✅ Dashboard dengan Statistik
- ✅ Manajemen Barang
- ✅ Responsive Design
- ✅ Session Management
- ✅ Password Hashing (Keamanan)

## 🛠️ Persyaratan

- **PHP 7.4+**
- **MySQL/MariaDB**
- **Laragon** (atau Apache + MySQL)

## 📦 Instalasi

### 1. Setup Database

**Cara 1: Menggunakan phpMyAdmin**
- Buka phpMyAdmin di browser (http://localhost/phpmyadmin)
- Klik "Import" atau "Baru"
- Copy isi dari file `setup.sql` ke query editor
- Jalankan query

**Cara 2: Menggunakan MySQL Command Line**
```bash
mysql -u root -p < setup.sql
```

### 2. Konfigurasi Database

Edit file `app/config.php` jika diperlukan:
```php
$host = "localhost";
$user = "root";
$pass = "";        // Masukkan password MySQL Anda jika ada
$db   = "toko_atk";
```

### 3. Jalankan Aplikasi

```
http://localhost/0PHP_Native/
```

Anda akan diarahkan ke halaman Login.

## 👤 Akun Test

Setelah setup selesai, daftarkan akun baru melalui halaman Register atau gunakan konfigurasi database untuk menambah user langsung.

## 📁 Struktur Folder

```
0PHP_Native/
├── app/
│   ├── config.php        # Konfigurasi Database
│   ├── index.php         # Entry Point (Redirect ke Login)
│   ├── login.php         # Halaman Login
│   ├── register.php      # Halaman Register
│   ├── dashboard.php     # Halaman Dashboard
│   ├── logout.php        # Logout Handler
│   └── style/
│       └── style.css     # CSS Styling
├── setup.sql             # Database Setup Script
└── README.md             # File ini
```

## 🎨 Halaman-Halaman

### 1. **Login** (`/app/login.php`)
- Input Email & Password
- Validasi user
- Session management

### 2. **Register** (`/app/register.php`)
- Buat akun baru
- Validasi email
- Password hashing

### 3. **Dashboard** (`/app/dashboard.php`)
- Statistik penjualan
- Daftar barang terbaru
- Menu sidebar navigasi

## 🔐 Keamanan

- ✅ Password di-hash menggunakan `password_hash()`
- ✅ Input di-sanitasi dengan `mysqli_real_escape_string()`
- ✅ Session management untuk proteksi halaman
- ✅ XSS protection dengan `htmlspecialchars()`

## 📝 Catatan

- Database harus di-setup terlebih dahulu sebelum aplikasi bisa diakses
- Pastikan folder `style/` memiliki file `style.css`
- Gunakan HTTPS di production untuk keamanan yang lebih baik

## 🚀 Pengembangan Lebih Lanjut

Untuk membuat fitur yang lebih kompleks:

1. Tambahkan halaman Data Barang
2. Implementasi fitur Transaksi
3. Buat halaman Laporan
4. Tambahkan kategori barang dinamis
5. Implementasi API untuk integrasi

---

**Dibuat dengan PHP Native | MySQL | CSS3**
