<?php
    // Importing db connection and response helper
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = trim($_POST['username']) ?? null;
        $email = trim($_POST['email']) ?? null;
        $password = trim($_POST['password']) ?? null;

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
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username,
                        ':email'=> $email]);
        if ($stmt->rowCount() > 0){
            jsonResponse(400, false, "Username or email already exists!", null, "A user with this username/email already exists.");
            exit;
        }

        // If it is a new user, hash the password and store it in the database
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $result = $stmt->execute([':username' => $username,
                        ':email' => $email,
                        ':password' => $passwordHash]);
        if($result){
            $userId = $conn->lastInsertId();
            jsonResponse(201, true, "User registered successfully", [
                "username" => $username,
                "userId" => $userId,
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