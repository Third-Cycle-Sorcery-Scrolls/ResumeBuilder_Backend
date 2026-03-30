<?php
    // Importing db connection and response helper
    require_once '../../config/db.php';
    require_once '/../../helpers/response.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password){
            jsonResponse(400, false, "Email and password are required!", null, "Credentials are not provided fully.");
        }
        // Check if the user is registered(email already exists)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0){
            jsonResponse(401, false, "Email already exists!", null, "A user with this email already exists.");
            exit;
        }
        // If it is a new user, hash the password and store it in the database
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO Users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $passwordHash]);

        jsonResponse(201, true, "User registered successfully");

    }
    else{
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }

?>