<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Services\AnalyticsService;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$analyticsService = new AnalyticsService();
$history = $analyticsService->getHistory($user_id);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=nutrition_history_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, ['Date', 'Food Name', 'Calories (kcal)', 'Protein (g)', 'Carbs (g)', 'Fat (g)']);

// Write data rows
foreach ($history as $row) {
    fputcsv($output, [
        $row['date'],
        $row['food_name'],
        $row['calories'],
        $row['protein'],
        $row['carbs'],
        $row['fat']
    ]);
}

fclose($output);
exit;
