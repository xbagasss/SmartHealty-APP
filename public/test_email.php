<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Services\NotificationService;

header('Content-Type: text/plain');

echo "=== EMAIL CONFIG DIAGNOSTIC ===\n";

$host = getenv('SMTP_HOST');
$user = getenv('SMTP_USER');
$pass = getenv('SMTP_PASS');

echo "SMTP_HOST: [" . $host . "]\n";
echo "SMTP_USER: [" . $user . "]\n";
echo "SMTP_PASS: [" . (strlen($pass) > 0 ? substr($pass, 0, 3) . '***' . substr($pass, -3) : 'EMPTY') . "]\n";
echo "Pass Length: " . strlen($pass) . "\n";

echo "\nNote: Checking for common .env parsing errors...\n";
if (strpos($pass, '#') !== false) {
    echo "WARNING: Password contains '#'. Comments in .env might be included in the value.\n";
}
if (strpos($pass, ' ') !== false) {
    echo "WARNING: Password contains spaces. Leading/trailing spaces might be included.\n";
}

echo "\n=== SENDING TEST EMAIL ===\n";

$notification = new NotificationService();
$to = 'test@example.com'; // We will just test if it connects, the address might bounce but that's a later stage
if ($user && filter_var($user, FILTER_VALIDATE_EMAIL)) {
    $to = $user; // Try sending to self
}

echo "Attempting to send details to: $to\n";

$result = $notification->sendEmail($to, 'Test Email SmartHealthy', 'If you read this, email is working!', true);

if ($result) {
    echo "\nSUCCESS: Email sent successfully!\n";
} else {
    echo "\nFAILURE: Email sending failed.\n";
    echo "Error Message: " . $notification->getLastError() . "\n";
}
