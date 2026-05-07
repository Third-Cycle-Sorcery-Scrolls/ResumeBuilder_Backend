<?php

function logError($pdo, $message, $file = null, $line = null, $user_id = null) {
    $endpoint = $_SERVER['REQUEST_URI'] ?? 'unknown';
    $method   = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $date     = date('Y-m-d H:i:s');

    // Try DB logging
    try {
        if ($pdo) {
            $stmt = $pdo->prepare("
                INSERT INTO error_logs (message, file, line, user_id, endpoint, method)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$message, $file, $line, $user_id, $endpoint, $method]);
            return;
        }
    } catch (Exception $e) {
        // fallback handled below
    }

    // File fallback
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $logFile = $logDir . '/error.log';

    $logMessage = "[$date] $message | File: $file | Line: $line | User: $user_id | Endpoint: $endpoint | Method: $method\n";

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}