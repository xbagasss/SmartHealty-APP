<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\MealRecommendationService;

session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$db = new Database();
$user_id = $_SESSION['user']['id'];

// Handle Generate Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_plan') {
    $plan_date = $_POST['plan_date'] ?? date('Y-m-d');
    
    // Auto-load service (ensure composer autoload is working or require manual)
    // require_once __DIR__ . '/../src/Services/MealRecommendationService.php'; // Uncomment if autoloader fails
    
    $service = new MealRecommendationService();
    $service->generateDailyPlan($user_id, $plan_date);
    
    header("Location: meal_plan.php?date=" . urlencode($plan_date) . "&msg=generated");
    exit;
}

// Ensure table exists
$db->conn->query("
    CREATE TABLE IF NOT EXISTS meal_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_date DATE NOT NULL,
        meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
        food_id INT NOT NULL,
        servings DECIMAL(5,2) DEFAULT 1.00,
        notes VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// handle add plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_plan') {
    $plan_date = $_POST['plan_date'] ?? date('Y-m-d');
    $meal_type = $_POST['meal_type'] ?? 'lunch';
    $food_id = (int)($_POST['food_id'] ?? 0);
    $servings = (float)($_POST['servings'] ?? 1);
    $notes = substr(trim($_POST['notes'] ?? ''), 0, 500);

    if ($food_id > 0) {
        $ins = $db->conn->prepare("
            INSERT INTO meal_plans (user_id, plan_date, meal_type, food_id, servings, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param("issids", $user_id, $plan_date, $meal_type, $food_id, $servings, $notes);
        $ins->execute();
        header("Location: meal_plan.php?date=" . urlencode($plan_date));
        exit;
    }
}

// handle remove plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $d = $db->conn->prepare("DELETE FROM meal_plans WHERE id = ? AND user_id = ?");
        $d->bind_param("ii", $id, $user_id);
        $d->execute();
    }
    header("Location: meal_plan.php?date=" . urlencode($_POST['return_date']));
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');

// foods list for selector
$foodsRes = $db->conn->query("SELECT id, name, calories FROM foods ORDER BY name LIMIT 1000");

// fetch plans for this user/date
$stmt = $db->conn->prepare("
    SELECT mp.id, mp.meal_type, mp.servings, mp.notes, f.id AS fid, f.name, f.calories
    FROM meal_plans mp
    JOIN foods f ON f.id = mp.food_id
    WHERE mp.user_id = ? AND mp.plan_date = ?
    ORDER BY FIELD(mp.meal_type, 'breakfast','lunch','dinner','snack'), mp.id
");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$plansArray = [];
$totalCalories = 0;
while ($row = $result->fetch_assoc()) {
    $plansArray[$row['meal_type']][] = $row;
    $totalCalories += ($row['calories'] * $row['servings']);
}

// Get User TDEE for comparison (Quick fetch)
$tdee = 2000; // Default
$uStmt = $db->conn->prepare("SELECT gender, age, height, activity_level, goal, (SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date DESC LIMIT 1) as weight FROM users WHERE id = ?");
$uStmt->bind_param("ii", $user_id, $user_id);
$uStmt->execute();
$uStats = $uStmt->get_result()->fetch_assoc();

if ($uStats) {
    // Calc TDEE (Same logic as service, simplified here for UI display or use service if possible, but raw calculation is fast)
    $weight = $uStats['weight'] ?? 60;
    $bmr = (10 * $weight) + (6.25 * ($uStats['height']??170)) - (5 * ($uStats['age']??25));
    $bmr += ($uStats['gender'] === 'female') ? -161 : 5;
    $act = $uStats['activity_level'] ?? 'moderate';
    $mult = ['sedentary'=>1.2, 'light'=>1.375, 'moderate'=>1.55, 'active'=>1.725, 'athlete'=>1.9];
    $tdee = $bmr * ($mult[$act] ?? 1.55);

    // Apply Goal Adjustment
    $goal = $uStats['goal'] ?? 'maintain';
    if ($goal === 'diet') $tdee -= 500;
    if ($goal === 'muscle') $tdee += 400;
    if ($tdee < 1200) $tdee = 1200;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Meal Planner — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .grid-col-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) { .grid-col-2 { grid-template-columns: 1fr; } }
    .plan-list { margin-top: 10px; }
    .plan-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed #e2e8f0; }
    .plan-item:last-child { border-bottom: none; }
    .btn.small { padding: 4px 8px; font-size: 12px; }
    .btn.danger { background: var(--danger); }
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
        <h1>Meal Planner</h1>
        <p class="muted">Rencanakan makanan Anda untuk mencapai target nutrisi.</p>
      </div>
  </section>

  <div class="grid-col-2" style="margin-top: 20px;">
    <div class="card">
      <div class="welcome" style="margin-bottom: 24px;">
        <div>
            <h3>Tambah ke Plan</h3>
            <p class="muted">Jadwalkan makanan untuk hari ini atau esok.</p>
        </div>
      </div>
      
      <form method="post">
        <input type="hidden" name="action" value="add_plan">
        
        <div class="form-group">
            <label class="form-label">Tanggal</label>
            <input type="date" name="plan_date" class="form-control" value="<?= htmlspecialchars($date) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Waktu Makan</label>
            <select name="meal_type" class="form-control">
                <option value="breakfast">Breakfast</option>
                <option value="lunch" selected>Lunch</option>
                <option value="dinner">Dinner</option>
                <option value="snack">Snack</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Makanan</label>
            <select name="food_id" class="form-control" required>
                <option value="">-- Pilih Makanan --</option>
                <?php while($f = $foodsRes->fetch_assoc()): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?> (<?= (int)$f['calories'] ?> kcal)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Porsi (Servings)</label>
                <input type="number" step="0.25" name="servings" class="form-control" value="1">
            </div>
            <div class="form-group">
                <label class="form-label">Catatan (Opsional)</label>
                <input type="text" name="notes" class="form-control" maxlength="500" placeholder="Contoh: Tanpa sambal">
            </div>
        </div>

        <button class="btn btn-block" type="submit">Tambahkan ke Plan</button>
      </form>
    </div>

    <!-- Auto Generate Section -->
    <div class="card" style="margin-top: 20px; background: linear-gradient(to right, #f0fdf4, #e0f2fe);">
        <div style="margin-bottom: 12px;">
            <h3 style="color: #047857;">Bingung mau makan apa?</h3>
            <p class="muted" style="color: #064e3b;">Dapatkan rekomendasi menu harian yang sehat dan sesuai kebutuhan kalorimu!</p>
        </div>
            <a href="generate_plan_preview.php?date=<?= htmlspecialchars($date) ?>" class="btn btn-block" style="background: #059669; display:block; text-align:center; padding: 12px; text-decoration:none;">
                ✨ Buat Rekomendasi Otomatis
            </a>
    </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <div>
                    <h3>Plan: <?= date('d M Y', strtotime($date)) ?></h3>
                    <div class="small muted"> Target: <?= number_format($tdee) ?> kcal • Planned: <?= number_format($totalCalories) ?> kcal</div>
                </div>
                <form method="get" style="margin:0;">
                    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()" style="padding:8px; border-radius:8px; border:1px solid #ccc; font-family: inherit;">
                </form>
            </div>

            <!-- Progress Bar -->
            <?php 
                $percent = ($totalCalories / $tdee) * 100; 
                $color = $percent > 110 ? '#ef4444' : ($percent < 90 ? '#f59e0b' : '#10b981');
            ?>
            <div style="background: #e2e8f0; border-radius: 99px; height: 8px; width: 100%; margin-bottom: 24px; overflow: hidden;">
                <div style="background: <?= $color ?>; width: <?= min($percent, 100) ?>%; height: 100%;"></div>
            </div>

            <?php if (empty($plansArray)): ?>
                <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
                    <p class="muted" style="margin-bottom: 12px;">Belum ada rencana makan.</p>
                </div>
            <?php else: ?>
                <?php 
                $mealOrder = ['breakfast', 'lunch', 'dinner', 'snack'];
                $mealLabels = [
                    'breakfast' => ['label' => 'Makan Pagi', 'icon' => '🌅', 'color' => '#ca8a04', 'bg' => '#fef9c3'],
                    'lunch' => ['label' => 'Makan Siang', 'icon' => '☀️', 'color' => '#0284c7', 'bg' => '#e0f2fe'],
                    'dinner' => ['label' => 'Makan Malam', 'icon' => '🌙', 'color' => '#be185d', 'bg' => '#fdf2f8'],
                    'snack' => ['label' => 'Camilan', 'icon' => '🍿', 'color' => '#15803d', 'bg' => '#f0fdf4']
                ];
                ?>

                <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($mealOrder as $mType): ?>
                    <?php if (!empty($plansArray[$mType])): ?>
                        <?php $style = $mealLabels[$mType]; ?>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-weight: 700; color: #334155;">
                                <span style="background: <?= $style['bg'] ?>; padding: 6px; border-radius: 8px;"><?= $style['icon'] ?></span>
                                <?= $style['label'] ?>
                            </div>
                            
                            <div style="display: grid; gap: 12px;">
                                <?php foreach ($plansArray[$mType] as $p): ?>
                                    <div style="
                                        display: flex; justify-content: space-between; align-items: center;
                                        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px;
                                        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                                    ">
                                        <div>
                                            <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="small muted" style="margin-top: 2px;">
                                                <?= (float)$p['servings'] ?> porsi • <?= (int)($p['calories'] * $p['servings']) ?> kcal
                                                <?php if($p['notes']): ?><span style="margin-left:8px; font-style: italic;">(<?= htmlspecialchars($p['notes']) ?>)</span><?php endif; ?>
                                            </div>
                                        </div>
                                        <form method="post" style="margin:0;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $p['id']?>">
                                            <input type="hidden" name="return_date" value="<?= htmlspecialchars($date) ?>">
                                            <button type="submit" onclick="return confirm('Hapus item ini?')" style="
                                                background: #fee2e2; color: #ef4444; border: none; 
                                                width: 32px; height: 32px; border-radius: 8px; 
                                                display: flex; align-items: center; justify-content: center;
                                                cursor: pointer; transition: background 0.2s;
                                            ">&times;</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Action</h3>
            <p class="muted small">Salin semua rencana hari ini ke Food Diary (Log Harian).</p>
            <a href="apply_plan_to_today.php?date=<?= htmlspecialchars($date) ?>" class="btn btn-block" style="background:var(--success); display:block; text-align:center; padding: 12px; text-decoration:none;">Apply to Food Diary</a>
        </div>
    </div>
  </div>
</main>
<script src="theme_loader.js"></script>
</body>
</html>
