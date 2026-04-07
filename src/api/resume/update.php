<?php
require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";

require '../../helpers/response.php';



$id = $_GET['id'] ?? null;

$data = json_decode(file_get_contents("php://input"), true);

$title = $data['title'] ?? null;
$template = $data['template'] ?? null;

$resume = new Resume($conn);

$resume->update($id, $title, $template);

jsonResponse(200, true, "Resume updated");