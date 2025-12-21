<?php
/**
 * DASHBOARD.PHP - Main User Dashboard
 * 
 * This file displays the user's nutrition tracking dashboard including:
 * - Daily and weekly calorie intake vs target
 * - TDEE (Total Daily Energy Expenditure) calculation
 * - Macro nutrient tracking (Protein, Carbs, Fat)
 * - Health warnings and recommendations
 * - Weight tracking
 * - Meal plans
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;

// ============================================
// AUTHENTICATION CHECK
// ============================================
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// FORCE PROFILE SETUP
// If user hasn't set up height/age/activity, redirect to onboarding
// We check 'height' as a proxy for completed profile
if (empty($_SESSION['user']['height']) || empty($_SESSION['user']['age'])) {
    // Re-fetch from DB to be sure (in case session is stale)
    $chkStmt = (new Database())->conn->prepare("SELECT height, age FROM users WHERE id = ?");
    $chkStmt->bind_param("i", $_SESSION['user']['id']);
    $chkStmt->execute();
    $chkUser = $chkStmt->get_result()->fetch_assoc();

    if (empty($chkUser['height']) || empty($chkUser['age'])) {
        header("Location: setup_profile.php");
        exit;
    } else {
        // Update session if DB has data but session doesn't
        $_SESSION['user']['height'] = $chkUser['height'];
        $_SESSION['user']['age'] = $chkUser['age'];
    }
}

// ============================================
// INITIALIZE DATABASE & USER DATA
// ============================================
$db = new Database();
$user = $_SESSION['user'];
$user_id = $user['id'];
$goal = $user['goal'] ?? 'maintain'; // User's fitness goal: diet, maintain, or muscle

// ============================================
// CALCULATE TDEE (Total Daily Energy Expenditure)
// ============================================
// TDEE is the number of calories a person burns per day based on their:
// - BMR (Basal Metabolic Rate): calories burned at rest
// - Activity level: sedentary, light, moderate, active, athlete
// - Goal adjustment: -500 for diet, +400 for muscle gain

// Fetch user stats for TDEE calculation
$stmt = $db->conn->prepare("SELECT gender, age, height, activity_level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$uStats = $stmt->get_result()->fetch_assoc();

// Get latest weight from weight_logs table
$wStmt = $db->conn->prepare("SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date DESC, created_at DESC LIMIT 1");
$wStmt->bind_param("i", $user_id);
$wStmt->execute();
$wRes = $wStmt->get_result();
$weight = 60; // Default weight if no logs exist
if ($wRes->num_rows > 0) {
    $weight = $wRes->fetch_assoc()['weight'];
}

if ($uStats) {
    // ============================================
    // MIFFLIN-ST JEOR EQUATION for BMR
    // ============================================
    // BMR = (10 × weight in kg) + (6.25 × height in cm) - (5 × age in years) + s
    // where s = +5 for males, -161 for females
    
    $bmr = (10 * $weight) + (6.25 * ($uStats['height']??170)) - (5 * ($uStats['age']??25));
    $bmr += ($uStats['gender'] === 'female') ? -161 : 5;
    
    // ============================================
    // ACTIVITY LEVEL MULTIPLIERS
    // ============================================
    // These multipliers convert BMR to TDEE based on activity level
    $multiplier = 1.55; // default moderate
    $actMap = [
        'sedentary' => 1.2,    // Little to no exercise
        'light' => 1.375,      // Light exercise 1-3 days/week
        'moderate' => 1.55,    // Moderate exercise 3-5 days/week
        'active' => 1.725,     // Heavy exercise 6-7 days/week
        'athlete' => 1.9       // Very heavy exercise, physical job
    ];
    if (isset($uStats['activity_level']) && isset($actMap[$uStats['activity_level']])) {
        $multiplier = $actMap[$uStats['activity_level']];
    }
    $tdee = $bmr * $multiplier;
    
    // ============================================
    // GOAL-BASED CALORIE ADJUSTMENT
    // ============================================
    // - Diet: -500 calories (creates deficit for weight loss)
    // - Muscle: +400 calories (creates surplus for muscle gain)
    // - Maintain: no adjustment
    if ($goal === 'diet') $tdee -= 500;
    if ($goal === 'muscle') $tdee += 400;
    
    // Safety check: never go below 1200 calories (minimum for health)
    if ($tdee < 1200) $tdee = 1200;
    
    $dailyTarget = round($tdee);
} else {
    // ============================================
    // FALLBACK TARGETS (if no profile data exists)
    // ============================================
    $targetMap = [
        'diet' => 1600,
        'maintain' => 2200,
        'muscle' => 2600
    ];
    $dailyTarget = $targetMap[$goal] ?? 2200;
}

// Weekly target = daily target × 7 days
$weeklyTarget = $dailyTarget * 7;

// ============================================
// FETCH TODAY'S & WEEKLY CALORIE INTAKE
// ============================================
$today = date('Y-m-d');

// Get total calories consumed today
$stmt = $db->conn->prepare("SELECT SUM(calories) AS t FROM nutrition_logs WHERE user_id = ? AND date = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$todayRow = $stmt->get_result()->fetch_assoc();
$todayCalories = (int)($todayRow['t'] ?? 0);

// Get total calories consumed in the last 7 days (including today)
$stmt = $db->conn->prepare("SELECT SUM(calories) AS tw FROM nutrition_logs WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$weekRow = $stmt->get_result()->fetch_assoc();
$weekCalories = (int)($weekRow['tw'] ?? 0);

// ============================================
// FETCH TODAY'S FOOD LOG ENTRIES
// ============================================
// Get all food items logged today for display in the table
$stmt = $db->conn->prepare("SELECT id, food_name, calories, protein, carbs, fat FROM nutrition_logs WHERE user_id = ? AND date = ? ORDER BY id DESC");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$todayFoods = $stmt->get_result();

// ============================================
// CALCULATE WEEKLY MACRO AVERAGES
// ============================================
// Get average daily intake of calories and macros over the last 7 days
// This helps identify eating patterns and nutritional balance
$stmt = $db->conn->prepare("SELECT AVG(calories) AS avg_cal, AVG(protein) AS avg_p, AVG(carbs) AS avg_c, AVG(fat) AS avg_f FROM nutrition_logs WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$macAvg = $stmt->get_result()->fetch_assoc();
$avgCal = round($macAvg['avg_cal'] ?? 0);
$avgP = round($macAvg['avg_p'] ?? 0, 1);
$avgC = round($macAvg['avg_c'] ?? 0, 1);
$avgF = round($macAvg['avg_f'] ?? 0, 1);

// ============================================
// CALCULATE TODAY'S MACRO TOTALS
// ============================================
// Sum up protein, carbs, and fat from today's food log
// These will be used for health warnings and recommendations
$todayP = 0; $todayC = 0; $todayF = 0;

$stmt = $db->conn->prepare("SELECT SUM(protein) as p, SUM(carbs) as c, SUM(fat) as f FROM nutrition_logs WHERE user_id = ? AND date = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if ($res) {
    $todayP = (float)$res['p'];
    $todayC = (float)$res['c'];
    $todayF = (float)$res['f'];
}

// ============================================
// HEALTH WARNINGS & RECOMMENDATIONS SYSTEM
// ============================================
$warnings = [];
$recom = [];

// ============================================
// CALCULATE MACRO RATIOS (as percentage of total grams)
// ============================================
// Ideal ratios vary by goal:
// - Diet: Higher protein (30%), moderate carbs (40%), lower fat (30%)
// - Muscle: High protein (35%), high carbs (45%), moderate fat (20%)
// - Maintain: Balanced (25% P, 45% C, 30% F)

$totalGrams = $todayP + $todayC + $todayF;
$pctP = $totalGrams > 0 ? ($todayP / $totalGrams) * 100 : 0;
$pctC = $totalGrams > 0 ? ($todayC / $totalGrams) * 100 : 0;
$pctF = $totalGrams > 0 ? ($todayF / $totalGrams) * 100 : 0;

// WARNING #1: CALORIE SURPLUS (exceeds target by 200+)
if ($todayCalories > $dailyTarget + 200) {
    $warnings[] = "⚠️ <strong>Surplus Kalori</strong>: Anda makan melebihi target harian. Kurangi porsi saat Dinner.";
}

// WARNING #2: HIGH CARB INTAKE (>60% of macros)
// Common issue in Indonesian diet (rice-heavy)
if ($pctC > 60) {
    $warnings[] = "⚠️ <strong>Dominasi Carbs (" . round($pctC) . "%)</strong>: Asupan didominasi oleh Rice/Noodles/Sugar. Ini bisa memicu fat storage.";
    $recom[] = "🍚 Kurangi porsi Rice setengah dari biasanya, ganti dengan Vegetables.";
}

// WARNING #3: HIGH FAT INTAKE (>40% of macros)
if ($pctF > 40) {
    $warnings[] = "⚠️ <strong>Tinggi Fat (" . round($pctF) . "%)</strong>: Terlalu banyak Oily/Fried Food hari ini.";
    $recom[] = "🛑 Hindari Fried Foods. Pilih lauk Steamed, Grilled, atau Boiled untuk sisa hari ini.";
}

// WARNING #4: LOW PROTEIN INTAKE (<15% of macros)
// Protein is crucial for muscle maintenance and satiety
if ($pctP < 15 && $todayCalories > 500) {
    $warnings[] = "⚠️ <strong>Low Protein (Hanya " . round($pctP) . "%)</strong>: Tubuh butuh protein untuk menjaga muscle mass.";
    $recom[] = "🥚 Segera makan <strong>2 Boiled Eggs</strong> atau <strong>100g Chicken Breast</strong>.";
}

// POSITIVE FEEDBACK or REMINDER
if (!$warnings && $todayCalories > 500) {
    $recom[] = "✨ <strong>Balanced Diet!</strong> Komposisi nutrisi Anda hari ini sangat baik. Keep it up!";
} elseif (!$warnings && $todayCalories < 500) {
    $recom[] = "👋 Jangan lupa catat Breakfast dan Lunch Anda!";
}

// ============================================
// WEIGHT TRACKING FOR DIET USERS
// ============================================
// Track weight loss progress for users with 'diet' goal
$weightData = null;
if ($goal === 'diet') {
    // Ensure weight_logs table exists
    $db->conn->query("
        CREATE TABLE IF NOT EXISTS weight_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            weight DECIMAL(5,2) NOT NULL,
            date DATE NOT NULL,
            notes VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get weight statistics
    $stmt = $db->conn->prepare("
        SELECT 
            (SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date ASC LIMIT 1) as first_weight,
            (SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date DESC LIMIT 1) as current_weight,
            (SELECT date FROM weight_logs WHERE user_id = ? ORDER BY date DESC LIMIT 1) as last_date,
            COUNT(*) as total_entries
        FROM weight_logs 
        WHERE user_id = ?
    ");
    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $weightData = $stmt->get_result()->fetch_assoc();
    
    if ($weightData['current_weight']) {
        $weightData['change'] = $weightData['current_weight'] - $weightData['first_weight'];
        $weightData['percent'] = ($weightData['change'] / $weightData['first_weight']) * 100;
    }
}

// ============================================
// MUSCLE GAIN TRACKING FOR MUSCLE BUILDING USERS
// ============================================
// Track weight gain progress for users with 'muscle' goal
$muscleData = null;
if ($goal === 'muscle') {
    // Ensure weight_logs table exists
    $db->conn->query("
        CREATE TABLE IF NOT EXISTS weight_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            weight DECIMAL(5,2) NOT NULL,
            date DATE NOT NULL,
            notes VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get muscle gain statistics
    $stmt = $db->conn->prepare("
        SELECT 
            (SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date ASC LIMIT 1) as first_weight,
            (SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date DESC LIMIT 1) as current_weight,
            (SELECT date FROM weight_logs WHERE user_id = ? ORDER BY date DESC LIMIT 1) as last_date,
            COUNT(*) as total_entries
        FROM weight_logs 
        WHERE user_id = ?
    ");
    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $muscleData = $stmt->get_result()->fetch_assoc();
    
    if ($muscleData['current_weight']) {
        $muscleData['gain'] = $muscleData['current_weight'] - $muscleData['first_weight'];
        $muscleData['percent'] = ($muscleData['gain'] / $muscleData['first_weight']) * 100;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link" style="color:var(--blue); font-weight:700;">Dashboard</a>
      <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
        <a href="admin/dashboard.php" class="nav-link" style="color:#ef4444; font-weight:600;">Admin Panel</a>
      <?php endif; ?>
      <a href="search_nutrition.php" class="nav-link">Search Nutrition (API)</a>
      <a href="analytics.php" class="nav-link">Analytics</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <section class="welcome card">
      <div>
        <h1>Hello, <?= htmlspecialchars($user['name']) ?></h1>
        <p class="muted">Goal: <strong><?= htmlspecialchars(ucfirst($goal)) ?></strong> — daily target <strong><?= $dailyTarget ?> kcal</strong></p>
      </div>
      <div class="goal-form">
        <form method="post" action="set_goal.php" style="display: flex; align-items: center; gap: 8px;">
          <span class="muted" style="font-size: 14px;">Ubah Goal:</span>
          <select name="goal" aria-label="Choose goal" class="form-control" style="width: auto; margin: 0; padding: 8px 32px 8px 12px;">
            <option value="diet" <?= $goal==='diet' ? 'selected' : '' ?>>Diet (Fat Loss)</option>
            <option value="maintain" <?= $goal==='maintain' ? 'selected' : '' ?>>Maintain</option>
            <option value="muscle" <?= $goal==='muscle' ? 'selected' : '' ?>>Build Muscle</option>
          </select>
          <button type="submit" class="btn" style="padding: 8px 16px;">Update</button>
        </form>
      </div>
    </section>

    <!-- Quick Menu Section -->
    <section class="cards-row quick-menu" style="margin-top: 0;">
      <a href="meal_plan.php" class="card link-card" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; text-align: left; gap: 12px; color: inherit; transition: transform 0.2s; padding: 24px;">
        <div style="background: #e0f2fe; padding: 12px; border-radius: 12px; color: #0284c7; min-width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
        <div>
          <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; color: #6366f1;">Meal Plan</div>
          <div class="muted" style="font-size: 13px;">Atur rencana makan</div>
        </div>
      </a>

      <a href="calendar.php" class="card link-card" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; text-align: left; gap: 12px; color: inherit; transition: transform 0.2s; padding: 24px;">
        <div style="background: #e0faff; padding: 12px; border-radius: 12px; color: #0891b2; min-width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
        <div>
          <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; color: #8b5cf6;">Food Diary</div>
          <div class="muted" style="font-size: 13px;">Lihat catatan harian</div>
        </div>
      </a>

      <a href="notifications.php" class="card link-card" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; text-align: left; gap: 12px; color: inherit; transition: transform 0.2s; padding: 24px;">
        <div style="background: #fdf2f8; padding: 12px; border-radius: 12px; color: #db2777; min-width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        </div>
        <div>
          <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; color: #a855f7;">Email Report</div>
          <div class="muted" style="font-size: 13px;">Kirim analisis via email</div>
        </div>
      </a>

      <a href="foods/index.php" class="card link-card" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; text-align: left; gap: 12px; color: inherit; transition: transform 0.2s; padding: 24px;">
        <div style="background: #ecfccb; padding: 12px; border-radius: 12px; color: #65a30d; min-width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
        </div>
        <div>
          <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; color: #a855f7;">Foods Database</div>
          <div class="muted" style="font-size: 13px;">Kelola database makanan</div>
        </div>
      </a>
    </section>

    <style>
      .link-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    </style>

    <!-- NEW: DAILY LOG REMINDER -->
    <?php if ($todayCalories == 0): ?>
    <section class="card" style="background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; margin-bottom: 24px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
      <div style="display: flex; align-items: center; gap: 16px;">
        <div style="font-size: 32px; background: rgba(251, 191, 36, 0.2); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">⚠️</div>
        <div>
          <h3 style="margin: 0 0 4px 0; font-size: 18px; color: #b45309;">Anda belum mencatat makanan hari ini!</h3>
          <p style="margin: 0; opacity: 0.9; font-size: 14px;">Catat asupan nutrisi Anda untuk mendapatkan analisis kesehatan yang akurat.</p>
        </div>
      </div>
      <a href="search_nutrition.php" class="btn" style="background: #d97706; color: white; padding: 10px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; white-space: nowrap;">⚡ Catat Sekarang</a>
    </section>
    <?php endif; ?>

    <section class="cards-row">
      <div class="stat card">
        <div class="stat-title">Today</div>
        <div class="stat-value"><?= $today ?></div>
        <div class="muted">Calories today</div>
        <div class="big"><?= $todayCalories ?> kcal</div>
      </div>

      <div class="stat card">
        <div class="stat-title">Week</div>
        <div class="stat-value">Last 7 days</div>
        <div class="muted">Calories this week</div>
        <div class="big"><?= $weekCalories ?> kcal</div>
      </div>

      <div class="stat card">
        <div class="stat-title">Avg Macro</div>
        <div class="muted">Weekly avg (g)</div>
        <div class="macro-row">
          <div>P: <strong><?= $avgP ?></strong></div>
          <div>C: <strong><?= $avgC ?></strong></div>
          <div>F: <strong><?= $avgF ?></strong></div>
        </div>
      </div>

      <div class="stat card">
        <div class="stat-title">Target</div>
        <div class="muted">Daily / Weekly</div>
        <div class="big"><?= $dailyTarget ?> kcal / <?= $weeklyTarget ?> kcal</div>
      </div>
    </section>

    <section class="grid">
      <div class="card">
        <h3>Progress — Hari ini</h3>
        <?php
          $pct = $dailyTarget>0 ? round(($todayCalories/$dailyTarget)*100) : 0;
          $pct = max(0, min(200, $pct));
          $barClass = $pct > 120 ? 'danger' : ($pct > 100 ? 'warn' : 'ok');
        ?>
        <div class="progress-label"><?= $todayCalories ?> / <?= $dailyTarget ?> kcal (<?= $pct ?>%)</div>
        <div class="progress">
          <div class="progress-fill <?= $barClass ?>" style="width: <?= $pct ?>%"></div>
        </div>

        <h4 style="margin-top:12px">Progress — Mingguan</h4>
        <?php
          $pctW = $weeklyTarget>0 ? round(($weekCalories/$weeklyTarget)*100) : 0;
          $pctW = max(0, min(200, $pctW));
          $barClassW = $pctW > 120 ? 'danger' : ($pctW > 100 ? 'warn' : 'ok');
        ?>
        <div class="progress-label"><?= $weekCalories ?> / <?= $weeklyTarget ?> kcal (<?= $pctW ?>%)</div>
        <div class="progress">
          <div class="progress-fill <?= $barClassW ?>" style="width: <?= $pctW ?>%"></div>
        </div>

        <div class="muted" style="margin-top:10px">
          <a href="calendar.php" class="link">Lihat Food Diary</a>
        </div>
      </div>

      <div class="card">
        <h3>Grafik (Kalori & Makro)</h3>
        <canvas id="chartCombined"></canvas>
      </div>
    </section>

    <?php if ($goal === 'diet' && $weightData): ?>
    <!-- Weight Tracking Widget (Diet Users Only) -->
    <section class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
        <h3 style="color: white; margin: 0;">📊 Weight Tracking</h3>
        <a href="weight_tracker.php" class="btn" style="background: white; color: #667eea; font-size: 14px; padding: 8px 16px;">Detail</a>
      </div>

      <?php if ($weightData['current_weight']): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-top: 16px;">
          <div style="background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">Berat Saat Ini</div>
            <div style="font-size: 28px; font-weight: 700;"><?= number_format($weightData['current_weight'], 1) ?> kg</div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">
              <?= $weightData['last_date'] ? date('d M Y', strtotime($weightData['last_date'])) : '' ?>
            </div>
          </div>

          <div style="background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">Perubahan</div>
            <div style="font-size: 28px; font-weight: 700;">
              <?php if ($weightData['change'] < 0): ?>
                <span style="color: #4ade80;">↓ <?= number_format(abs($weightData['change']), 1) ?> kg</span>
              <?php elseif ($weightData['change'] > 0): ?>
                <span style="color: #fbbf24;">↑ <?= number_format($weightData['change'], 1) ?> kg</span>
              <?php else: ?>
                <span>0 kg</span>
              <?php endif; ?>
            </div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">
              <?= $weightData['change'] != 0 ? ($weightData['change'] > 0 ? '+' : '') . number_format($weightData['percent'], 1) . '%' : 'Sejak awal' ?>
            </div>
          </div>

          <div style="background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">Total Tracking</div>
            <div style="font-size: 28px; font-weight: 700;"><?= $weightData['total_entries'] ?></div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">entries</div>
          </div>
        </div>

        <?php if ($weightData['change'] < 0): ?>
          <?php
            // Calculate BMI for checking underweight
            $h_m = ($uStats['height'] ?? 170) / 100;
            $bmi = $weightData['current_weight'] / ($h_m * $h_m);
          ?>
          <?php if ($bmi < 18.5): ?>
             <div style="margin-top: 16px; padding: 12px; background: rgba(251, 191, 36, 0.2); border-radius: 8px; border: 1px solid rgba(251, 191, 36, 0.3);">
                <strong>⚠️ PERHATIAN:</strong> Berat turun <?= abs($weightData['change']) ?> kg, namun BMI Anda <?= number_format($bmi, 1) ?> (Kurang Ideal). Targetkan asupan <strong><?= isset($tdee) ? number_format($tdee + 300) : 2500 ?> kcal</strong> (Surplus) untuk mencapai berat ideal.
             </div>
          <?php else: ?>
             <div style="margin-top: 16px; padding: 12px; background: rgba(74, 222, 128, 0.2); border-radius: 8px; border: 1px solid rgba(74, 222, 128, 0.3);">
                <strong>🎉 Luar biasa!</strong> Anda telah menurunkan <?= abs($weightData['change']) ?> kg. Pertahankan!
             </div>
          <?php endif; ?>
        <?php elseif ($weightData['change'] > 0): ?>
          <?php 
            $h_m = ($uStats['height'] ?? 170) / 100;
            $bmi = $weightData['current_weight'] / ($h_m * $h_m);
          ?>
          <?php if ($bmi >= 30): ?>
             <div style="margin-top: 16px; padding: 12px; background: rgba(239, 68, 68, 0.25); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.4);">
                <strong>🚨 BAHAYA / OBESITAS:</strong> Berat naik <?= $weightData['change'] ?> kg. BMI: <?= number_format($bmi, 1) ?>. Batasi kalori maksimal <strong><?= isset($dailyTarget) ? $dailyTarget : 1500 ?> kcal</strong> per hari.
             </div>
          <?php else: ?>
             <div style="margin-top: 16px; padding: 12px; background: rgba(251, 191, 36, 0.2); border-radius: 8px; border: 1px solid rgba(251, 191, 36, 0.3);">
                <strong>⚠️ Perhatian:</strong> Berat naik <?= $weightData['change'] ?> kg. Pastikan asupan tidak melebihi <strong><?= isset($dailyTarget) ? $dailyTarget : 2000 ?> kcal</strong>.
             </div>
          <?php endif; ?>
        <?php endif; ?>
      <?php else: ?>
        <div style="text-align: center; padding: 24px 0;">
          <p style="margin-bottom: 16px; opacity: 0.9;">Mulai tracking berat badan Anda untuk melihat progress diet!</p>
          <a href="weight_tracker.php" class="btn" style="background: white; color: #667eea;">Mulai Tracking</a>
        </div>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($goal === 'muscle' && $muscleData): ?>
    <!-- Muscle Gain Tracking Widget (Muscle Building Users Only) -->
    <section class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
        <h3 style="color: white; margin: 0;">💪 Muscle Gain Tracking</h3>
        <a href="weight_tracker.php" class="btn" style="background: white; color: #f5576c; font-size: 14px; padding: 8px 16px;">Detail</a>
      </div>

      <?php if ($muscleData['current_weight']): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-top: 16px;">
          <div style="background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">Berat Saat Ini</div>
            <div style="font-size: 28px; font-weight: 700;"><?= number_format($muscleData['current_weight'], 1) ?> kg</div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">
              <?= $muscleData['last_date'] ? date('d M Y', strtotime($muscleData['last_date'])) : '' ?>
            </div>
          </div>

          <div style="background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">Gain Massa</div>
            <div style="font-size: 28px; font-weight: 700;">
              <?php if ($muscleData['gain'] > 0): ?>
                <span style="color: #4ade80;">+<?= number_format($muscleData['gain'], 1) ?> kg</span>
              <?php elseif ($muscleData['gain'] < 0): ?>
                <span style="color: #fbbf24;"><?= number_format($muscleData['gain'], 1) ?> kg</span>
              <?php else: ?>
                <span>0 kg</span>
              <?php endif; ?>
            </div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">
              <?= $muscleData['gain'] != 0 ? ($muscleData['gain'] > 0 ? '+' : '') . number_format($muscleData['percent'], 1) . '%' : 'Sejak awal' ?>
            </div>
          </div>

          <div style="background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px);">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">Total Tracking</div>
            <div style="font-size: 28px; font-weight: 700;"><?= $muscleData['total_entries'] ?></div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">entries</div>
          </div>
        </div>

        <?php if ($muscleData['gain'] > 0): ?>
          <div style="margin-top: 16px; padding: 12px; background: rgba(74, 222, 128, 0.2); border-radius: 8px; border: 1px solid rgba(74, 222, 128, 0.3);">
            <strong>💪 Excellent!</strong> Anda telah gain <?= $muscleData['gain'] ?> kg massa. Keep bulking!
          </div>
        <?php elseif ($muscleData['gain'] < 0): ?>
          <div style="margin-top: 16px; padding: 12px; background: rgba(251, 191, 36, 0.2); border-radius: 8px; border: 1px solid rgba(251, 191, 36, 0.3);">
            <strong>⚠️ Perhatian:</strong> Berat turun <?= abs($muscleData['gain']) ?> kg. Tingkatkan asupan kalori & protein!
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div style="text-align: center; padding: 24px 0;">
          <p style="margin-bottom: 16px; opacity: 0.9;">Mulai tracking berat badan untuk monitor progress muscle gain!</p>
          <a href="weight_tracker.php" class="btn" style="background: white; color: #f5576c;">Mulai Tracking</a>
        </div>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="card">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Today's Meal Plan</h3>
        <a href="meal_plan.php" class="link">Manage Plan</a>
      </div>
      <?php
        // Fetch today's meal plan
        $planStmt = $db->conn->prepare("
            SELECT mp.meal_type, f.name, mp.servings, f.calories
            FROM meal_plans mp
            JOIN foods f ON f.id = mp.food_id
            WHERE mp.user_id = ? AND mp.plan_date = CURDATE()
            ORDER BY FIELD(mp.meal_type, 'breakfast','lunch','dinner','snack')
        ");
        $planStmt->bind_param("i", $user_id);
        $planStmt->execute();
        $todayPlans = $planStmt->get_result();
      ?>

      <?php if ($todayPlans->num_rows === 0): ?>
        <p class="muted">Belum ada rencana makan hari ini.</p>
        <a href="meal_plan.php" class="btn" style="display:inline-block; margin-top:10px; text-decoration:none;">Buat Plan</a>
      <?php else: ?>
        <ul class="food-list">
          <?php while ($p = $todayPlans->fetch_assoc()): ?>
            <li>
              <div class="food-left">
                <strong><?= htmlspecialchars(ucfirst($p['meal_type'])) ?></strong>: <?= htmlspecialchars($p['name']) ?>
                <span class="muted small">(<?= (float)$p['servings'] ?>x)</span>
              </div>
              <div class="food-right"><?= (int)($p['calories'] * $p['servings']) ?> kcal</div>
            </li>
          <?php endwhile; ?>
        </ul>
        <form method="post" action="apply_plan_to_today.php" style="margin-top:16px;">
            <input type="hidden" name="date" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-block" style="background:var(--success);" type="submit" onclick="return confirm('Salin plan ke log harian?')">Apply to Food Diary</button>
        </form>
      <?php endif; ?>
    </section>

    <section class="card">
      <h3>Today — Food List</h3>
      <?php if ($todayFoods->num_rows === 0): ?>
        <p class="muted">Belum ada makanan tercatat hari ini.</p>
      <?php else: ?>
        <ul class="food-list">
          <?php while ($f = $todayFoods->fetch_assoc()): ?>
            <li>
              <div class="food-left">
                <strong><?= htmlspecialchars($f['food_name']) ?></strong>
                <div class="muted small">P: <?= (float)$f['protein'] ?>g • C: <?= (float)$f['carbs'] ?>g • F: <?= (float)$f['fat'] ?>g</div>
              </div>
              <div class="food-right"><?= (int)$f['calories'] ?> kcal</div>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="card">
      <h3>Nutrition Analysis (Today)</h3>
      <?php if (empty($warnings) && empty($recom)): ?>
        <p class="muted">Belum cukup data untuk analisis.</p>
      <?php else: ?>
        
        <?php if (!empty($warnings)): ?>
            <div style="background: #fff1f2; border: 1px solid #fda4af; border-radius: 16px; padding: 20px; margin-bottom: 20px;">
                <h4 style="color: #be123c; margin: 0 0 12px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">🚨 Perhatian</h4>
                <ul style="margin: 0; padding-left: 20px; color: #9f1239; font-size: 14px; line-height: 1.6;">
                    <?php foreach ($warnings as $w): ?>
                        <li style="margin-bottom: 8px;"><?= $w ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($recom)): ?>
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 16px; padding: 20px;">
                <h4 style="color: #15803d; margin: 0 0 12px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">💡 Rekomendasi (Healthier Options)</h4>
                <ul style="margin: 0; padding-left: 20px; color: #166534; font-size: 14px; line-height: 1.6;">
                    <?php foreach ($recom as $r): ?>
                        <li style="margin-bottom: 8px;"><?= $r ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

      <?php endif; ?>
    </section>

  </main>

<script>
// fetch chart data (chart_data.php must return dates, calories, proteins, carbs, fats arrays)
fetch('chart_data.php')
.then(r => r.json())
.then(d => {
    const ctx = document.getElementById('chartCombined').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: d.dates,
            datasets: [
                { label: 'Kalori (kcal)', data: d.calories, borderColor:'#157AFE', yAxisID:'y', tension:0.2 },
                { label: 'Protein (g)', data: d.proteins, borderColor:'#00A3FF', yAxisID:'y1', tension:0.2 },
                { label: 'Karbo (g)', data: d.carbs, borderColor:'#F6C84C', yAxisID:'y1', tension:0.2 },
                { label: 'Lemak (g)', data: d.fats, borderColor:'#FF6B6B', yAxisID:'y1', tension:0.2 }
            ]
        },
        options: {
            responsive:true,
            interaction:{ mode:'index', intersect:false },
            scales: {
                y: { type:'linear', position:'left', title:{display:true,text:'kcal'} },
                y1: { type:'linear', position:'right', title:{display:true,text:'grams'}, grid:{ drawOnChartArea:false } }
            }
        }
    });
    fetch('recommendation_engine.php').then(r=>r.json()).then(d=>{
  // render suggestions into DOM
});

});
</script>
</body>
</html>
