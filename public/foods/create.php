<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = $_POST['name'];
    $desc    = $_POST['description'];
    $cal     = (int)$_POST['calories'];
    $protein = (float)$_POST['protein'];
    $carbs   = (float)$_POST['carbs'];
    $fat     = (float)$_POST['fat'];
    $userId  = $_SESSION['user']['id'];

    // 1. Simpan ke tabel foods
    $stmt = $db->conn->prepare("
        INSERT INTO foods (name, description, calories, protein, carbs, fat, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiddii", $name, $desc, $cal, $protein, $carbs, $fat, $userId);

    if ($stmt->execute()) {
        $foodId = $stmt->insert_id;


        // Determine Meal Type
        $hour = (int)date('H');
        $mealType = 'Snack';
        if ($hour >= 4 && $hour < 11) {
            $mealType = 'Breakfast';
        } elseif ($hour >= 11 && $hour < 15) {
            $mealType = 'Lunch';
        } elseif ($hour >= 15 && $hour < 21) {
            $mealType = 'Dinner';
        }

        // 2. AUTO INSERT ke nutrition_logs
        $log = $db->conn->prepare("
            INSERT INTO nutrition_logs
            (user_id, food_id, food_name, calories, protein, carbs, fat, meal_type, date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
        ");

        $log->bind_param("iisiddds",
            $userId,
            $foodId,
            $name,
            $cal,
            $protein,
            $carbs,
            $fat,
            $mealType
        );

        $log->execute();

        header("Location: index.php");
        exit;

    } else {
        $msg = "Gagal menyimpan makanan.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tambah Food — SmartHealthy</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>

  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="../dashboard.php" class="nav-link">Dashboard</a>
      <a href="../foods/index.php" class="nav-link" style="color:var(--blue); font-weight:700;">Foods</a>
      <a href="../logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2>Tambah Makanan Baru</h2>
            <a href="index.php" class="link">Kembali</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert" style="background:#fef2f2; color:#ef4444; padding:12px; border-radius:8px; margin-bottom:16px;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label class="form-label">Nama Makanan</label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Nasi Goreng Spesial">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi (Opsional)</label>
                <textarea name="description" class="form-control" placeholder="Keterangan singkat..." style="min-height:80px;"></textarea>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Kalori (kcal)</label>
                    <input type="number" name="calories" class="form-control" required placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Protein (g)</label>
                    <input type="number" step="0.1" name="protein" class="form-control" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Karbohidrat (g)</label>
                    <input type="number" step="0.1" name="carbs" class="form-control" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Lemak (g)</label>
                    <input type="number" step="0.1" name="fat" class="form-control" placeholder="0">
                </div>
            </div>

            <button type="submit" class="btn btn-block">Simpan & Log Hari Ini</button>
        </form>
    </div>
  </main>

</body>
</html>
