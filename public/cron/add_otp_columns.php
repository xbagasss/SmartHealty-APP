<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Config\Database;

$db = new Database();

// Add columns if not exist
$columns = [
    "ADD COLUMN otp_code VARCHAR(6) NULL AFTER activity_level",
    "ADD COLUMN otp_expires_at DATETIME NULL AFTER otp_code",
    "ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER otp_expires_at"
];

foreach ($columns as $colSql) {
    try {
        $sql = "ALTER TABLE users " . $colSql;
        if ($db->conn->query($sql) === TRUE) {
            echo "Executed: $colSql\n";
        } else {
            echo "Error/Exists: " . $db->conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
}

// Update existing users to verified
$db->conn->query("UPDATE users SET is_verified = 1 WHERE is_verified = 0");
echo "Existing users marked as verified.\n";
