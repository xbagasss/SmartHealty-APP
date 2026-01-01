<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Services\RecommendationService;

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];
$goal = $user['goal'] ?? 'maintain';

$service = new RecommendationService();
$data = $service->getSmartRecommendations($userId, $goal);

echo json_encode($data);
