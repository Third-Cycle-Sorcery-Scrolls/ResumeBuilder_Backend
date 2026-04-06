<?php
    require_once __DIR__  . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password){
            jsonResponse(400, false, "Email and password are required!", null, "Credentials are not provided fully.");
        }
        // Check if the user is registered(email already exists)
        $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($password, $user['password'])){
            jsonResponse(400, false, "Invalid credentials!", null, "Email or password is incorrect.");
        }
        jsonResponse(200, true, "Login successful", [
            "userId" => $user['id'],
            "email" => $user['email'],
        ]);

    }else{
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }
?>