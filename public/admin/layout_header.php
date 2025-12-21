<?php
// Admin Header Component
// Includes session check (already in ensure_admin.php but good to have context)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> — SmartHealthy</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Admin specific overrides if needed */
        .admin-nav-trigger { display: none; }
        @media (max-width: 600px) {
            .admin-nav-trigger { display: block; margin-bottom: 16px; }
        }
        .nav-link.active-admin { background: #e0f2fe; color: #0284c7; }
    </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy <span style="font-size: 12px; background:#ef4444; color:white; padding:2px 6px; border-radius:4px; vertical-align: middle;">ADMIN</span></div>
    <nav>
      <!-- Main Navigation Replica (Adjusted Paths) -->
      <a href="../dashboard.php" class="nav-link">Back to App</a>
      
      <!-- Admin Specific Navigation -->
      <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active-admin' : '' ?>">Overview</a>
      <a href="users.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'users.php' || basename($_SERVER['PHP_SELF']) == 'add_user.php') ? 'active-admin' : '' ?>">Manage Users</a>
      <a href="foods.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'foods.php' || basename($_SERVER['PHP_SELF']) == 'food_form.php') ? 'active-admin' : '' ?>">Foods</a>
      <a href="categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active-admin' : '' ?>">Categories</a>
      <a href="api_logs.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'api_logs.php' ? 'active-admin' : '' ?>">API Logs</a>
      <a href="reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active-admin' : '' ?>">Reports</a>
      
      <a href="../logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
