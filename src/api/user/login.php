<?php
    require_once __DIR__  . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = trim($_POST['email']) ?? null;
        $password = trim($_POST['password']) ?? null;

        if (!$email || !$password){
            jsonResponse(400, false, "Email and password are required!", null, "Credentials are not provided fully.");
        }

        // Sanitize email
        $email = htmlspecialchars($email);

        // Check if the user is registered(email already exists)
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$user){
            jsonResponse(401, false, "Invalid credentials!", null, "Email or password is incorrect.");
        }
        if (!password_verify($password, $user['password'])){
            jsonResponse(401, false, "Invalid credentials!", null, "Email or password is incorrect.");
        }

        $token = bin2hex(random_bytes(32)); // Generate a random token (for demo)

        jsonResponse(200, true, "Login successful", [
            'user' => [
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email'],
            ],
            'token' => $token,
        ]);

    }else{
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }
?>