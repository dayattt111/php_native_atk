<?php
include "config.php";

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

// ======================
// HELPER FUNCTIONS
// ======================

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function insertLoginLog($conn, $idUser, $status)
{
    $stmt = mysqli_prepare($conn, "INSERT INTO login (id_user, status) VALUES (?, ?)");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "is", $idUser, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// ======================
// DEFAULT VARIABLES
// ======================

$error   = "";
$warning = "";

$fieldErrors = [
    "role"     => "",
    "email"    => "",
    "password" => "",
];

$oldRole  = $_POST['role'] ?? "";
$oldEmail = $_POST['email'] ?? "";

// ======================
// LOGIN PROCESS
// ======================

if (isset($_POST['login'])) {
    $role     = trim($_POST['role'] ?? "");
    $email    = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    $oldRole  = $role;
    $oldEmail = $email;

    // Validasi role
    if ($role === "") {
        $fieldErrors["role"] = "Silakan pilih role terlebih dahulu.";
    } elseif (!in_array($role, ["admin", "pelanggan"], true)) {
        $fieldErrors["role"] = "Role yang dipilih tidak valid.";
    }

    // Validasi email
    if ($email === "") {
        $fieldErrors["email"] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors["email"] = "Format email tidak valid. Contoh: nama@email.com.";
    }

    // Validasi password
    if ($password === "") {
        $fieldErrors["password"] = "Password wajib diisi.";
    }

    // Jika ada field yang salah, tampilkan pesan umum
    if (
        $fieldErrors["role"] !== "" ||
        $fieldErrors["email"] !== "" ||
        $fieldErrors["password"] !== ""
    ) {
        $error = "Periksa kembali data login Anda.";
    } else {
        // Ambil user berdasarkan email
        $stmt = mysqli_prepare($conn,
            "SELECT id_user, nama, email, password, role, status 
             FROM users 
             WHERE email = ? 
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Terjadi kesalahan pada server. Silakan coba lagi.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user   = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            if (!$user) {
                $fieldErrors["email"] = "Email tidak terdaftar.";
                $error = "Email yang Anda masukkan belum terdaftar. Silakan cek kembali atau daftar akun baru.";
            } elseif ($user["role"] !== $role) {
                $roleAsli = ucfirst($user["role"]);
                $rolePilih = ucfirst($role);

                $fieldErrors["role"] = "Role tidak sesuai. Akun ini terdaftar sebagai {$roleAsli}.";
                $error = "Role login tidak sesuai. Email ini terdaftar sebagai {$roleAsli}, tetapi Anda memilih {$rolePilih}.";
            } elseif (!password_verify($password, $user["password"])) {
                insertLoginLog($conn, (int)$user["id_user"], "gagal");

                $fieldErrors["password"] = "Password salah.";
                $error = "Password yang Anda masukkan salah. Silakan coba lagi.";
            } elseif ($user["status"] === "nonaktif") {
                insertLoginLog($conn, (int)$user["id_user"], "gagal");

                $warning = "Akun Anda belum diverifikasi. Silakan cek email " . $user["email"] . " dan klik tautan verifikasi.";
            } else {
                insertLoginLog($conn, (int)$user["id_user"], "berhasil");

                $_SESSION["login"]   = true;
                $_SESSION["id_user"] = $user["id_user"];
                $_SESSION["nama"]    = $user["nama"];
                $_SESSION["email"]   = $user["email"];
                $_SESSION["role"]    = $user["role"];

                header("Location: dashboard.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko ATK</title>
    <link rel="stylesheet" href="style/style.css">

    <style>
        .form-group.has-error input,
        .form-group.has-error select {
            border: 1px solid #dc3545;
            background: #fff8f8;
        }

        .field-error {
            display: block;
            margin-top: 6px;
            color: #dc3545;
            font-size: 13px;
            line-height: 1.4;
        }

        .field-hint {
            display: block;
            margin-top: 6px;
            color: #777;
            font-size: 12px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>Login</h2>
        <p class="subtitle">Masuk ke aplikasi Toko ATK</p>

        <?php if ($error !== "") : ?>
            <div class="alert error"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($warning !== "") : ?>
            <div class="alert warning"><?= e($warning); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group <?= $fieldErrors["role"] !== "" ? "has-error" : ""; ?>">
                <label>Role</label>
                <select name="role">
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" <?= $oldRole === "admin" ? "selected" : ""; ?>>Admin</option>
                    <option value="pelanggan" <?= $oldRole === "pelanggan" ? "selected" : ""; ?>>Pelanggan</option>
                </select>

                <?php if ($fieldErrors["role"] !== "") : ?>
                    <small class="field-error"><?= e($fieldErrors["role"]); ?></small>
                <?php else : ?>
                    <small class="field-hint">Pilih role sesuai jenis akun Anda.</small>
                <?php endif; ?>
            </div>

            <div class="form-group <?= $fieldErrors["email"] !== "" ? "has-error" : ""; ?>">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email Anda"
                       value="<?= e($oldEmail); ?>">

                <?php if ($fieldErrors["email"] !== "") : ?>
                    <small class="field-error"><?= e($fieldErrors["email"]); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group <?= $fieldErrors["password"] !== "" ? "has-error" : ""; ?>">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password">

                <?php if ($fieldErrors["password"] !== "") : ?>
                    <small class="field-error"><?= e($fieldErrors["password"]); ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" name="login" class="btn">Login</button>
        </form>

        <p class="auth-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
    </div>
</div>

</body>
</html>