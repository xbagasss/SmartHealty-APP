<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();

// HANDLE SEEDING REMOVED

$foods = $db->conn->query("SELECT * FROM foods ORDER BY id DESC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Foods — SmartHealthy</title>
  <link rel="stylesheet" href="../dashboard.css">
  <style>
    .food-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
        gap: 20px; 
        margin-top: 24px; 
    }
    .food-item { 
        display: flex; 
        flex-direction: column; 
        height: 100%; 
        border: 1px solid var(--border);
        transition: all 0.2s;
    }
    .food-img { 
        width: 100%; 
        height: 160px; 
        object-fit: cover; 
        border-radius: 12px; 
        background: #f1f5f9; 
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-weight: 500;
        font-size: 14px;
        border: 1px solid #e2e8f0;
    }
    .food-actions { 
        margin-top: auto; 
        display: flex; 
        gap: 8px; 
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }
    .badge {
        display: inline-block;
        padding: 4px 10px;
        background: #eff6ff;
        color: #3b82f6;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 8px;
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="../dashboard.php" class="nav-link">Dashboard</a>
      <a href="../search_nutrition.php" class="nav-link">Search</a>
      <a href="../analytics.php" class="nav-link">Analytics</a>
      <a href="../profile.php" class="nav-link">Profile</a>
      <a href="../logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <section class="welcome card">
      <?php if (isset($_GET['seeded'])): ?>
        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #86efac;">
            ✅ Berhasil menambahkan <?= (int)$_GET['seeded'] ?> menu sehat baru!
        </div>
      <?php endif; ?>

      <div style="display:flex; justify-content:space-between; align-items:center; width:100%; flex-wrap: wrap; gap: 16px;">
        <div>
          <h1>Foods Database</h1>
          <p class="muted">Koleksi makanan dan informasi nutrisi lengkap.</p>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="create.php" class="btn">+ Buat Makanan</a>
        </div>
      </div>
    </section>

    <div class="food-grid">
      <?php while ($row = $foods->fetch_assoc()): ?>
        <div class="card food-item">
            <div class="food-img">
                <?php if (!empty($row['image_url'])): ?>
                    <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 8px;">
                <span class="badge"><?= (int)$row['calories'] ?> kcal</span>
            </div>

            <h3 style="margin:0 0 6px 0; font-size:18px; text-decoration: none; color: inherit;"><?= htmlspecialchars($row['name']) ?></h3>
            <p class="muted" style="font-size:14px; margin:0 0 16px 0; line-height:1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <?= htmlspecialchars($row['description'] ?? 'Tidak ada deskripsi.') ?>
            </p>
            
            <div style="font-size: 13px; color: #64748b; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Protein</span> <strong><?= (float)$row['protein'] ?>g</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                    <span>Carbs</span> <strong><?= (float)$row['carbs'] ?>g</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                    <span>Fat</span> <strong><?= (float)$row['fat'] ?>g</strong>
                </div>
            </div>
            
            <div class="food-actions">
                <a href="add_today.php?id=<?= $row['id'] ?>" class="btn btn-block small" style="flex:2;">+ Log</a>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn small" style="background: white; color: var(--text-muted); border: 1px solid var(--border); flex:1;">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus makanan ini?');" class="btn small danger" style="background: white; color: var(--danger); border: 1px solid #fee2e2; flex:1; padding: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </a>
            </div>
        </div>
      <?php endwhile; ?>
    </div>
  </main>
<script src="../theme_loader.js"></script>
</body>
</html>
