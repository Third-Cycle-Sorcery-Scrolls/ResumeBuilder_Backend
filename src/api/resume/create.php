<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/logger.php';

try {
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError($conn, "Resume create route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'] ?? null;
    $title = $data['title'] ?? null;
    $template = $data['template'] ?? "template1";

    if (!$user_id) {

        logError(
            $conn,
            "Resume creation failed: missing user_id",
            __FILE__,
            __LINE__,
            null
        );

        jsonResponse(400, false, "User ID is required");
    }

    if (!$title) {

        logError(
            $conn,
            "Resume creation failed: missing title",
            __FILE__,
            __LINE__,
            $user_id
        );

        jsonResponse(400, false, "Title is required");
    }

    $resume = new Resume($conn);

    $id = $resume->create($user_id, $title, $template);

    if (!$id) {

        logError(
            $conn,
            "Resume creation failed for user_id=$user_id",
            __FILE__,
            __LINE__,
            $user_id
        );

        jsonResponse(500, false, "Could not create resume");
    }

    jsonResponse(201, true, "Resume created", ["id" => $id]);

} catch (Exception $e) {

    logError(
        $conn,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $user_id ?? null
    );

    jsonResponse(500, false, "Internal Server Error");
}