<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=resume_db", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1. We need the ID of the row to update
        $id = $_POST['id'] ?? null;
        $company = $_POST['company_name'] ?? '';
        $title = $_POST['job_title'] ?? '';
        $desc = $_POST['description'] ?? '';

        if (empty($id)) {
            echo json_encode(["status" => "error", "message" => "ID is required for update!"]);
            exit;
        }

        // 2. Prepare the Update SQL
        $sql = "UPDATE work_experience 
                SET company_name = :comp, job_title = :title, description = :desc 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'    => $id,
            ':comp'  => $company,
            ':title' => $title,
            ':desc'  => $desc
        ]);

        echo json_encode(["status" => "success", "message" => "Work experience updated!"]);
    }
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>