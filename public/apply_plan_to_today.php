<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$user_id = $_SESSION['user']['id'];
$date = $_REQUEST['date'] ?? date('Y-m-d');
$user = $_SESSION['user']; // For header/layout if needed

// Handle POST request (Process the form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_items'])) {
    $selected_items = $_POST['items'] ?? [];
    
    if (empty($selected_items)) {
        // No items selected, just redirect back
        header("Location: dashboard.php");
        exit;
    }

    $ins = $db->conn->prepare("
        INSERT INTO nutrition_logs (user_id, food_id, food_name, calories, protein, carbs, fat, date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $count = 0;
    foreach ($selected_items as $food_id => $val) {
        // Fetch specific food details from the hidden inputs or re-query
        // Ideally re-query for security, but we trust the plan data for now or get from hidden fields.
        // Let's re-query from meal_plans to ensure data integrity
        
        $planStmt = $db->conn->prepare("
            SELECT f.id, f.name, f.calories, f.protein, f.carbs, f.fat, mp.servings
            FROM meal_plans mp
            JOIN foods f ON f.id = mp.food_id
            WHERE mp.user_id = ? AND mp.plan_date = ? AND mp.food_id = ?
        ");
        $planStmt->bind_param("isi", $user_id, $date, $food_id);
        $planStmt->execute();
        $item = $planStmt->get_result()->fetch_assoc();

        if ($item) {
            $checkStmt = $db->conn->prepare("SELECT id FROM nutrition_logs WHERE user_id = ? AND food_id = ? AND date = ?");
            $checkStmt->bind_param("iis", $user_id, $item['id'], $date);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                // Skip duplicate
                continue;
            }

             $servings = $item['servings'];
             
             $cal = $item['calories'] * $servings;
             $pro = $item['protein'] * $servings;
             $car = $item['carbs'] * $servings;
             $fat = $item['fat'] * $servings;
             
             $ins->bind_param("iisiddds", 
                $user_id, 
                $item['id'], 
                $item['name'], 
                $cal, 
                $pro, 
                $car, 
                $fat, 
                $date
            );
            
            if ($ins->execute()) {
                $count++;
            }
        }
    }
    
    $skipped = count($selected_items) - $count;
    $msg = "$count items added to your diary.";
    if ($skipped > 0) {
        $msg .= " ($skipped duplicates skipped)";
        if ($count === 0) {
            $_SESSION['flash_error'] = "Semua item yang dipilih sudah ada di diary hari ini.";
        } else {
             $_SESSION['flash_success'] = $msg;
        }
    } else {
        $_SESSION['flash_success'] = $msg;
    }
    
    header("Location: dashboard.php");
    exit;
}

// Handle GET request (Display the selection form)
// Fetch plans for the date
$stmt = $db->conn->prepare("
    SELECT mp.food_id, mp.servings, mp.meal_type, f.name, f.calories, f.protein, f.carbs, f.fat
    FROM meal_plans mp
    JOIN foods f ON f.id = mp.food_id
    WHERE mp.user_id = ? AND mp.plan_date = ?
    ORDER BY FIELD(mp.meal_type, 'breakfast','lunch','dinner','snack')
");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$res = $stmt->get_result();

$plans = [];
while ($r = $res->fetch_assoc()) {
    $plans[] = $r;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Apply Plan — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .selection-list { list-style: none; padding: 0; }
    .selection-item { 
        display: flex; align-items: center; gap: 12px; 
        padding: 12px; border: 1px solid #e5e7eb; margin-bottom: 8px; border-radius: 8px; 
        background: white;
    }
    .selection-item:hover { border-color: #cbd5e1; }
    .checkbox { width: 20px; height: 20px; cursor: pointer; }
    .item-details { flex: 1; }
    .item-meta { font-size: 13px; color: #64748b; }
    .meal-badge { 
        display: inline-block; padding: 2px 8px; border-radius: 4px; 
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        background: #f1f5f9; color: #475569; margin-right: 6px;
    }
  </style>
</head>
<body>
  <div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card">
      <h2>Apply Meal Plan</h2>
      <p class="muted">Select the items you want to add to your Food Diary for <strong><?= htmlspecialchars($date) ?></strong>.</p>
      
      <?php if (empty($plans)): ?>
        <div style="padding: 40px; text-align: center; color: #64748b;">
            <p>No meal plan found for this date.</p>
            <a href="meal_plan.php" class="btn">Create Meal Plan</a>
        </div>
      <?php else: ?>
        <form method="post">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
            <ul class="selection-list">
                <?php foreach ($plans as $p): ?>
                <li class="selection-item">
                    <input type="checkbox" name="items[<?= $p['food_id'] ?>]" value="1" class="checkbox" checked>
                    <div class="item-details">
                        <div>
                            <span class="meal-badge"><?= htmlspecialchars($p['meal_type']) ?></span>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                        </div>
                        <div class="item-meta">
                            <?= (float)$p['servings'] ?> serving(s) • <?= (int)($p['calories'] * $p['servings']) ?> kcal
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <button type="submit" name="apply_items" class="btn" style="background: var(--success); flex: 1;">Confirm & Apply</button>
                <a href="dashboard.php" class="btn" style="background: #f1f5f9; color: #475569;">Cancel</a>
            </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
