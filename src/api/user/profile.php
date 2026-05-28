<?php
require_once __DIR__."/../../config/db.php";
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/auth.php';
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
require_once '../../helpers/debug.php';
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Authenticate user for all requests
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized: Authentication required");
    exit();
}

// Users Table:
// - id (INT, PRIMARY KEY, AUTO_INCREMENT)
// - name (VARCHAR(255))
// - email (VARCHAR(255), UNIQUE)
// - password (VARCHAR(255))
// - profile_picture (VARCHAR(255)) (optional)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        jsonResponse(400, false, "User ID required");
    }
    
    // Verify user can only access their own profile
    if (!verifyResourceOwnership($userId, $user_id)) {
        jsonResponse(403, false, "Forbidden: Cannot access another user's profile");
        exit();
    }
    $sql = $conn->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ?");
    $sql->execute([$user_id]);
    $data = $sql->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        jsonResponse(404, false, "User not found");
    }
    $data['profile_picture'] = $data['profile_picture'] ? "http://localhost:8000/" . $data['profile_picture'] : null;
    jsonResponse(200, true, "User profile fetched", $data);
} elseif (
    $_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        jsonResponse(400, false, "User ID required");
    }

    $data = $_POST;

    $existingUserSql = $conn->prepare(
        "SELECT * FROM users WHERE id = ?"
    );

    $existingUserSql->execute([$user_id]);

    $existingUser = $existingUserSql->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        jsonResponse(404, false, "User not found");
    }

    $name = $data['name'] ?? $existingUser['name'];

    $email = $data['email'] ?? $existingUser['email'];

    $profile_picture =
        $existingUser['profile_picture'];

    // HANDLE IMAGE UPLOAD


    if (
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] === 0
    ) {

        $uploadDir = "../../uploads/";

        // create uploads dir if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['profile_picture'];

        $extension = pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        );

        $fileName =
            uniqid("profile_", true)
            . "."
            . $extension;

        $targetPath =
            $uploadDir . $fileName;

        move_uploaded_file(
            $file['tmp_name'],
            $targetPath
        );

        // save relative path in db
        $profile_picture =
            "uploads/" . $fileName;
    }

    //PASSWORD
   

    $password = $data['password'] ?? null;

    $confirm_password =
        $data['confirm_password'] ?? null;

    if (
        $password &&
        $password !== $confirm_password
    ) {
        jsonResponse(
            400,
            false,
            "Passwords do not match"
        );
    }

    //UPDATE QUERY


    $updateFields =
        "name = ?, email = ?, profile_picture = ?";

    $params = [
        $name,
        $email,
        $profile_picture
    ];

    if ($password) {

        $updateFields .= ", password = ?";

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $params[] = $hashedPassword;
    }

    $params[] = $user_id;

    $sql = $conn->prepare(
        "UPDATE users
         SET $updateFields
         WHERE id = ?"
    );

    if ($sql->execute($params)) {

        jsonResponse(
            200,
            true,
            "User profile updated"
        );

    } else {

        jsonResponse(
            500,
            false,
            "Failed to update user profile"
        );
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $user_id = $_GET['user_id'] ?? null;
    if (!$user_id) {
        jsonResponse(400, false, "User ID required");
    }
    // Optionally: delete user's resumes and related data here

    $sql = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($sql->execute([$user_id])) {
        jsonResponse(200, true, "User deleted");
    } else {
        jsonResponse(500, false, "Failed to delete user");
    }
} else {
    jsonResponse(405, false, "Method not allowed");
}