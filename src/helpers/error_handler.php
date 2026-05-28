<?php
/**
 * Global Error Handler
 *
 * Include this file at the top of any PHP entry point to automatically log:
 *   - Uncaught exceptions  → ERROR / SYSTEM
 *   - Fatal PHP errors     → CRITICAL / SYSTEM
 *   - E_WARNING / E_NOTICE → WARNING / SYSTEM  (only in dev; silent in prod)
 *
 * Usage:
 *   require_once __DIR__ . '/../../helpers/error_handler.php';
 *
 * Helper functions for manual logging:
 *   logDbFailure($e, $context)
 *   logValidationFailure($message, $context)
 *   logFileUploadFailure($message, $context)
 */

require_once __DIR__ . '/Logger.php';

// ── Uncaught exception handler ───────────────────────────────────────────────
set_exception_handler(function (Throwable $e) {
    Logger::critical(Logger::CAT_SYSTEM, 'UNCAUGHT_EXCEPTION', $e->getMessage(), [
        'exception'   => get_class($e),
        'file'        => $e->getFile(),
        'line'        => $e->getLine(),
        'stack_trace' => $e->getTraceAsString(),
    ]);

    // Don't expose internals to the client
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit(1);
});

// ── Fatal error handler (shutdown) ───────────────────────────────────────────
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        Logger::critical(Logger::CAT_SYSTEM, 'FATAL_ERROR', $err['message'], [
            'file' => $err['file'],
            'line' => $err['line'],
            'type' => $err['type'],
        ]);
    }
});

// ── PHP error handler (warnings/notices) ────────────────────────────────────
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Only log warnings and above; ignore notices/deprecations
    if (!($errno & (E_WARNING | E_USER_WARNING | E_USER_ERROR))) {
        return false; // let PHP handle it
    }

    $level = ($errno === E_USER_ERROR) ? Logger::LEVEL_ERROR : Logger::LEVEL_WARNING;
    Logger::write($level, Logger::CAT_SYSTEM, 'PHP_ERROR', $errstr, [
        'file'  => $errfile,
        'line'  => $errline,
        'errno' => $errno,
    ]);

    return false; // don't suppress PHP's own handling
});

// ════════════════════════════════════════════════════════════════════════════
// Manual logging helpers — call these from any PHP file
// ════════════════════════════════════════════════════════════════════════════

/**
 * Log a database failure (PDOException or similar).
 */
function logDbFailure(Throwable $e, array $context = []): void {
    Logger::critical(Logger::CAT_ERROR, 'DB_CONNECTION_FAILED', $e->getMessage(), array_merge([
        'file'        => $e->getFile(),
        'line'        => $e->getLine(),
        'stack_trace' => $e->getTraceAsString(),
    ], $context));
}

/**
 * Log a validation failure.
 */
function logValidationFailure(string $message, array $context = []): void {
    Logger::warning(Logger::CAT_ERROR, 'VALIDATION_FAILED', $message, $context);
}

/**
 * Log a file upload failure.
 */
function logFileUploadFailure(string $message, array $context = []): void {
    Logger::error(Logger::CAT_ERROR, 'FILE_UPLOAD_FAILED', $message, $context);
}
