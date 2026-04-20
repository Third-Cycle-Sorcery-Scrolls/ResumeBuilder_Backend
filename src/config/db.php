<?php
    $host = 'localhost';
    $port = 3306;
    $db = 'resume_builder';
    $user = "phpuser";
    $password = "Php##0923";

    
    try{
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "DB connected successfully";
    }catch(PDOException $e){
        die("DB connection failed: " . $e->getMessage());
    }
?>