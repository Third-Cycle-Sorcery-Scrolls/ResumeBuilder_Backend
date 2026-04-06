<?php
header('Content-Type: application/json');

// MAMP Connection
$host = "localhost";
$user = "root";
$pass = "root"; 
$dbname = "resume_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    
    // Check if the request is a POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1. Collect data from $_POST
        // Using Mock ID 1 for now until Dev-2 is ready
        $resume_id = 1; 
        $company = $_POST['company_name'] ?? '';
        $title = $_POST['job_title'] ?? '';
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? null; // Can be empty
        $desc = $_POST['description'] ?? '';

        // 2. Simple Validation: Company and Title shouldn't be empty
        if (empty($company) || empty($title)) {
            echo json_encode(["status" => "error", "message" => "Company and Title are required!"]);
            exit;
        }

        // 3. Prepare SQL
        $sql = "INSERT INTO work_experience (resume_id, company_name, job_title, start_date, end_date, description) 
                VALUES (:rid, :comp, :title, :start, :end, :desc)";
        
        $stmt = $conn->prepare($sql);
        
        // 4. Execute
        $stmt->execute([
            ':rid'   => $resume_id,
            ':comp'  => $company,
            ':title' => $title,
            ':start' => $start,
            ':end'   => $end,
            ':desc'  => $desc
        ]);

        echo json_encode([
            "status" => "success", 
            "message" => "Work experience saved successfully!",
            "inserted_id" => $conn->lastInsertId()
        ]);
    }
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>