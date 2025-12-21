<?php
require_once __DIR__ . '/vendor/autoload.php';

// Set Timezone to Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Load .env manual
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove inline comments (e.g., KEY=val # comment)
            # handle quotes if needed but simple strip hash is safer for now
            if (strpos($value, '#') !== false) {
                // Ensure the # is not part of a quoted string or password (edge case)
                // For simplicity assuming # starts a comment unless complex parsing
                $parts = explode('#', $value, 2);
                $value = trim($parts[0]);
            }

            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value; // Also populate $_ENV
            $_SERVER[$name] = $value;
        }
    }
}
