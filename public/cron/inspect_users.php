<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Config\Database;

$db = new Database();
$result = $db->conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
