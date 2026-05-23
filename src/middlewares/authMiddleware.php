<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/jwt-token.php';

function authMiddleware(){
    $headers = getallheaders();
    if (!isset($headers['Authorization'])){
        jsonResponse(401, false, "Unauthorized: No token provided.",null, "Missing token.");
    }
    if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)){
        jsonResponse(401, false, "Unauthorized", null, "Token format is incorrect.");
    }

    $payload = verifyJWT($matches[1]);

    if(!$payload){
        jsonResponse(401, false, "Unauthorized", null, "Token is invalid or has expired.");
    }

    return $payload;
}