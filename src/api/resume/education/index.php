<?php


    // // EDUCATION
    // $pdo->exec("
    //     CREATE TABLE IF NOT EXISTS education (
    //         id INT AUTO_INCREMENT PRIMARY KEY,
    //         resume_id INT,
    //         institution VARCHAR(255),
    //         degree VARCHAR(255),
    //         field_of_study VARCHAR(255),
    //         start_date DATE,
    //         end_date DATE,
    //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    //         FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
    //     );
    // ");
    require_once "../../../config/db.php";
    require_once "../../../models/Education.php";
    require_once "../../../helpers/response.php";
    require_once "../../../helpers/debug.php";

    // Set CORS headers for React frontend
    header("Access-Control-Allow-Origin: *");

    // CORS
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $data = json_decode(file_get_contents("php://input"), true);

        $resume_id = $data['resume_id'] ?? null;
        $education = $data['education'] ?? null;
        if(!$resume_id || !$education) {
            jsonResponse(400, false, "Resume ID and education data are required");
        }


        $edu_instance = new Education($conn);

        foreach($education as $edu) {
               $institution = $edu['institution'] ?? null;
                $degree = $edu['degree'] ?? null;
                $field_of_study = $edu['fieldOfStudy'] ?? null;
                $start_date = $edu['startDate'] ?? null;
                $end_date = $edu['endDate'] ?? null;
                $description = $edu['description'] ?? null;
                if(!$resume_id || !$institution || !$degree || !$field_of_study || !$start_date || !$end_date){
                    jsonResponse(400, false, "All fields are required");
                }
                $id = $edu_instance->create($resume_id, $institution, $degree, $field_of_study, $start_date, $end_date, $description);
                if($id){
                    continue;
                } else {
                    jsonResponse(500, false, "Failed to create education entry");
                }
        }
        jsonResponse(201, true, "Education entry created", ["id" => $id]);
    } else if($_SERVER['REQUEST_METHOD'] == 'GET'){
        $education = new Education($conn);
        $entries = $education->getAll();
        jsonResponse(200, true, "Education entries retrieved", $entries);
    } else if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])){
        $id = $_GET['id'];
        $education = new Education($conn);
        $entry = $education->getById($id);
        if($entry){
            jsonResponse(200, true, "Education entry retrieved", $entry);
        } else {
            jsonResponse(404, false, "Education entry not found");
        }
    } else if($_SERVER['REQUEST_METHOD'] == 'PUT'){
        
        
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['resume_id'] ?? null;

        $educations = $data['education'] ?? [];
        debug("PUT request in /api/resume/education/index.php for ID: " . $id, $educations);
        $failure = 0;
        foreach($educations as $education) {


            $institution = $data['institution'] ?? null;
            $degree = $data['degree'] ?? null;
            $field_of_study = $data['field_of_study'] ?? null;
            $start_date = $data['start_date'] ?? null;
            $end_date = $data['end_date'] ?? null;
            $description = $data['description'] ?? null;
            // if(!$institution || !$degree || !$field_of_study || !$start_date || !$end_date){
            //     jsonResponse(400, false, "All fields are required");
            // }

            $education = new Education($conn);
            $updated = $education->update($id, $institution, $degree, $field_of_study, $start_date, $end_date, $description);
            if (!$updated) {
                $failure++;
            }
        }
        debug("Education update failure count: " . $failure, null);
        if ($failure == count($educations) && $failure > 0) {
            jsonResponse(500, false, "Failed to update some education entries");
        } else {
            jsonResponse(200, true, "Education entry updated");
        }
        
            
    } else if($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])){
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM education WHERE id = ?");
        if($stmt->execute([$id])){
            jsonResponse(200, true, "Education entry deleted");
        } else {
            jsonResponse(500, false, "Failed to delete education entry");
        }
    } else {
        jsonResponse(405, false, "Method Not Allowed");
    }


