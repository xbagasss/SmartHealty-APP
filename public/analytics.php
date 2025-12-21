<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Services\AnalyticsService;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$analyticsService = new AnalyticsService();
$user = $_SESSION['user'];
$user_id = $user['id'];

// Fetch data using service
$data = $analyticsService->getWeeklyTotals($user_id);
$top = $analyticsService->getTopFoods($user_id);
$history = $analyticsService->getHistory($user_id);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Analytics — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
    .history-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .history-table th, .history-table td { padding: 10px; border-bottom: 1px solid #f0f2f6; text-align: left; }
    .history-table th { background: var(--soft); font-weight: 600; color: var(--blue); }
    .list-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #f0f2f6; }
    .list-item:last-child { border-bottom: none; }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="search_nutrition.php" class="nav-link">Search</a>
      <a href="analytics.php" class="nav-link" style="color:var(--blue); font-weight:700;">Analytics</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <section class="welcome card">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Analytics & Insights</h1>
            <p class="muted">Ringkasan pola makan dan tren nutrisi Anda minggu ini.</p>
        </div>
        <a href="export_csv.php" class="btn" style="background:#0284c7; color:white; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Download CSV
        </a>
      </div>
    </section>

    <div class="grid-2" style="margin-top: 16px;">
      <div class="card">
        <h3>Weekly Calories Chart</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="calChart"></canvas>
        </div>
      </div>

      <div class="card">
        <h3>Macro Chart (Protein / Karbo / Lemak)</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="macroChart"></canvas>
        </div>
      </div>
    </div>

    <div class="grid-2" style="margin-top: 16px;">
      <div class="card">
        <h3>Weekly Macro Distribution</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="pieChart"></canvas>
        </div>
      </div>

      <div class="card">
        <h3>Most Eaten Foods</h3>
        <div class="food-list">
          <?php if (!empty($top)): ?>
            <?php foreach ($top as $t): ?>
                <div class="list-item">
                    <strong><?= htmlspecialchars($t['food_name']) ?></strong>
                    <span class="muted"><?= $t['total'] ?>x</span>
                </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="muted">Belum ada data makanan.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <section class="card" style="margin-top: 16px;">
        <h3>Insight Mingguan</h3>
        <p class="muted small" style="margin-bottom: 16px;">Dihitung otomatis dari rata-rata data makro</p>
        
        <?php
        $ins = [];
        if (!empty($data)) {
            $avgCal = array_sum(array_column($data, 'cal')) / count($data);
            if ($avgCal > 2300) $ins[] = "⚠ Kalori harian tinggi. Coba kurangi gorengan & minuman manis.";
            if ($avgCal < 1500) $ins[] = "⚠ Kalori terlalu rendah. Tambahkan makanan bernutrisi.";
        }
        ?>

        <?php if (empty($data)): ?>
            <div class="alert" style="background:#f1f5f9; color:#64748b; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                ℹ️ Belum cukup data untuk analisis. Yuk mulai catat makananmu!
            </div>
        <?php elseif (empty($ins)): ?>
            <div class="alert" style="background:#d1fae5; color:#065f46; padding:12px; border-radius:8px;">
                ✅ Pola makan stabil, lanjutkan!
            </div>
        <?php else: ?>
            <div class="alert" style="background:#fef2f2; color:#ef4444; padding:12px; border-radius:8px;">
                <?php foreach ($ins as $insight): ?>
                    <p><?= $insight ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card" style="margin-top:16px;">
        <h3>History (Last 20 Entries)</h3>
        <?php if (empty($history)): ?>
            <p class="muted">Tidak ada riwayat makanan.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Makanan</th>
                            <th style="text-align:right;">Kalori</th>
                            <th style="text-align:right;">P</th>
                            <th style="text-align:right;">C</th>
                            <th style="text-align:right;">F</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['date']) ?></td>
                                <td><?= htmlspecialchars($h['food_name']) ?></td>
                                <td style="text-align:right;"><?= $h['calories'] ?></td>
                                <td style="text-align:right;"><?= $h['protein'] ?></td>
                                <td style="text-align:right;"><?= $h['carbs'] ?></td>
                                <td style="text-align:right;"><?= $h['fat'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
  </main>

<script>
    const dates = <?= json_encode(array_column($data, 'date')) ?>;
    const cal = <?= json_encode(array_column($data, 'cal')) ?>;
    const p = <?= json_encode(array_column($data, 'p')) ?>;
    const c = <?= json_encode(array_column($data, 'c')) ?>;
    const f = <?= json_encode(array_column($data, 'f')) ?>;

    // Calories Chart
    new Chart(document.getElementById('calChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Kalori (kcal)',
                data: cal,
                borderColor: '#157AFE',
                backgroundColor: 'rgba(21, 122, 254, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Macro chart
    new Chart(document.getElementById('macroChart'), {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                { label: 'Protein', data: p, backgroundColor: '#00A3FF' },
                { label: 'Karbo', data: c, backgroundColor: '#F6C84C' },
                { label: 'Lemak', data: f, backgroundColor: '#FF6B6B' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { x: { stacked: true }, y: { stacked: true } }
        }
    });

    // Pie Chart
    const totalP = p.reduce((a, b) => a + Number(b), 0);
    const totalC = c.reduce((a, b) => a + Number(b), 0);
    const totalF = f.reduce((a, b) => a + Number(b), 0);

    new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Protein', 'Karbs', 'Lemak'],
            datasets: [{
                data: [totalP, totalC, totalF],
                backgroundColor: ['#00A3FF', '#F6C84C', '#FF6B6B'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'right' } 
            }
        }
    });
</script>
<script src="theme_loader.js"></script>
</body>
</html>
