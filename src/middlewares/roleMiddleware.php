<?php
require_once __DIR__ . '/../helpers/response.php';

function roleMiddleware($user, $allowedRoles){
    if (!in_array($user['role'], $allowedRoles)){
        jsonResponse(403, false, "Forbidden: You don't have permission to access this resource.", null, "User role does not have the necessary permissions.");
    }
}
?>