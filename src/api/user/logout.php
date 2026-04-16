<?php

require_once __DIR__ . '/../../helpers/response.php';

header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // For demo, we just return a success message.
    jsonResponse(200, true, "Logout successful", null, null);
}else{
    jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
}
?>