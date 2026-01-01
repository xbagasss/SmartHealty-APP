<?php
require_once __DIR__ . '/../../bootstrap.php';

use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

$db = new Database();
$id = intval($_GET['id']);

$db->conn->query("DELETE FROM foods WHERE id = $id");

header("Location: index.php");
exit;
