<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/jwt-token.php';
require_once __DIR__ . '/../helpers/logger.php';

function authMiddleware(){
    global $conn;

    $headers = getallheaders();

    if (!isset($headers['Authorization'])){
        logError($conn, "Auth failed: missing Authorization header", __FILE__, __LINE__, null);
        jsonResponse(401, false, "Unauthorized: No token provided.", null, "Missing token.");
    }

    if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)){
        logError($conn, "Auth failed: malformed Authorization header format", __FILE__, __LINE__, null);
        jsonResponse(401, false, "Unauthorized", null, "Token format is incorrect.");
    }

    $payload = verifyJWT($matches[1]);

    if(!$payload){
        logError($conn, "Auth failed: invalid or expired token", __FILE__, __LINE__, null);
        jsonResponse(401, false, "Unauthorized", null, "Token is invalid or has expired.");
    }

    return $payload;
}