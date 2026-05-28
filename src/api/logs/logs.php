<?php
/**
 * Logs API — file-based, admin-only
 *
 * GET  /api/logs/logs.php                  → paginated log list
 * GET  /api/logs/logs.php?stream=1         → SSE realtime stream
 * GET  /api/logs/logs.php?metrics=1        → today's summary metrics
 * GET  /api/logs/logs.php?download=1       → download filtered logs as JSON/CSV
 *
 * Filters (all optional):
 *   category  = AUTH|RESUME|ERROR|SECURITY|SYSTEM
 *   level     = INFO|WARNING|ERROR|CRITICAL
 *   search    = free-text (action, message, ip, user_id)
 *   user_id   = integer
 *   date      = YYYY-MM-DD  (defaults to today)
 *   date_from / date_to for range
 *   page      = integer (default 1)
 *   per_page  = integer (default 50, max 200)
 *   sort      = asc|desc (default desc)
 *   format    = json|csv  (for download)
 */

require_once __DIR__ . '/../../helpers/Logger.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/response.php';

// ── CORS ────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ── Auth guard ───────────────────────────────────────────────────────────────
authenticateAdmin();

// ── Sanitize query params ────────────────────────────────────────────────────
$category  = sanitizeParam($_GET['category']  ?? '');
$level     = sanitizeParam($_GET['level']     ?? '');
$search    = sanitizeParam($_GET['search']    ?? '');
$userId    = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$date      = sanitizeDate($_GET['date']       ?? date('Y-m-d'));
$dateFrom  = sanitizeDate($_GET['date_from']  ?? '');
$dateTo    = sanitizeDate($_GET['date_to']    ?? '');
$page      = max(1, (int)($_GET['page']       ?? 1));
$perPage   = min(200, max(1, (int)($_GET['per_page'] ?? 50)));
$sort      = ($_GET['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$format    = ($_GET['format'] ?? 'json') === 'csv' ? 'csv' : 'json';

// ── Route ────────────────────────────────────────────────────────────────────
if (isset($_GET['metrics'])) {
    handleMetrics();
    exit();
}

if (isset($_GET['download'])) {
    handleDownload($category, $level, $search, $userId, $dateFrom ?: $date, $dateTo ?: $date, $sort, $format);
    exit();
}

// Default: paginated list
header("Content-Type: application/json");
$allLogs = loadLogs($category, $level, $search, $userId, $dateFrom ?: $date, $dateTo ?: $date, $sort);
$total   = count($allLogs);
$offset  = ($page - 1) * $perPage;
$paged   = array_slice($allLogs, $offset, $perPage);

echo json_encode([
    'success' => true,
    'logs'    => $paged,
    'pagination' => [
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => (int)ceil($total / $perPage),
    ],
]);

// ════════════════════════════════════════════════════════════════════════════
// HANDLERS
// ════════════════════════════════════════════════════════════════════════════

/**
 * Metrics: counts for today's logs across all categories.
 */
function handleMetrics(): void {
    header("Content-Type: application/json");

    $metrics = [
        'total_today'    => 0,
        'errors_today'   => 0,
        'warnings_today' => 0,
        'by_category'    => [],
        'by_level'       => [],
        'failed_logins'  => 0,
        'active_users'   => [],
        'exports_today'  => 0,
    ];

    $categories = ['auth', 'resume', 'error', 'security', 'system'];
    foreach ($categories as $cat) {
        $file = Logger::todayFile($cat);
        if (!file_exists($file)) continue;

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $entry = json_decode($line, true);
            if (!$entry) continue;

            $metrics['total_today']++;
            $lvl = $entry['level'] ?? '';
            $metrics['by_level'][$lvl] = ($metrics['by_level'][$lvl] ?? 0) + 1;
            $metrics['by_category'][$cat] = ($metrics['by_category'][$cat] ?? 0) + 1;

            if ($lvl === 'ERROR' || $lvl === 'CRITICAL') $metrics['errors_today']++;
            if ($lvl === 'WARNING') $metrics['warnings_today']++;

            $action = $entry['action'] ?? '';
            if ($action === 'AUTH_LOGIN_FAILED') $metrics['failed_logins']++;
            if ($action === 'RESUME_EXPORT')     $metrics['exports_today']++;

            if (!empty($entry['user_id'])) {
                $metrics['active_users'][$entry['user_id']] = true;
            }
        }
    }

    $metrics['active_users'] = count($metrics['active_users']);
    echo json_encode(['success' => true, 'metrics' => $metrics]);
}

/**
 * Download: stream filtered logs as JSON or CSV attachment.
 */
function handleDownload(
    string $category, string $level, string $search, ?int $userId,
    string $dateFrom, string $dateTo, string $sort, string $format
): void {
    $logs = loadLogs($category, $level, $search, $userId, $dateFrom, $dateTo, $sort);

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="logs-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['timestamp', 'level', 'category', 'action', 'message', 'user_id', 'ip_address', 'user_agent']);
        foreach ($logs as $e) {
            fputcsv($out, [
                $e['timestamp'] ?? '', $e['level'] ?? '', $e['category'] ?? '',
                $e['action'] ?? '', $e['message'] ?? '', $e['user_id'] ?? '',
                $e['ip_address'] ?? '', $e['user_agent'] ?? '',
            ]);
        }
        fclose($out);
    } else {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="logs-' . date('Y-m-d') . '.json"');
        echo json_encode($logs, JSON_PRETTY_PRINT);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════════════════

/**
 * Load, filter, and sort log entries from files.
 */
function loadLogs(
    string $category, string $level, string $search, ?int $userId,
    string $dateFrom, string $dateTo, string $sort
): array {
    $categories = $category
        ? [strtolower($category)]
        : ['auth', 'resume', 'error', 'security', 'system'];

    $results = [];

    foreach ($categories as $cat) {
        $files = getFilesInRange($cat, $dateFrom, $dateTo);
        foreach ($files as $file) {
            $lines = Logger::tailFile($file, 5000);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if (!$entry) continue;
                if (!matchesFilters($entry, $category, $level, $search, $userId)) continue;
                $results[] = $entry;
            }
        }
    }

    // Sort by timestamp
    usort($results, function ($a, $b) use ($sort) {
        $cmp = strcmp($a['timestamp'] ?? '', $b['timestamp'] ?? '');
        return $sort === 'asc' ? $cmp : -$cmp;
    });

    return $results;
}

/**
 * Return log file paths for a category within a date range.
 */
function getFilesInRange(string $cat, string $dateFrom, string $dateTo): array {
    $baseDir = Logger::getBaseDir();
    $catDir  = $baseDir . '/' . strtolower($cat);
    if (!is_dir($catDir)) return [];

    $files = glob($catDir . '/*.log');
    if (!$files) return [];

    return array_filter($files, function ($f) use ($dateFrom, $dateTo) {
        $name = basename($f, '.log'); // YYYY-MM-DD
        return $name >= $dateFrom && $name <= $dateTo;
    });
}

/**
 * Check whether a log entry matches the active filters.
 */
function matchesFilters(array $entry, string $category, string $level, string $search, ?int $userId): bool {
    if ($category && strcasecmp($entry['category'] ?? '', $category) !== 0) return false;
    if ($level    && strcasecmp($entry['level']    ?? '', $level)    !== 0) return false;
    if ($userId   && (int)($entry['user_id'] ?? 0) !== $userId)             return false;

    if ($search) {
        $haystack = strtolower(
            ($entry['action']    ?? '') . ' ' .
            ($entry['message']   ?? '') . ' ' .
            ($entry['ip_address']?? '') . ' ' .
            ($entry['user_id']   ?? '')
        );
        if (strpos($haystack, strtolower($search)) === false) return false;
    }

    return true;
}

// ── Input sanitizers ─────────────────────────────────────────────────────────

function sanitizeParam(string $val): string {
    return preg_replace('/[^a-zA-Z0-9_\-\s@.]/', '', trim($val));
}

function sanitizeDate(string $val): string {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) ? $val : date('Y-m-d');
}
