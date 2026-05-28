<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
    
        $resume_id = $data['resume_id'] ?? null;
        $experience = $data['experience'] ?? [];
    
        if (!$resume_id) {
            jsonResponse(400, false, "resume_id is required");
            exit();
        }
    
        if (empty($experience)) {
            jsonResponse(400, false, "Experience data is required");
            exit();
        }
    
        $conn->beginTransaction();
    
        $sql = "INSERT INTO work_experience 
            (resume_id, company, position, start_date, end_date, description) 
            VALUES (:rid, :comp, :pos, :start, :end, :desc)";
    
        $stmt = $conn->prepare($sql);
    
        $insertedIds = [];
    
        foreach ($experience as $exp) {
            $position = $exp['position'] ?? $exp['jobTitle'] ?? null;
            $startDate = $exp['start_date'] ?? $exp['startDate'] ?? null;
            $endDate = $exp['end_date'] ?? $exp['endDate'] ?? null;
            $description = $exp['description'] ?? $exp['duration'] ?? null;

            $success = $stmt->execute([
                ':rid'   => $resume_id,
                ':comp'  => $exp['company'] ?? null,
                ':pos'   => $position,
                ':start' => $startDate,
                ':end'   => $endDate,
                ':desc'  => $description
            ]);
    
            if (!$success) {
                $conn->rollBack(); 
                jsonResponse(500, false, "Failed to insert experience");
                exit();
            }
    
            $insertedIds[] = $conn->lastInsertId();
        }
    
        $conn->commit();
    
        jsonResponse(201, true, "Work experience saved successfully!", [
            "inserted_ids" => $insertedIds
        ]);
        exit();
    
    } catch (PDOException $e) {
    
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
    
        jsonResponse(500, false, "Database error", null, $e->getMessage());
        exit();
    }

} else if ($_SERVER['REQUEST_METHOD'] === "PUT") {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $resume_id = $data['resume_id'] ?? null;
        if (!$resume_id) {
            jsonResponse(400, false, "resume_id is required for update");
            exit();
        }
        $experience = $data['experience'] ?? [];
        if (empty($experience)) {
            jsonResponse(400, false, "Experience data is required for update");
            exit();
        }

        $conn->beginTransaction();
        try {
            $sql = "UPDATE work_experience 
                    SET company = :comp, position = :pos, start_date = :start, end_date = :end, description = :desc, updated_at = NOW()
                    WHERE id = :id AND resume_id = :rid";
            $stmt = $conn->prepare($sql);
            foreach ($experience as $exp) {
                $id = $exp['id'] ?? null;
                if (!$id) {
                    $conn->rollBack();
                    jsonResponse(400, false, "Each experience entry must have an ID for update");
                    exit();
                }

                $position = $exp['position'] ?? $exp['jobTitle'] ?? null;
                $startDate = $exp['start_date'] ?? $exp['startDate'] ?? null;
                $endDate = $exp['end_date'] ?? $exp['endDate'] ?? null;
                $description = $exp['description'] ?? $exp['duration'] ?? null;

                $success = $stmt->execute([
                    ':id'    => $id,
                    ':rid'   => $resume_id,
                    ':comp'  => $exp['company'] ?? null,
                    ':pos'   => $position,
                    ':start' => $startDate,
                    ':end'   => $endDate,
                    ':desc'  => $description
                ]);
                if (!$success) {
                    $conn->rollBack();
                    jsonResponse(500, false, "Failed to update experience with ID: $id");
                    exit();
                }
            }
            $conn->commit();
            jsonResponse(200, true, "Work experience updated successfully!");
            exit();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            jsonResponse(500, false, "Database error during update", null, $e->getMessage());
            exit();
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        jsonResponse(500, false, "Database error", null, $e->getMessage());
        exit();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            jsonResponse(400, false, "id is required for delete");
            exit();
        }
        $sql = "DELETE FROM work_experience WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(200, true, "Work experience deleted successfully");
        } else {
            jsonResponse(404, false, "Work experience not found");
        }
        exit();
    } catch (PDOException $e) {
        jsonResponse(500, false, "Database error", null, $e->getMessage());
        exit();
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
    exit();
}