<?php
    $host = getenv('DB_HOST') ?? 'localhost';
    $port = getenv('DB_PORT') ?? 3306;
    $db = getenv('DB_NAME') ?? 'resume_builder';
    $user = getenv('DB_USER') ?? 'root';
    $password = getenv('DB_PASS') ?? 'root';

    try{
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "DB connected successfully";
    }catch(PDOException $e){
        die("DB connection failed: " . $e->getMessage());
    }
?>