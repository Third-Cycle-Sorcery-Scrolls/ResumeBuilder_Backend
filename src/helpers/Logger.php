<?php

/**
 * Centralized Logger — writes JSON Lines to /storage/logs/{category}/{date}.log
 *
 * Usage:
 *   Logger::info('AUTH', 'AUTH_LOGIN', 'User logged in', ['user_id' => 5]);
 *   Logger::error('ERROR', 'PDF_FAIL', 'PDF generation failed', ['file' => 'resume.pdf']);
 *   Logger::security('SECURITY', 'SECURITY_BRUTE_FORCE', 'Too many failed logins');
 */
class Logger {

    // ── Log levels ──────────────────────────────────────────────────────────
    const LEVEL_INFO     = 'INFO';
    const LEVEL_WARNING  = 'WARNING';
    const LEVEL_ERROR    = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';

    // ── Categories ──────────────────────────────────────────────────────────
    const CAT_AUTH     = 'AUTH';
    const CAT_RESUME   = 'RESUME';
    const CAT_ERROR    = 'ERROR';
    const CAT_SECURITY = 'SECURITY';
    const CAT_SYSTEM   = 'SYSTEM';

    /** Base directory for log files (relative to this file's location) */
    private static string $baseDir = __DIR__ . '/../storage/logs';

    // ── Public convenience methods ──────────────────────────────────────────

    public static function info(string $category, string $action, string $message, array $metadata = []): void {
        self::write(self::LEVEL_INFO, $category, $action, $message, $metadata);
    }

    public static function warning(string $category, string $action, string $message, array $metadata = []): void {
        self::write(self::LEVEL_WARNING, $category, $action, $message, $metadata);
    }

    public static function error(string $category, string $action, string $message, array $metadata = []): void {
        self::write(self::LEVEL_ERROR, $category, $action, $message, $metadata);
    }

    public static function critical(string $category, string $action, string $message, array $metadata = []): void {
        self::write(self::LEVEL_CRITICAL, $category, $action, $message, $metadata);
    }

    /** Shorthand for security events */
    public static function security(string $action, string $message, array $metadata = []): void {
        self::write(self::LEVEL_WARNING, self::CAT_SECURITY, $action, $message, $metadata);
    }

    // ── Core write method ───────────────────────────────────────────────────

    /**
     * Build a log entry and append it to the appropriate file.
     * Automatically captures: timestamp, IP, user-agent, URL, HTTP method.
     */
    public static function write(
        string $level,
        string $category,
        string $action,
        string $message,
        array  $metadata = []
    ): void {
        // Sanitize inputs — prevent log injection via newlines
        $message = str_replace(["\n", "\r"], ' ', $message);
        $action  = str_replace(["\n", "\r"], ' ', $action);

        $entry = [
            'timestamp'  => gmdate('Y-m-d\TH:i:s\Z'),
            'level'      => strtoupper($level),
            'category'   => strtoupper($category),
            'action'     => strtoupper($action),
            'message'    => $message,
            'user_id'    => self::resolveUserId($metadata),
            'ip_address' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_url'=> ($_SERVER['REQUEST_URI'] ?? null),
            'http_method'=> ($_SERVER['REQUEST_METHOD'] ?? null),
            'metadata'   => self::sanitizeMetadata($metadata),
        ];

        $logFile = self::resolveLogFile($category);
        self::appendLine($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    // ── File helpers ────────────────────────────────────────────────────────

    private static function resolveLogFile(string $category): string {
        $category = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $category));
        $dir      = self::$baseDir . '/' . $category;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . date('Y-m-d') . '.log';
    }

    /**
     * Append a single line to a file with an exclusive lock to prevent race conditions.
     */
    private static function appendLine(string $path, string $line): void {
        $fp = fopen($path, 'a');
        if ($fp === false) return;

        flock($fp, LOCK_EX);
        fwrite($fp, $line . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    // ── Request context helpers ─────────────────────────────────────────────

    private static function getClientIp(): string {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // X-Forwarded-For can be a comma-separated list
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return 'unknown';
    }

    private static function resolveUserId(array &$metadata): ?int {
        if (isset($metadata['user_id'])) {
            $id = (int) $metadata['user_id'];
            unset($metadata['user_id']); // move to top-level field
            return $id;
        }
        return null;
    }

    /**
     * Strip sensitive keys from metadata before logging.
     */
    private static function sanitizeMetadata(array $metadata): array {
        $sensitive = ['password', 'token', 'secret', 'credit_card', 'cvv'];
        foreach ($sensitive as $key) {
            if (isset($metadata[$key])) {
                $metadata[$key] = '[REDACTED]';
            }
        }
        return $metadata;
    }

    // ── Reader helpers (used by the API) ────────────────────────────────────

    /**
     * Read the last $lines lines from a file efficiently (reverse scan).
     * Avoids loading the entire file into memory.
     */
    public static function tailFile(string $path, int $lines = 200): array {
        if (!file_exists($path)) return [];

        $fp   = fopen($path, 'r');
        $buffer = '';
        $result = [];
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);

        while ($pos > 0 && count($result) < $lines) {
            $chunk = min(4096, $pos);
            $pos  -= $chunk;
            fseek($fp, $pos);
            $buffer = fread($fp, $chunk) . $buffer;
            $parts  = explode("\n", $buffer);
            // Keep the incomplete first part for the next iteration
            $buffer = array_shift($parts);
            // Prepend complete lines (reversed)
            foreach (array_reverse($parts) as $line) {
                if ($line !== '' && count($result) < $lines) {
                    $result[] = $line;
                }
            }
        }

        // Handle any remaining buffer
        if ($buffer !== '' && count($result) < $lines) {
            $result[] = $buffer;
        }

        fclose($fp);
        return array_reverse($result); // oldest first
    }

    /**
     * Return all available log files grouped by category.
     * Returns: ['auth' => ['/path/2026-05-28.log', ...], ...]
     */
    public static function listLogFiles(): array {
        $result = [];
        if (!is_dir(self::$baseDir)) return $result;

        foreach (scandir(self::$baseDir) as $cat) {
            if ($cat === '.' || $cat === '..') continue;
            $catDir = self::$baseDir . '/' . $cat;
            if (!is_dir($catDir)) continue;

            $files = glob($catDir . '/*.log');
            sort($files);
            $result[$cat] = $files;
        }
        return $result;
    }

    /**
     * Get the path to today's log file for a given category.
     */
    public static function todayFile(string $category): string {
        $category = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $category));
        return self::$baseDir . '/' . $category . '/' . date('Y-m-d') . '.log';
    }

    /**
     * Get the base storage directory.
     */
    public static function getBaseDir(): string {
        return self::$baseDir;
    }
}
