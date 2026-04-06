<?php


require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";

require '../../helpers/response.php';


$id = $_GET['id'] ?? null;

if (!$id) {
    jsonResponse(400, false, "ID required");
}

$resume = new Resume($conn);
$data = $resume->getById($id);

if (!$data) {
    jsonResponse(404, false, "Resume not found");
}

jsonResponse(200, true, "Resume fetched", $data);