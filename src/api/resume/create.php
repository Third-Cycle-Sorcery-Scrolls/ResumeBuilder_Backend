<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";

require '../../helpers/response.php';



$data = json_decode(file_get_contents("php://input"), true);

$title = $data['title'] ?? null;
$template = $data['template'] ?? "template1";

if (!$title) {
    jsonResponse(400, false, "Title is required");
}
echo "creating tempalte";

$resume = new Resume($conn);
$id = $resume->create($user_id, $title, $template);

jsonResponse(201, true, "Resume created", ["id" => $id]);