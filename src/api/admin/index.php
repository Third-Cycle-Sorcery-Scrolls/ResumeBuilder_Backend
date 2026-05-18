<?php
require_once __DIR__."/../../config/db.php";


require '../../helpers/response.php';
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $type = $_GET['type'] ?? null;
    //type can be users,resumes,Templates,analytics
    if (!$type) {
        jsonResponse(400, false, "Type required");
    }

    if ($type == "users") {
        $sql = $conn->prepare("SELECT id, name, email,role FROM users");
        $sql->execute();
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(200, true, "Users fetched", $data);
    } else if ($type == "resumes") {
        $sql = $conn->prepare("SELECT r.id, r.title,r.template,u.name as user_name FROM resumes r JOIN users u ON r.user_id = u.id");
        $sql->execute();
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(200, true, "Resumes fetched", $data);
    } else if ($type == "dashboard") {
        #total_users,total_resumes,templates,active_users
        #recent activity from logs
        /*
        output format:-
         {
        analytics:{},
        activity:[{},{}]
        }
        */
    }


} else {
    jsonResponse(405, false, "Method not allowed");
}
