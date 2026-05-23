<?php
    // Importing db connection and response helper
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';
    require_once __DIR__ . '/../../models/User.php';

    $user = new User($conn);

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = isset($_POST['username']) ? trim($_POST['username']) : null;
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;

        // Validate input
        if(!$username){
            jsonResponse(400, false, "Username is required!", null, "The username field is missing.");
        }

        if (!$email){
            jsonResponse(400, false, "Email is required!", null, "The email field is missing.");
        }

        if(!$password){
            jsonResponse(400, false, "Password is required!", null, "The password field is missing.");
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            jsonResponse(400, false, "Invalid email format!", null, "The provided email does not match a valid format.");
        }

        if(strlen($password) < 6){
            jsonResponse(400, false, "Password must be at least 6 characters long!", null, "The provided password does not meet the minimum length requirement.");
        }

        // Sanitization block
        $username = htmlspecialchars($username);
        $email = htmlspecialchars($email);

        // Check if the user is registered(email already exists)
        if($user->findByEmailOrUsername($email, $username)){
            jsonResponse(409, false, "User already exists!", null, "A user with the provided email or username already exists.");
        }

        // If it is a new user, hash the password and store it in the database
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        if($userData = $user->create($username, $email, $passwordHash)){
            jsonResponse(201, true, "User registered successfully", [
                "username" => $username,
                "userId" => $userData->getId(),
                "email" => $email
            ]);

        }else {
            jsonResponse(500, false, "Failed to register user!", null, "An error occurred while trying to register the user.");
        }

    }
    else{
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }

?>