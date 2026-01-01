<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\AuthService;

$db = new Database();
$auth = new AuthService($db);
$message = '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resend'])) {
        if ($auth->resendOtp($email)) {
            $message = "Kode OTP baru telah dikirim ke email Anda.";
        } else {
            $message = "Gagal mengirim ulang OTP.";
        }
    } else {
        $otp = $_POST['otp_code'];
        $result = $auth->verifyOtp($email, $otp);
        if ($result === true) {
            // Auto Login logic manually since we don't have password here, or redir to login
            // For better UX, let's redirect to login with success message
            header("Location: login.php?verified=1");
            exit;
        } else {
            $message = $result;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Verifikasi OTP — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); width: 100%; max-width: 400px; text-align: center; }
    .otp-input { letter-spacing: 8px; font-size: 24px; text-align: center; width: 100%; padding: 12px; margin: 20px 0; border: 2px solid #e2e8f0; border-radius: 8px; }
  </style>
</head>
<body>
    <div class="card">
        <h2>Verifikasi Email 📧</h2>
        <p style="color: #64748b;">Kami telah mengirimkan 6 digit kode OTP ke <strong><?= htmlspecialchars($email) ?></strong></p>
        
        <?php if ($message): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 10px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp_code" class="otp-input" maxlength="6" placeholder="000000" required autofocus>
            <button type="submit" class="btn btn-block" style="background:#2563eb;">Verifikasi</button>
        </form>

        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="resend" value="1">
            <button type="submit" style="background:none; border:none; color:#2563eb; cursor:pointer; text-decoration:underline;">Kirim Ulang OTP</button>
        </form>
    </div>
</body>
</html>
