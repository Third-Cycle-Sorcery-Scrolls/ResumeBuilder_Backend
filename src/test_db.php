<?php
$dsn = "mysql:host=localhost;dbname=resume_builder;charset=utf8mb4";
$username = "phpuser";
$password = "Php##0923";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully with PDO";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>