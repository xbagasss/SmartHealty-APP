<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$db = new Database();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender = $_POST['gender'];
    $age = (int)$_POST['age'];
    $height = (float)$_POST['height'];
    $weight = (float)$_POST['weight'];
    $activity = $_POST['activity_level'];
    $goal = $_POST['goal'];

    if ($age <= 0 || $height <= 0 || $weight <= 0) {
        $error = "Data tidak valid. Harap isi angka yang benar.";
    } else {
        // Update User Profile
        $stmt = $db->conn->prepare("UPDATE users SET gender=?, age=?, height=?, activity_level=?, goal=? WHERE id=?");
        $stmt->bind_param("sidssi", $gender, $age, $height, $activity, $goal, $user['id']);
        
        if ($stmt->execute()) {
            // Log Initial Weight
            // Create table if not exists (safety check)
            $db->conn->query("
                CREATE TABLE IF NOT EXISTS weight_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    weight DECIMAL(5,2) NOT NULL,
                    date DATE NOT NULL,
                    notes VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            
            $date = date('Y-m-d');
            $wStmt = $db->conn->prepare("INSERT INTO weight_logs (user_id, weight, date, notes) VALUES (?, ?, ?, 'Initial setup')");
            $wStmt->bind_param("ids", $user['id'], $weight, $date);
            $wStmt->execute();

            // Refresh Session Data (important!)
            $user['gender'] = $gender;
            $user['age'] = $age;
            $user['height'] = $height;
            $user['activity_level'] = $activity;
            $user['goal'] = $goal;
            $_SESSION['user'] = $user;

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Terjadi kesalahan saat menyimpan data.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Setup Profile — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; }
    .setup-card { width: 100%; max-width: 500px; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .setup-header { text-align: center; margin-bottom: 30px; }
    .setup-header h1 { font-size: 26px; font-weight: 800; color: #1e293b; margin: 0 0 8px 0; }
    .setup-header p { color: #64748b; font-size: 15px; margin: 0; }
    .emoji-icon { font-size: 48px; margin-bottom: 16px; display: block; }
    
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-label { display: block; font-weight: 600; color: #334155; margin-bottom: 8px; font-size: 14px; }
    .form-control { width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 15px; transition: border 0.2s; box-sizing: border-box; }
    .form-control:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    
    .btn-primary { background: #2563eb; color: white; width: 100%; padding: 14px; border: none; border-radius: 10px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.2s; }
    .btn-primary:hover { background: #1d4ed8; }
    
    .row { display: flex; gap: 16px; }
    .col { flex: 1; }
  </style>
</head>
<body>

  <div class="setup-card">
    <div class="setup-header">
      <span class="emoji-icon">👋</span>
      <h1>Halo, <?= htmlspecialchars($user['name']) ?>!</h1>
      <p>Sebelum mulai, yuk lengkapi profil kesehatanmu agar kami bisa menghitung kebutuhan kalorimu dengan akurat.</p>
    </div>

    <?php if ($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:20px; text-align:center;"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="row">
        <div class="col">
           <div class="form-group">
            <label class="form-label">Jenis Kelamin</label>
            <select name="gender" class="form-control" required>
                <option value="male">Laki-laki</option>
                <option value="female">Perempuan</option>
            </select>
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label class="form-label">Umur (Tahun)</label>
            <input type="number" name="age" class="form-control" placeholder="Contoh: 25" min="10" max="100" required>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="form-group">
            <label class="form-label">Tinggi Badan (cm)</label>
            <input type="number" name="height" class="form-control" placeholder="170" required>
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label class="form-label">Berat Badan (kg)</label>
            <input type="number" step="0.1" name="weight" class="form-control" placeholder="65.5" required>
            <small style="color: #64748b; margin-top: 4px; display: block; font-size: 13px;">Contoh: 50 (Berat Ideal)</small>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Tingkat Aktivitas</label>
        <select name="activity_level" class="form-control" required>
            <option value="sedentary">Jarang Olahraga (Sedentary)</option>
            <option value="light">Ringan (1-3 hari/minggu)</option>
            <option value="moderate">Sedang (3-5 hari/minggu)</option>
            <option value="active">Aktif (6-7 hari/minggu)</option>
            <option value="athlete">Sangat Aktif (Atlet/Fisik Berat)</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Apa Goal Kamu?</label>
        <select name="goal" class="form-control" required>
            <option value="maintain">Jaga Berat Badan (Maintain)</option>
            <option value="diet">Turunkan Berat Badan (Fat Loss)</option>
            <option value="muscle">Naikkan Massa Otot (Muscle Gain)</option>
        </select>
      </div>

      <button type="submit" class="btn-primary">Simpan & Masuk Dashboard →</button>
    </form>
  </div>

</body>
</html>
