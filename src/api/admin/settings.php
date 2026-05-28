<?php
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/auth.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

$settingsFile = __DIR__ . "/settings.store.json";
$defaultSettings = [
    "siteTitle" => "Resume Builder",
    "allowRegistration" => true,
    "maintenanceMode" => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function readSettings($file, $defaults) {
    if (!file_exists($file)) {
        return $defaults;
    }

    $raw = file_get_contents($file);
    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        return $defaults;
    }

    return array_merge($defaults, $decoded);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    jsonResponse(200, true, "Settings fetched successfully", readSettings($settingsFile, $defaultSettings));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        authenticateAdmin();
    } catch (Exception $e) {
        jsonResponse(401, false, "Unauthorized", null, "Admin authentication required");
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        jsonResponse(400, false, "Invalid JSON payload");
        exit();
    }

    $settings = array_merge($defaultSettings, [
        "siteTitle" => trim((string)($input['siteTitle'] ?? $defaultSettings['siteTitle'])),
        "allowRegistration" => (bool)($input['allowRegistration'] ?? false),
        "maintenanceMode" => (bool)($input['maintenanceMode'] ?? false),
    ]);

    $written = file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

    if ($written === false) {
        jsonResponse(500, false, "Failed to save settings");
        exit();
    }

    jsonResponse(200, true, "Settings saved successfully", $settings);
    exit();
}

jsonResponse(405, false, "Method not allowed");
