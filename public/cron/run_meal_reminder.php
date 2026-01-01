<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Services\ReminderService;

$type = isset($_GET['type']) ? $_GET['type'] : (isset($argv[1]) ? $argv[1] : null);

if (!$type || !in_array($type, ['Breakfast', 'Lunch', 'Dinner'])) {
    die("Usage: php run_meal_reminder.php [Breakfast|Lunch|Dinner] or ?type=Breakfast");
}

$service = new ReminderService();
$count = $service->sendMealReminder($type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $type ?> Reminder</title>
    <style>
        body { font-family: system-ui; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0fdf4; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 1rem; text-align: center; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        h1 { color: #166534; margin-top: 0; }
        .stat { font-size: 3rem; font-weight: bold; color: #15803d; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= $type ?> Reminders Sent</h1>
        <div class="stat"><?= $count ?></div>
        <p>Emails sent to users who haven't logged <?= $type ?> yet.</p>
        <a href="javascript:window.close()" style="color: #666;">Close</a>
    </div>
</body>
</html>
