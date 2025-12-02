<?php
session_start();

// CORS (⚠ if you use credentials: 'include' in fetch, read note below)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

// For debugging on server, enable these temporarily:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Always use absolute path for safety (Linux is case-sensitive)

require_once __DIR__ . "/Project_info.php";
function send_json($payload, int $code = 200)
{
    http_response_code($code);
    // Clean any accidental previous output (spaces, BOM etc.)
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Read JSON body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// If body is not valid JSON
if (!is_array($data)) {
    send_json([
        "status"  => false,
        "message" => "Invalid Request Body",
        "raw"     => $raw,    // remove in production if you want
    ], 400);
}

$function     = $data["function"] ?? "";
$project_name = $data["project_name"] ?? "";

$project = new project_info();

try {

    if ($function === 'create_project') {
        $response = $project->create_project($project_name);
        send_json($response);
    }

    if ($function === 'fetch_projects_name') {
        $response = $project->fetch_projects_name();
        send_json($response);
    }

    if ($function === "save_project_log") {
        $response = $project->save_project_log($data);
        send_json($response);
    }

    if ($function === "fetch_recent_logs") {
        $response = $project->fetch_recent_logs();
        send_json($response);
    }

    if ($function === "fetch_this_month_logs") {
        $startTime = $data["startTime"] ?? "";
        $endTime   = $data["endTime"] ?? "";
        $response  = $project->fetch_this_month_logs($startTime, $endTime);
        send_json($response);
    }

    if ($function === "fetch_all_activity_logs") {
        $startTime = $data["startTime"] ?? "";
        $endTime   = $data["endTime"] ?? "";
        $limit     = $data["limit"] ?? 10;
        $offset    = $data["offset"] ?? 0;
        $response  = $project->fetch_all_activity_logs($startTime, $endTime, $limit, $offset);
        send_json($response);
    }

    if ($function === "fetch_graph_data") {
        $startTime = $data["startTime"] ?? "";
        $endTime   = $data["endTime"] ?? "";
        $response  = $project->fetch_graph_data($startTime, $endTime);
        send_json($response);
    }

    if ($function === "fetch_attendance") {
        $month    = $data["month"] ?? "";
        $response = $project->fetch_attendance($month);
        send_json($response);
    }


    if ($function === "delete_time_entry") {
        $entry_id = $data["entry_id"] ?? "";
        $response = $project->delete_time_entry($entry_id);
        send_json($response);
    }

    // If invalid function
    send_json([
        "status"  => false,
        "message" => "Unknown function: " . $function
    ], 400);

} catch (Throwable $e) {
    // In production, log this instead of sending full message to client
    send_json([
        "status"  => false,
        "message" => "Server error",
        "error"   => $e->getMessage(), // remove in production if needed
    ], 500);
}
