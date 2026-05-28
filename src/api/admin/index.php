<?php
require_once __DIR__."/../../config/db.php";
require_once __DIR__."/../../helpers/response.php";
require_once __DIR__.'/../../helpers/debug.php';
require_once __DIR__.'/../../helpers/auth.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");


if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verify user is authenticated and is admin
try {
    $user = authenticateAdmin();
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized", null, "Admin authentication required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $type = $_GET['type'] ?? null;
    //type can be users,resumes,Templates,analytics
    if (!$type) {
        jsonResponse(400, false, "Type required");
    }

    if ($type == "users") {
        $sql = $conn->prepare("SELECT id, name, email,role,profile_picture FROM users");
        $sql->execute();
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(200, true, "Users fetched", $data);
    } else if ($type == "resumes") {
        $sql = $conn->prepare("SELECT r.id, r.title,r.template,u.name as user_name FROM resumes r JOIN users u ON r.user_id = u.id");
        $sql->execute();
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(200, true, "Resumes fetched", $data);
    } else if ($type == "dashboard") {
        debug("GET request in /api/admin/index.php for dashboard analytics", $_GET);
        #total_users,total_resumes,templates,active_users
        #recent activity from logs
        /*
        output format:-
         {
        analytics:{
        total_users:100,
        total_resumes:200,
        templates:10,
        active_users:50(using activity logs in last 24 hours)
        most_used_template:[
         {
        template_name:"template1",
        usage_count:50
          },
        {
        template_name:"template2",
        usage_count:30}
        ]
        },
        activity:[{},{}],
        weekly_resume_creation:[
        { 
        week:"2023-01-01",
        count:10
        },
        {
        week:"2023-01-08",
        count:20
            }
        ]
        }
        */
        // var_dump("dashboard");
        $analytics = [];
        $sql = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
        $sql->execute();
        $analytics['total_users'] = $sql->fetch(PDO::FETCH_ASSOC)['total_users'];
        $sql = $conn->prepare("SELECT COUNT(*) as total_resumes FROM resumes");
        $sql->execute();

        $analytics['total_resumes'] = $sql->fetch(PDO::FETCH_ASSOC)['total_resumes'];
        #TODO: add templates table to manage templates and count them here
        // $sql = $conn->prepare("SELECT COUNT(*) as templates FROM templates");
        // $sql->execute();
        // $analytics['templates'] = $sql->fetch(PDO::FETCH_ASSOC)['templates'];
        $analytics['templates'] = 2; //hardcoded for now
        // var_dump("analytics:" , $analytics);
        $sql = $conn->prepare("SELECT COUNT(DISTINCT user_id) as active_users FROM logs WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $sql->execute();

        $analytics['active_users'] = $sql->fetch(PDO::FETCH_ASSOC)['active_users'];
        $sql = $conn->prepare("SELECT a.*,u.name as user_name FROM logs a JOIN users u ON a.user_id = u.id ORDER BY a.timestamp DESC LIMIT 10");
        $sql->execute();
        $activity = $sql->fetchAll(PDO::FETCH_ASSOC);

        //template usage with user count in last 30 days
        $sql = $conn->prepare("SELECT template, COUNT(*) as usage_count FROM resumes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY template ORDER BY usage_count DESC");
        $sql->execute();
        $analytics['most_used_template'] = $sql->fetchAll(PDO::FETCH_ASSOC);
        //weekly resume creation for last 8 weeks
        $sql = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%u') as week, COUNT(*) as count FROM resumes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK) GROUP BY week ORDER BY week ASC");
        $sql->execute();
        $analytics['weekly_resume_creation'] = $sql->fetchAll(PDO::FETCH_ASSOC);

        // var_dump($analytics);
        jsonResponse(200, true, "Dashboard data fetched", ['analytics' => $analytics, 'activity' => $activity]);
    }


} else {
    jsonResponse(405, false, "Method not allowed");
}
