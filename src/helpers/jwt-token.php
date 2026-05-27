<?php

require_once __DIR__ . '/../../config/db.php';

define('JWT_SECRET_KEY', 'your_secret');

function base64UrlEncode($data){
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data){
    return base64_decode(strtr($data, '-_', '+/'));
}

function generateToken($payload){
    global $conn;

    try {
        $header         = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + 900;
        $base64_header  = base64UrlEncode($header);
        $base64_payload = base64UrlEncode(json_encode($payload));

        $signature        = hash_hmac('sha256', "$base64_header.$base64_payload", JWT_SECRET_KEY, true);
        $base64_signature = base64UrlEncode($signature);

        return "$base64_header.$base64_payload.$base64_signature";

    } catch (Exception $e) {
        require_once __DIR__ . '/logger.php';
        logError($conn, "JWT generation failed: " . $e->getMessage(), $e->getFile(), $e->getLine(), null);
        return null;
    }
}

function verifyJWT($token){
    global $conn;

    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            require_once __DIR__ . '/logger.php';
            logError($conn, "JWT verification failed: malformed token structure", __FILE__, __LINE__, null);
            return null;
        }

        list($base64_header, $base64_payload, $base64_signature) = $parts;

        $signature          = base64UrlDecode($base64_signature);
        $expected_signature = hash_hmac('sha256', "$base64_header.$base64_payload", JWT_SECRET_KEY, true);

        if (!hash_equals($signature, $expected_signature)) {
            require_once __DIR__ . '/logger.php';
            logError($conn, "JWT verification failed: invalid signature", __FILE__, __LINE__, null);
            return null;
        }

        $payload = json_decode(base64UrlDecode($base64_payload), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            require_once __DIR__ . '/logger.php';
            logError($conn, "JWT verification failed: token expired for user_id=" . ($payload['id'] ?? 'unknown'), __FILE__, __LINE__, $payload['id'] ?? null);
            return null;
        }

        return $payload;

    } catch (Exception $e) {
        require_once __DIR__ . '/logger.php';
        logError($conn, "JWT verification failed: " . $e->getMessage(), $e->getFile(), $e->getLine(), null);
        return null;
    }
}