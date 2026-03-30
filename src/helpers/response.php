<?php
    function jsonResponse($code, $success, $message, $data=null, $error = null){
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data,
            "error" => $error
        ]);
        exit;
    }
?>