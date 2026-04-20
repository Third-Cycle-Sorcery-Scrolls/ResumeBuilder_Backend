<?php
    function jsonResponse($code, $success, $message, $data=null, $error = null){
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data,
            "error" => $error
        ]);
        exit();
    }
    function get_env_value($key, $default = null) {
    // In a real setup, you'd use a library, but for a school project, 
    // you can define them here or use a simple parser.
    $env = parse_ini_file('.env'); 
    return $env[$key] ?? $default;
}

$host = get_env_value('DB_HOST', 'localhost');
$db   = get_env_value('DB_NAME', 'resume_db');
$user = get_env_value('DB_USER', 'root');
$pass = get_env_value('DB_PASS', 'root');
$port = get_env_value('DB_PORT', '3306');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
    }
?>