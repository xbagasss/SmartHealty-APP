<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

$token = $_GET['token'] ?? '';
$db = new Database();
$error = '';
$success = '';

// Check Token Validity
$valid = false;
$user_id = null;

if ($token) {
    $stmt = $db->conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $valid = true;
        $user_id = $res->fetch_assoc()['id'];
    } else {
        $error = "Token tidak valid atau sudah kadaluarsa.";
    }
} else {
    $error = "Token tidak ditemukan.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (strlen($pass) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($pass !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        // Reset Password & Clear Token
        $update = $db->conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
        $update->bind_param("si", $hash, $user_id);
        
        if ($update->execute()) {
            $success = "Password berhasil diubah! Silakan login dengan password baru.";
            $valid = false; // Disable form
        } else {
            $error = "Gagal mengupdate password.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { background: #f8fafc; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .card { background: white; width: 100%; max-width: 400px; padding: 32px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
  </style>
</head>
<body>

  <div class="card">
    <h1 style="font-size: 24px; margin-bottom: 8px; text-align: center;">Set New Password</h1>
    <p class="muted" style="text-align: center; margin-bottom: 24px;">Buat password baru yang aman.</p>

    <?php if ($success): ?>
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:24px; text-align:center;">
            <?= $success ?>
            <div style="margin-top:12px;">
                <a href="login.php" class="btn btn-block">Login Sekarang</a>
            </div>
        </div>
    <?php elseif ($error && !$valid && empty($_POST)): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:16px; text-align:center;"><?= $error ?></div>
        <a href="forgot_password.php" class="btn btn-block" style="background:#64748b;">Request Link Baru</a>
    <?php endif; ?>

    <?php if ($valid): ?>
        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-block">Simpan Password</button>
        </form>
    <?php endif; ?>
  </div>

</body>
</html>
