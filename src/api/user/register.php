<?php
    // Importing db connection and response helper
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';

    require_once './../../config/db.php';
    require_once './../../helpers/response.php';
    header('Content-Type: application/json');
    // CORS
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        // Get the input data
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        if (!$email || !$password){
            jsonResponse(400, false, "Email and password are required!", null, "Credentials are not provided fully.");
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            jsonResponse(400, false, "Invalid email format!", null, "The provided email does not match a valid format.");
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

        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $passwordHash]);
        $userId = $conn->lastInsertId();

        jsonResponse(201, true, "User registered successfully",[
            "userId" => $userId,
            "email" => $email
        ]);

    }
    else{
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }
?>