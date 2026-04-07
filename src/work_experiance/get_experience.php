<?php
// Tells the browser/Postman to expect JSON
header('Content-Type: application/json');

// 1. Simple Connection (No .env or require_once)
$host = "localhost";
$db   = "resume_db";
$user = "root";
$pass = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. We use Mock ID 1 for now
    $resume_id = 1; 

    // 3. Simple Select Query
    $sql = "SELECT * FROM work_experience WHERE resume_id = :rid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':rid' => $resume_id]);
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Success Response
    echo json_encode([
        "status" => "success",
        "count" => count($data),
        "work_history" => $data
    ]);

} catch(PDOException $e) {
    // 5. Error Response
    echo json_encode([
        "status" => "error", 
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>