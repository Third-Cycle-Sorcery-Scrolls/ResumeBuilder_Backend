<?php

require_once __DIR__ . '/../models/ActivityLog.php';

class ActivityLogger {

    public static function log($conn, $user_id, $action, $entity_type, $entity_id = null, $metadata = null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        try {
            $activityLog = new ActivityLog($conn);
            $activityLog->insert(
                $user_id,
                $action,
                $entity_type,
                $entity_id,
                $metadata,
                $ip_address,
                $user_agent
            );
        } catch (Exception $e) {
            // fallback to file if DB fails
            $logDir  = __DIR__ . '/../logs';
            $logFile = $logDir . '/activity.log';
            $date    = date('Y-m-d H:i:s');

            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            $line = "[$date] user:$user_id | action:$action | entity:$entity_type | id:$entity_id | ip:$ip_address\n";
            file_put_contents($logFile, $line, FILE_APPEND);
        }
    }
}