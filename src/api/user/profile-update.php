<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/debug.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Verify user is authenticated
        $user = authenticateUser();
        $userId = $user['userId'];
        
        // Get request data
        $data = json_decode(file_get_contents("php://input"), true);
        
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        
        // At least one field must be provided for update
        if (!$name && !$email && !$password) {
            jsonResponse(400, false, "At least one field (name, email, or password) is required for update");
            exit();
        }
        
        // If email is being updated, check if it's already in use by another user
        if ($email) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->rowCount() > 0) {
                jsonResponse(400, false, "Email is already in use by another user");
                exit();
            }
        }
        
        // Build dynamic UPDATE query
        $updateFields = [];
        $params = [];
        
        if ($name) {
            $updateFields[] = "name = ?";
            $params[] = $name;
        }
        
        if ($email) {
            $updateFields[] = "email = ?";
            $params[] = $email;
        }
        
        if ($password) {
            // Validate password strength (minimum 4 characters)
            if (strlen($password) < 4) {
                jsonResponse(400, false, "Password must be at least 4 characters long");
                exit();
            }
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $updateFields[] = "password = ?";
            $params[] = $passwordHash;
        }
        
        // Add user ID to params for WHERE clause
        $params[] = $userId;
        
        // Update timestamp
        $updateFields[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            // Fetch updated user data (without password)
            $stmt = $conn->prepare("SELECT id, name, email, profile_picture, role, created_at, updated_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            debug("User profile updated", ["userId" => $userId]);
            
            jsonResponse(200, true, "Profile updated successfully", $updatedUser);
        } else {
            jsonResponse(400, false, "No changes were made to the profile");
        }
        
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Unauthorized") !== false) {
            jsonResponse(401, false, "Unauthorized", null, "Invalid or missing authentication token");
        } else {
            jsonResponse(500, false, "Database error", null, $e->getMessage());
        }
    }
} else {
    jsonResponse(405, false, "Method Not Allowed", null, "Only PUT requests are allowed");
}
?>
