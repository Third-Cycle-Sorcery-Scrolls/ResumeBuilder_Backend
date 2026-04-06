<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";

require '../../helpers/response.php';




$resume = new Resume($conn);
$data = $resume->getAll();

jsonResponse(200, true, "Resumes fetched", $data);