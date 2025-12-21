<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\NotificationService;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $db = new Database();
    
    // Check if email exists
    $stmt = $db->conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save to DB
        $update = $db->conn->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expiry, $user['id']);
        
        if ($update->execute()) {
            // Send Email
            $notch = new NotificationService();
            $link = "http://localhost/yourproject/public/reset_password.php?token=" . $token;
            $subject = "Reset Password - SmartHealthy";
            $msg = "
                <h3>Reset Password Request</h3>
                <p>Anda menerima email ini karena ada permintaan untuk reset password akun SmartHealthy Anda.</p>
                <p>Silakan klik link di bawah ini untuk membuat password baru:</p>
                <p><a href='$link' style='background:#4f46e5; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
                <p>Link ini akan kadaluarsa dalam 1 jam.</p>
                <p>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
            ";
            
            if ($notch->sendEmail($email, $subject, $msg, false, true)) {
                $message = "Link reset password telah dikirim ke email Anda.";
            } else {
                $error = "Gagal mengirim email. Silakan coba lagi nanti.";
            }
        }
    } else {
        // Security: Don't reveal if email exists or not, but for UX usually we say sent if valid format
        $message = "Link reset password telah dikirim ke email Anda (jika terdaftar).";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
        margin: 0;
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
        font-family: 'Inter', sans-serif;
    }
    
    .auth-visual {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px;
    }
    .auth-form-container {
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }
    .auth-box { width: 100%; max-width: 400px; }
    
    @media (max-width: 900px) {
        body { grid-template-columns: 1fr; }
        .auth-visual { display: none; }
    }
  </style>
</head>
<body>

  <div class="auth-visual">
    <div style="max-width: 480px;">
        <h1 style="font-size: 42px; margin-bottom: 20px;">Forgot Password?</h1>
        <p style="font-size: 18px; opacity: 0.9;">Jangan khawatir, kami akan membantu Anda mengembalikan akses ke akun Anda dengan aman.</p>
    </div>
  </div>

  <div class="auth-form-container">
    <div class="auth-box">
        <h2 style="font-size: 28px; margin-bottom: 8px;">Reset Password</h2>
        <p class="muted" style="margin-bottom: 32px;">Masukkan email yang terdaftar untuk menerima link reset.</p>

        <?php if ($message): ?>
            <div style="background:#eff6ff; color:#1d4ed8; padding:12px; border-radius:12px; margin-bottom:24px; font-size:14px;">✅ <?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:12px; margin-bottom:24px; font-size:14px;">❌ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" style="height:48px;" placeholder="name@company.com" required>
            </div>

            <button type="submit" class="btn btn-block" style="height:48px; background:#4f46e5;">Kirim Link Reset</button>
        </form>

        <p style="text-align:center; margin-top:32px; font-size:14px;">
            <a href="login.php" style="color:#64748b; text-decoration:none;">← Kembali ke Login</a>
        </p>
    </div>
  </div>

</body>
</html>
