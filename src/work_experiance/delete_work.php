<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=resume_db", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;

        if (empty($id)) {
            echo json_encode(["status" => "error", "message" => "ID is required!"]);
            exit;
        }

        $sql = "DELETE FROM work_experience WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // rowCount() tells us if something actually disappeared
        $count = $stmt->rowCount();

        if ($count > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "Successfully deleted $count row(s)."
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Success call, but 0 rows deleted. Check if ID $id actually exists in the 'id' column!"
            ]);
        }
    }
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}