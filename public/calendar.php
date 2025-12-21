<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$db = new Database();
$user_id = $_SESSION['user']['id'];
$days = [];

// Fetch all days user has log
$q = $db->conn->query("
    SELECT DISTINCT date 
    FROM nutrition_logs 
    WHERE user_id = $user_id 
    ORDER BY date DESC
");

while ($d = $q->fetch_assoc()) {
    $days[] = $d['date'];
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Food Diary — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .calendar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; margin-top: 24px; }
    .day-card { 
        background: #f0f9ff; /* Soft Blue */
        border: 1px solid #bae6fd; 
        border-radius: 16px; 
        padding: 24px; 
        text-align: center; 
        text-decoration: none; 
        color: #0369a1; 
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .day-card:hover { 
        transform: translateY(-4px); 
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.15); 
        background: #e0f2fe;
    }
    .day-card span { 
        display: block; 
        font-size: 13px; 
        color: #64748b; 
        font-weight: 500; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
    }
    .day-card strong {
        font-size: 18px;
        font-weight: 800;
        color: #0ea5e9;
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="search_nutrition.php" class="nav-link">Search</a>
      <a href="analytics.php" class="nav-link">Analytics</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <section class="welcome card">
      <div>
        <h1>Food Diary Calendar</h1>
        <p class="muted">Klik tanggal untuk melihat riwayat makanan Anda.</p>
      </div>
    </section>

    <?php if (empty($days)): ?>
        <div class="card" style="text-align:center; padding: 40px; margin-top: 20px;">
            <p class="muted">Belum ada riwayat makanan.</p>
            <a href="search_nutrition.php" class="btn">Mulai Mencatat</a>
        </div>
    <?php else: ?>
        <div class="calendar-grid">
          <?php foreach ($days as $d): ?>
            <?php 
                $timestamp = strtotime($d);
                $dayName = date('l', $timestamp);
                $dateFormatted = date('d M Y', $timestamp);
            ?>
            <a href="calendar_view.php?date=<?= $d ?>" class="day-card">
                <span><?= $dayName ?></span>
                <strong><?= $dateFormatted ?></strong>
            </a>
          <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </main>
<script src="theme_loader.js"></script>
</body>
</html>
