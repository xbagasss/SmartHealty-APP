<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Services\ReminderService;

$service = new ReminderService();
$count = $service->sendDailyReminders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Reminders Check</title>
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #1e293b;
        }
        .card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
            border: 1px solid #e2e8f0;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 16px;
            display: inline-block;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #0f172a;
        }
        p {
            margin: 0 0 24px 0;
            color: #64748b;
            line-height: 1.5;
        }
        .stat {
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 32px;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #dbeafe;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: white;
            color: #64748b;
            border: 1px solid #cbd5e1;
            margin-top: 12px;
        }
        .btn-secondary:hover {
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h1>Daily Reminders Sent</h1>
        <p>System has successfully checked all users and sent email reminders to those who haven't logged food today.</p>
        
        <div class="stat">
            <?= $count ?> <span style="font-size: 16px; font-weight: 500; color: #60a5fa;">Emails Sent</span>
        </div>

        <a href="../admin/dashboard.php" class="btn">Back to Dashboard</a>
        <a href="javascript:window.close()" class="btn btn-secondary">Close Window</a>
    </div>
</body>
</html>
