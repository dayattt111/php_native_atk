<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Kirim email verifikasi ke pengguna baru.
 *
 * @param string $toEmail   Alamat email tujuan
 * @param string $toName    Nama penerima
 * @param string $token     Token verifikasi unik
 * @return bool Berhasil atau tidak
 */
function sendVerificationEmail(string $toEmail, string $toName, string $token): bool
{
    $appUrl  = rtrim($_ENV['APP_URL'] ?? 'http://localhost/0PHP_Native', '/');
    $appName = $_ENV['APP_NAME'] ?? 'Toko ATK';
    $verifyLink = $appUrl . '/app/verify-email.php?token=' . urlencode($token);

    $mail = new PHPMailer(true);

    try {
        // Server settings – Mailtrap Sandbox SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
        $mail->CharSet    = 'UTF-8';

        // Pengirim & Penerima
        $mail->setFrom(
            $_ENV['EMAIL_FROM'] ?? 'noreply@toko-atk.com',
            $_ENV['EMAIL_FROM_NAME'] ?? $appName
        );
        $mail->addAddress($toEmail, $toName);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = '✉️ Verifikasi Email Anda – ' . $appName;
        $mail->Body    = buildVerificationEmailHtml($toName, $verifyLink, $appName);
        $mail->AltBody = "Halo $toName,\n\nSilakan klik tautan berikut untuk memverifikasi email Anda:\n$verifyLink\n\nLink berlaku selama 1 jam.\n\n– Tim $appName";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error tapi jangan tampilkan detail ke user
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/** Bangun HTML template email verifikasi */
function buildVerificationEmailHtml(string $nama, string $link, string $appName): string
{
    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f4f6fb; margin:0; padding:0; }
    .wrapper { max-width:560px; margin:40px auto; background:#fff; border-radius:12px;
               box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:hidden; }
    .header  { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
               padding:36px 40px; text-align:center; }
    .header h1 { color:#fff; margin:0; font-size:26px; letter-spacing:.5px; }
    .body    { padding:36px 40px; color:#333; line-height:1.7; }
    .body p  { margin:0 0 16px; }
    .btn-wrap{ text-align:center; margin:28px 0; }
    .btn     { display:inline-block; padding:14px 36px; background:linear-gradient(135deg,#667eea,#764ba2);
               color:#fff!important; text-decoration:none; border-radius:8px; font-size:15px;
               font-weight:600; letter-spacing:.3px; }
    .note    { font-size:13px; color:#888; margin-top:8px; }
    .footer  { background:#f4f6fb; padding:20px 40px; text-align:center;
               font-size:12px; color:#aaa; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>🛒 {$appName}</h1>
    </div>
    <div class="body">
      <p>Halo, <strong>{$nama}</strong>!</p>
      <p>Terima kasih sudah mendaftar di <strong>{$appName}</strong>.
         Satu langkah lagi — verifikasi email Anda agar akun Anda aktif.</p>
      <div class="btn-wrap">
        <a href="{$link}" class="btn">✅ Verifikasi Email Saya</a>
      </div>
      <p class="note">Tombol tidak berfungsi? Salin tautan ini ke browser Anda:<br>
         <a href="{$link}" style="color:#667eea;word-break:break-all;">{$link}</a></p>
      <p class="note">⏰ Tautan ini berlaku selama <strong>1 jam</strong>.
         Jika Anda tidak mendaftar, abaikan email ini.</p>
    </div>
    <div class="footer">© {$appName} · Email ini dikirim otomatis, jangan dibalas.</div>
  </div>
</body>
</html>
HTML;
}
