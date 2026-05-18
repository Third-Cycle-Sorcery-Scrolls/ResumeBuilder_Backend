<?php


// personal_info.php
require_once __DIR__.'/../../models/Log.php';
require_once __DIR__."/../../config/db.php";

require '../../helpers/response.php';
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
//get and post request for logs
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $log = new Log($conn);
    $data = $log->getAll();
    jsonResponse(200, true, "Logs fetched", $data);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? null;
    $action = $data['action'] ?? null;
    if (!$action) {
        jsonResponse(400, false, "Action is required");
    }
    //country of the user performing the action
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = "http://ip-api.com/json/$ip";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    $country = $data['country'] ?? 'Unknown';
    $city = $data['city'] ?? 'Unknown';

    $log = new Log($conn);
    //update action with country
    if ($country != 'Unknown') {
        $action .= " from $country, $city";
    }
    $log_id = $log->create($user_id, $action);
    if ($log_id) {
        jsonResponse(200, true, "Log created", ['log_id' => $log_id]);
    } else {
        jsonResponse(500, false, "Failed to create log");
    }
} else {
    jsonResponse(405, false, "Method not allowed");
}