<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\AuthService;

$db = new Database();
$auth = new AuthService($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->register($_POST['name'], $_POST['email'], $_POST['password'])) {
        // Auto Login
        $user = $auth->login($_POST['email'], $_POST['password']);
        if ($user) {
            session_start();
            $_SESSION['user'] = $user;
            // Redirect DIRECTLY to profile setup
            header('Location: setup_profile.php');
            exit;
        } else {
            // Fallback (should not happen)
            header('Location: login.php?registered=1');
            exit;
        }
    } else {
        $message = 'Email sudah digunakan!';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — SmartHealthy</title>
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
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        background-image: url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=2560&q=80');
        background-size: cover;
        background-position: center;
        opacity: 0.15;
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
        <div class="logo-large">🥗 Eat Better, Live Better</div>
        <h1 class="visual-heading">Join the movement.</h1>
        <p class="visual-text">Ribuan pengguna telah berhasil mencapai berat badan ideal mereka bersama SmartHealthy. Giliran Anda sekarang.</p>
    </div>
  </div>

  <!-- Right Side: Register Form -->
  <div class="auth-form-container">
    <div class="auth-box">
        <h2 class="form-heading">Create an account</h2>
        <p class="form-sub">Mulai perjalanan sehat Anda dalam 30 detik.</p>

        <?php if ($message): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:12px; margin-bottom:24px; font-size:14px; display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" style="height:48px; font-size:16px;" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" style="height:48px; font-size:16px;" placeholder="name@company.com" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Create Password</label>
                <input type="password" name="password" class="form-control" style="height:48px; font-size:16px;" placeholder="Min. 8 characters" required>
            </div>

            <button type="submit" class="btn btn-block" style="height:48px; font-size:16px; background:#10b981;">Create Account</button>
        </form>

        <p style="text-align:center; margin-top:32px; color:#64748b; font-size:14px;">
            Sudah punya akun? <a href="login.php" style="color:#10b981; font-weight:600; text-decoration:none;">Log in</a>
        </p>
    </div>
  </div>

</body>
</html>
