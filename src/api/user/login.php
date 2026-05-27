<?php
    require_once __DIR__  . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';
    require_once __DIR__ . '/../../helpers/jwt-token.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . "/../../helpers/logger.php";

    header('Content-Type: application/json');

    try{

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $email = isset($_POST['email']) ? trim($_POST['email']) : null;
            $password = isset($_POST['password']) ? trim($_POST['password']) : null;

            if (!$email || !$password){

                logError($conn,"Login failed: missing email or password",__FILE__,__LINE__,null);

                jsonResponse(400, false, "Email and password are required!", null, "Credentials are not provided fully.");
            }

            // Sanitize email
            $email = htmlspecialchars($email);

            // Check if the user is registered(email already exists)
            $user = new User($conn);
            $userData = $user->findByEmail($email);
            if(!$userData || !password_verify($password, $userData->getPassword())){

                logError($conn,"Login failed: invalid credentials for email=$email",__FILE__,__LINE__,null);

                jsonResponse(401, false, "Invalid credentials!", null, "Email or password is incorrect.");
            }

            $payload = [
                'id' => $userData->getId(),
                "email" => $userData->getEmail(),
                'role' => $userData->getRole()
            ];
            $token = generateToken($payload);

            jsonResponse(200, true, "Login successful", [
                'user' => [
                    "id" => $userData->getId(),
                    "username" => $userData->getUsername(),
                    "email" => $userData->getEmail(),
                ],
                'token' => $token,
            ]);

        }else{

            logError($conn,"Login route accessed with invalid method",__FILE__,__LINE__,null);

            jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
        }
    }catch(Exception $e){

        logError($conn,$e->getMessage(),$e->getFile(),$e->getLine(),null);

        jsonResponse(500,false,"Internal Server Error");
    }
?>