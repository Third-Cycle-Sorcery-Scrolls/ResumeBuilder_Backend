<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';

header('Content-Type: application/json');

function getRequestBody(): array {
    $body = json_decode(file_get_contents('php://input'), true);
    return is_array($body) ? $body : [];
}

function findUser(PDO $conn, ?string $userId, ?string $email) {
    if ($userId) {
        $stmt = $conn->prepare('SELECT id, email, visit_count FROM users WHERE id = ?');
        $stmt->execute([$userId]);
    } else {
        $stmt = $conn->prepare('SELECT id, email, visit_count FROM users WHERE email = ?');
        $stmt->execute([$email]);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = getRequestBody();
    $userId = $_POST['user_id'] ?? $body['user_id'] ?? null;
    $email = $_POST['email'] ?? $body['email'] ?? null;

    if (!$userId && !$email) {
        jsonResponse(400, false, 'user_id or email is required to update visit count.', null, 'Missing user identifier.');
    }

    $user = findUser($conn, $userId, $email);
    if (!$user) {
        jsonResponse(404, false, 'User not found.', null, 'The provided user_id or email does not exist.');
    }

    try {
        $stmt = $conn->prepare('UPDATE users SET visit_count = COALESCE(visit_count, 0) + 1, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$user['id']]);
    } catch (PDOException $e) {
        jsonResponse(500, false, 'Failed to update visit count.', null, 'Database column visit_count may be missing: ' . $e->getMessage());
    }

    $stmt = $conn->prepare('SELECT visit_count FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $visitCount = (int) $stmt->fetchColumn();

    jsonResponse(200, true, 'Visit count incremented successfully.', [
        'user_id' => $user['id'],
        'visit_count' => $visitCount,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? null;
    $email = $_GET['email'] ?? null;

    if (!$userId && !$email) {
        jsonResponse(400, false, 'user_id or email is required to fetch visit count.', null, 'Missing user identifier.');
    }

    $user = findUser($conn, $userId, $email);
    if (!$user) {
        jsonResponse(404, false, 'User not found.', null, 'The provided user_id or email does not exist.');
    }

    jsonResponse(200, true, 'Visit count retrieved successfully.', [
        'user_id' => $user['id'],
        'visit_count' => (int) $user['visit_count'],
    ]);
}

jsonResponse(404, false, 'Route not found.', null, 'The requested endpoint does not exist.');
