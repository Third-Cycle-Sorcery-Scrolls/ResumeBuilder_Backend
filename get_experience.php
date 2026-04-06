<?php
// This tells the browser/Dev-6 to expect JSON data
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "root"; 
$dbname = "resume_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    
    // We are using Mock ID 1 for now
    $resume_id = 1; 

    $sql = "SELECT * FROM work_experience WHERE resume_id = :rid";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':rid' => $resume_id]);
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // // Loop through each work entry and format the description
    // foreach ($data as &$entry) {
    // $entry['description'] = nl2br($entry['description']);
}
    // If data exists, send it. If not, send an empty list.
    echo json_encode([
        "status" => "success",
        "count" => count($data),
        "work_history" => $data
    ]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>