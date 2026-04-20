<?php
require_once __DIR__.'/../../../models/Education.php';
require_once __DIR__."/../../../config/db.php";
require __DIR__.'/../../../helpers/response.php';

$resume_id = $_GET['id'] ?? null;
if (!$resume_id) {
    jsonResponse(400, false, "Resume id is required");
}

$education = new Education($conn);
$data = $education->getByResumeId($resume_id);

jsonResponse(200, true, "Education entries fetched", $data);
