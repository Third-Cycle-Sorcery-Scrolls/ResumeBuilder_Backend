<?php
    $host = 'localhost';
    $port = 3306;
    $db = 'resume_builder';
    $user = 'root';
    $password = '';
    
    if (!$user) $user = 'root';
    if ($password === false) $password = '';

    try{
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
        throw new Exception("Database connection failed");
    }
?>