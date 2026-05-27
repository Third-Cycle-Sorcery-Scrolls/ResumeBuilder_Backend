<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ActivityLog.php';
require_once __DIR__ . '/../../middlewares/authMiddleware.php';
require_once __DIR__ . '/../../middlewares/roleMiddleware.php';

header('Content-Type: application/json');

// Step 1: verify token and get user
$user = authMiddleware();

// Step 2: only admins can view logs
roleMiddleware($user, ['admin']);

// Step 3: build filters from query params
$activityLog = new ActivityLog($conn);

$filters = [];

if (!empty($_GET['user_id'])) {
    $filters['user_id'] = $_GET['user_id'];
}

if (!empty($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}

if (!empty($_GET['entity_type'])) {
    $filters['entity_type'] = $_GET['entity_type'];
}

// Step 4: fetch and return logs
$logs = $activityLog->getAll($filters);

echo json_encode([
    'status' => 'success',
    'count'  => count($logs),
    'data'   => $logs
]);