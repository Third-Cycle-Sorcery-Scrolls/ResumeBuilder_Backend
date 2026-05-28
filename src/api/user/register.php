<?php
    // Importing db connection and response helper
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../helpers/response.php';
    require_once __DIR__ . '/../../helpers/auth.php';
    require_once __DIR__ . '/../../helpers/Logger.php';
    
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

    $settingsFile = __DIR__ . '/../admin/settings.store.json';
    $defaultSettings = [
        'allowRegistration' => true,
    ];

    $readSettings = function ($file, $defaults) {
        if (!file_exists($file)) {
            return $defaults;
        }

        $decoded = json_decode(file_get_contents($file), true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, $decoded);
    };

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $settings = $readSettings($settingsFile, $defaultSettings);

        if (!($settings['allowRegistration'] ?? true)) {
            jsonResponse(403, false, 'Registration is currently disabled by the administrator');
            exit();
        }

        // Get the input data
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $name = $input['name'] ?? null;
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
            Logger::warning(Logger::CAT_AUTH, 'AUTH_REGISTER_FAILED', 'Registration failed: email already exists', [
                'email' => $email,
            ]);
            jsonResponse(401, false, "Email already exists!", null, "A user with this email already exists.");
            exit;
        }
        // If it is a new user, hash the password and store it in the database
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        $stmt->execute([$email, $passwordHash, $name]);
        $userId = $conn->lastInsertId();

        // Generate authentication token
        $token = createToken($userId, $email, 'user');

        Logger::info(Logger::CAT_AUTH, 'AUTH_REGISTER', 'New user registered', [
            'user_id' => $userId,
            'email'   => $email,
            'name'    => $name,
        ]);

        jsonResponse(201, true, "User registered successfully",[
            "userId" => $userId,
            "email" => $email,
            "name" => $name,
            "token" => $token,
            "role" => "user"
        ]);

    }
    else{
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }
?>