<?php
include "config.php";

echo "<h1>🔐 Reset Admin Password</h1>";

// Generate password baru untuk admin
$newPassword = "user123";
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

echo "<p>Mengupdate password user...</p>";
echo "<pre>";
echo "Email: user@gmail.com\n";
echo "Password Baru: <strong>" . $newPassword . "</strong>\n";
echo "Hash Baru: " . $newHash . "\n\n";

// Update password di database
$query = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $newHash) . "' WHERE email = 'user@gmail.com'";

if (mysqli_query($conn, $query)) {
    echo "✅ PASSWORD BERHASIL DIUBAH!\n\n";
    echo "Silakan login dengan:\n";
    echo "Email: user@gmail.com\n";
    echo "Password: " . $newPassword . "\n";
    
    // Verify
    echo "\n--- VERIFIKASI ---\n";
    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE email = 'user@gmail.com'");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    $verify = password_verify($newPassword, $row['password']);
    echo "Password Verify: " . ($verify ? "✅ OK" : "❌ FAILED");
} else {
    echo "❌ GAGAL: " . mysqli_error($conn);
}

echo "</pre>";

echo "<p><a href='login.php'>→ Ke Halaman Login</a></p>";
?>
