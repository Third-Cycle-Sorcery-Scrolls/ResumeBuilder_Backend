<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/logger.php';

function roleMiddleware($user, $allowedRoles){
    global $conn;

    if (!in_array($user['role'], $allowedRoles)){
        logError(
            $conn,
            "Access denied: user_id={$user['id']} with role={$user['role']} attempted to access a resource requiring roles=[" . implode(', ', $allowedRoles) . "]",
            __FILE__,
            __LINE__,
            $user['id'] ?? null
        );
        jsonResponse(403, false, "Forbidden: You don't have permission to access this resource.", null, "User role does not have the necessary permissions.");
    }
}
?>