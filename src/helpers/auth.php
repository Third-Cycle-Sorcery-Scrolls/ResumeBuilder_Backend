<?php

/**
 * Authentication & Authorization Helper Functions
 * 
 * Provides middleware for validating user authentication via JWT tokens
 * and role-based authorization for API endpoints.
 */

require_once __DIR__ . '/response.php';

/**
 * Simple JWT Token Creation (for session)
 * Note: In production, use a proper JWT library like firebase/php-jwt
 * 
 * @param int $userId User ID
 * @param string $email User email
 * @param string $role User role (admin, user)
 * @return string JWT-like token
 */
function createToken($userId, $email, $role) {
    // For now, use a simple session-based approach
    // In production, implement proper JWT signing
    $token = base64_encode(json_encode([
        'userId' => $userId,
        'email' => $email,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 hour expiration
    ]));
    return $token;
}

/**
 * Verify and Extract Token from Authorization Header
 * Expects: "Authorization: Bearer <token>"
 * 
 * @return array|null Decoded token data or null if invalid
 */
function verifyToken() {
    $headers = getallheaders();
    
    // Check if Authorization header exists
    if (!isset($headers['Authorization'])) {
        return null;
    }
    
    $authHeader = $headers['Authorization'];
    
    // Extract Bearer token
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return null;
    }
    
    $token = substr($authHeader, 7); // Remove "Bearer " prefix
    
    try {
        $decoded = json_decode(base64_decode($token), true);
        
        // Check if token has expired
        if (isset($decoded['exp']) && $decoded['exp'] < time()) {
            return null;
        }
        
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Middleware: Verify User Authentication
 * Returns user data if authenticated, otherwise returns JSON error and exits
 * 
 * @return array User data (userId, email, role)
 */
function authenticateUser() {
    $user = verifyToken();
    
    if (!$user) {
        jsonResponse(401, false, "Unauthorized: Invalid or missing authentication token");
        exit();
    }
    
    return $user;
}

/**
 * Middleware: Verify User is Admin
 * Returns user data if user is admin, otherwise returns JSON error and exits
 * 
 * @return array User data with admin role
 */
function authenticateAdmin() {
    $user = authenticateUser();
    
    if ($user['role'] !== 'admin') {
        jsonResponse(403, false, "Forbidden: Admin access required");
        exit();
    }
    
    return $user;
}

/**
 * Verify User Owns Resource
 * Ensures the authenticated user can only access their own data
 * 
 * @param int $userId Current authenticated user ID
 * @param int $resourceUserId User ID of the resource owner
 * @return bool True if user owns the resource
 */
function verifyResourceOwnership($userId, $resourceUserId) {
    return (int)$userId === (int)$resourceUserId;
}

/**
 * Get Authorization Header
 * 
 * @return string|null Authorization header value
 */
function getAuthorizationHeader() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? null;
}

/**
 * Check if user role matches required role(s)
 * 
 * @param array $user User data from token
 * @param string|array $requiredRoles Single role or array of allowed roles
 * @return bool True if user has required role
 */
function hasRole($user, $requiredRoles) {
    if (is_string($requiredRoles)) {
        return $user['role'] === $requiredRoles;
    }
    
    if (is_array($requiredRoles)) {
        return in_array($user['role'], $requiredRoles);
    }
    
    return false;
}
?>
