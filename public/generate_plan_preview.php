<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\MealRecommendationService;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$user_id = $_SESSION['user']['id'];
$date = $_REQUEST['date'] ?? date('Y-m-d');

// If 'save' action is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_plan'])) {
    $date = $_POST['date'];
    $selected_items = $_POST['items'] ?? []; // Array of indices or food_ids
    
    // We need to reconstruct the plan items securely. 
    // Since we don't want to trust hidden fields entirely for data integrity, 
    // we could re-generate or just trust the 'food_id' and 'servings' passed from form if we output them explicitly.
    // For simplicity and to match the "Selection" feature:
    // We'll trust the hidden inputs for food_id, meal_type, servings, notes which we will render in the form.
    
    // 1. Delete existing plan for this date
    $del = $db->conn->prepare("DELETE FROM meal_plans WHERE user_id = ? AND plan_date = ?");
    $del->bind_param("is", $user_id, $date);
    $del->execute();

    // 2. Insert new selected items
    $ins = $db->conn->prepare("INSERT INTO meal_plans (user_id, plan_date, meal_type, food_id, servings, notes) VALUES (?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    if (isset($_POST['plan_data'])) {
        foreach ($_POST['plan_data'] as $index => $data) {
            // Check if this index was selected
            if (isset($selected_items[$index])) {
                $meal_type = $data['meal_type'];
                $food_id = (int)$data['food_id'];
                $servings = (float)$data['servings'];
                $notes = $data['notes'];
                
                $ins->bind_param("issids", $user_id, $date, $meal_type, $food_id, $servings, $notes);
                $ins->execute();
                $count++;
            }
        }
    }
    
    header("Location: meal_plan.php?date=" . urlencode($date) . "&msg=generated");
    exit;
}

// Generate the recommendation (Preview)
$service = new MealRecommendationService();
$recommendations = $service->getDailyRecommendation($user_id);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Preview Recommendation — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .item-card {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 16px; border: 1px solid #e2e8f0; margin-bottom: 12px;
        border-radius: 12px; background: white;
    }
    .badge {
        display: inline-block; padding: 4px 8px; border-radius: 6px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        background: #f1f5f9; color: #475569;
    }
  </style>
</head>
<body>
  <div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card">
      <h2>Rekomendasi Menu</h2>
      <p class="muted">Berikut adalah rekomendasi menu untuk tanggal <strong><?= htmlspecialchars($date) ?></strong>.</p>
      
      <div style="background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
        ⚠️ <strong>Perhatian:</strong> Menyimpan rekomendasi ini akan <strong>menghapus</strong> meal plan yang sudah ada untuk tanggal ini.
      </div>

      <form method="post">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
        <input type="hidden" name="save_plan" value="1">
        
        <?php foreach ($recommendations as $idx => $item): ?>
            <div class="item-card">
                <input type="checkbox" name="items[<?= $idx ?>]" value="1" checked style="margin-top: 4px; width: 18px; height: 18px;">
                
                <!-- Hidden fields to pass data -->
                <input type="hidden" name="plan_data[<?= $idx ?>][meal_type]" value="<?= htmlspecialchars($item['meal_type']) ?>">
                <input type="hidden" name="plan_data[<?= $idx ?>][food_id]" value="<?= $item['food_id'] ?>">
                <input type="hidden" name="plan_data[<?= $idx ?>][servings]" value="<?= $item['servings'] ?>">
                <input type="hidden" name="plan_data[<?= $idx ?>][notes]" value="<?= htmlspecialchars($item['notes']) ?>">
                
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="badge"><?= htmlspecialchars($item['meal_type']) ?></span>
                        <span style="font-weight: 700; color: #059669;"><?= (int)$item['total_calories'] ?> kcal</span>
                    </div>
                    <div style="font-weight: 600; font-size: 16px; margin-bottom: 4px;"><?= htmlspecialchars($item['food_name']) ?></div>
                    <div class="muted" style="font-size: 13px;">
                        <?= (float)$item['servings'] ?> porsi
                        <?php if($item['notes']): ?> • <span style="font-style: italic;"><?= htmlspecialchars($item['notes']) ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn" style="background: #059669; flex: 1;">✅ Simpan Plan</button>
            <a href="meal_plan.php?date=<?= htmlspecialchars($date) ?>" class="btn" style="background: #f1f5f9; color: #475569;">Batal</a>
        </div>
      </form>

    </div>
  </div>
</body>
</html>
