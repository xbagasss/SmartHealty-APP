<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\AuthService;

session_start();
$db = new Database();
$auth = new AuthService($db);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = $auth->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $message = 'Email atau password salah!';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — SmartHealthy</title>
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
    
    /* Left Side - Visual */
    .auth-visual {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px;
        position: relative;
        overflow: hidden;
    }
    .auth-visual::before {
        content: '';
        position: absolute;
        top: -10%;
        left: -10%;
        width: 120%;
        height: 120%;
        background-image: url('https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=2560&q=80');
        background-size: cover;
        background-position: center;
        opacity: 0.1;
        z-index: 1;
    }
    .visual-content {
        position: relative;
        z-index: 2;
        max-width: 480px;
    }
    .logo-large {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 24px;
        letter-spacing: -1px;
    }
    .visual-heading {
        font-size: 48px;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 24px;
    }
    .visual-text {
        font-size: 18px;
        opacity: 0.9;
        line-height: 1.6;
    }

    /* Right Side - Form */
    .auth-form-container {
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }
    .auth-box {
        width: 100%;
        max-width: 400px;
    }
    
    .form-heading {
        font-size: 30px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .form-sub {
        color: #64748b;
        margin-bottom: 32px;
    }
    
    /* Responsive */
    @media (max-width: 900px) {
        body { grid-template-columns: 1fr; }
        .auth-visual { display: none; }
    }
  </style>
</head>
<body>

  <!-- Left Side: Branding -->
  <div class="auth-visual">
    <div class="visual-content">
        <div class="logo-large">⚡ SmartHealthy</div>
        <h1 class="visual-heading">Start your health journey today.</h1>
        <p class="visual-text">Pantau nutrisi harian, hitung kalori otomatis, dan dapatkan rekomendasi makanan sehat yang dipersonalisasi khusus untuk tubuh Anda.</p>
    </div>
  </div>

  <!-- Right Side: Login Form -->
  <div class="auth-form-container">
    <div class="auth-box">
        <h2 class="form-heading">Welcome back</h2>
        <p class="form-sub">Masukan detail akun Anda untuk login.</p>

        <?php if ($message): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:12px; margin-bottom:24px; font-size:14px; display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:12px; margin-bottom:24px; font-size:14px; display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Registrasi berhasil! Silakan login.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" style="height:48px; font-size:16px;" placeholder="name@company.com" required>
            </div>
            
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <label class="form-label" style="margin:0;">Password</label>
                    <a href="forgot_password.php" style="font-size:13px; color:#4f46e5; text-decoration:none; font-weight:600;">Lupa password?</a>
                </div>
                <input type="password" name="password" class="form-control" style="height:48px; font-size:16px;" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-block" style="height:48px; font-size:16px; background:#4f46e5;">Sign in</button>
        </form>

        <p style="text-align:center; margin-top:32px; color:#64748b; font-size:14px;">
            Belum punya akun? <a href="register.php" style="color:#4f46e5; font-weight:600; text-decoration:none;">Daftar gratis</a>
        </p>
    </div>
  </div>

</body>
</html>
