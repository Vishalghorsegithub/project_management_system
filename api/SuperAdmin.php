<?php
// -------------------- SESSION --------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------- HEADERS --------------------
header("Access-Control-Allow-Origin: *");   // Change to specific domain if using credentials
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// -------------------- ERROR HANDLING --------------------
// While debugging on server:
error_reporting(E_ALL);
ini_set("display_errors", 1);

// -------------------- SAFE JSON OUTPUT FUNCTION --------------------
function send_json($payload, int $http = 200)
{
    http_response_code($http);

    if (ob_get_length()) {
        ob_clean();  // Remove any previous unwanted output
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// -------------------- INCLUDE MODEL --------------------
require_once __DIR__ . "/SuperAdmin_info.php";  // Absolute path to avoid Linux file-case issues

$admin = new SuperAdmin_info();

// -------------------- READ DATA --------------------
$data = [];

// 1) JSON raw input (fetch)
$json = json_decode(file_get_contents("php://input"), true);
if (is_array($json)) {
    $data = $json;
}

// 2) Fallback to form-data (POST)
if (empty($data) && !empty($_POST)) {
    $data = $_POST;
}

// If request still empty → invalid
if (!is_array($data)) {
    send_json([
        "status" => false,
        "message" => "Invalid request format"
    ], 400);
}

$function = $data["function"] ?? "";

// -------------------- ROUTER --------------------
try {

    if ($function === "create_user") {
        send_json($admin->create_user());
    }

    if ($function === "fetch_all_users_display") {
        send_json($admin->fetch_all_users_display());
    }

    // ANALYTICS DISPLAY
    if ($function === "fetch_all_users") {
        send_json($admin->fetch_all_users());
    }

    if ($function === "fetch_user_graph") {

        $user_id = intval($data["user_id"] ?? 0);

        if ($user_id <= 0) {
            send_json([
                "status" => false,
                "message" => "Invalid user_id"
            ], 400);
        }

        send_json($admin->fetch_user_graph($user_id));
    }

    if ($function === "fetch_user_attendance") {

        $user_id = intval($data["user_id"] ?? 0);
        $month = $data["month"] ?? "";

        if ($user_id <= 0) {
            send_json([
                "status" => false,
                "message" => "Invalid user_id"
            ], 400);
        }

        send_json($admin->fetch_user_attendance($user_id, $month));
    }

    if ($function === "fetch_user_activity") {

        $user_id = intval($data["user_id"] ?? 0);
        $fromDate = $data["from_date"] ?? "";
        $toDate = $data["to_date"] ?? "";
        $page = intval($data["page"] ?? 1);
        $limit = intval($data["limit"] ?? 10);

        if ($user_id <= 0) {
            send_json([
                "status" => false,
                "message" => "Invalid user_id"
            ], 400);
        }

        send_json($admin->fetch_user_activity(
            $user_id,
            $fromDate,
            $toDate,
            $page,
            $limit
        ));
    }

    if ($function === "update_company_profile") {
        send_json($admin->update_company_profile($data));
    }




    // If function does not match anything
    send_json([
        "status" => false,
        "message" => "Invalid function name"
    ], 404);

} catch (Throwable $e) {

    // Return clean JSON instead of HTML fatal error
    send_json([
        "status" => false,
        "message" => "Server Error",
        "error" => $e->getMessage()  // REMOVE IN PRODUCTION for security
    ], 500);
}

?>