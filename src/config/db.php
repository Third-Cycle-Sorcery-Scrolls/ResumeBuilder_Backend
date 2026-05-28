<?php
    $host = 'localhost';
    $port = 3306;
    $db = 'resume_builder';
    $user = "phpuser";
    $password = "Php##0923";

    // Register global error/exception handlers for all endpoints
    require_once __DIR__ . '/../helpers/error_handler.php';

    try{
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
        logDbFailure($e, ['host' => $host, 'db' => $db]);
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(503);
        }
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit(1);
    }
?>