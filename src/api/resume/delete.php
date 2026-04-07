<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";

require '../../helpers/response.php';



$id = $_GET['id'] ?? null;

$resume = new Resume($conn);
$resume->delete($id);

jsonResponse(200, true, "Resume deleted");