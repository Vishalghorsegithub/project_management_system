<?php
// -------------------- HEADERS --------------------
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// -------------------- ERROR REPORTING --------------------
// Turn ON during debugging on server
error_reporting(E_ALL);
ini_set('display_errors', 1);

// -------------------- SAFE JSON RESPONSE FUNCTION --------------------
function send_json($payload, int $code = 200)
{
    http_response_code($code);

    if (ob_get_length()) {
        ob_clean(); // remove accidental output
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// -------------------- INCLUDE USER MODEL --------------------
require_once __DIR__ . "/User.php";   // absolute path → avoids Linux issues

$user = new User();

// -------------------- READ INPUT --------------------
$data = [];

// JSON input (fetch)
$json = json_decode(file_get_contents("php://input"), true);
if (is_array($json)) {
    $data = $json;
}

// Form-data fallback
if (empty($data) && !empty($_POST)) {
    $data = $_POST;
}

// If still invalid
if (!is_array($data)) {
    send_json([
        "status" => false,
        "message" => "Invalid or empty request body"
    ], 400);
}

$function = $data["function"] ?? "";

// -------------------- ROUTER --------------------
try {

    // --------------------------------
    // LOGIN
    // --------------------------------
    if ($function === "login") {

        $email = $data["email"] ?? "";
        $password = $data["password"] ?? "";

        send_json($user->login($email, $password));
    }

    // --------------------------------
    // CREATE USER
    // --------------------------------
    if ($function === "createUser") {

        $name = $data["name"] ?? "Vishal";
        $email = $data["email"] ?? "vishal@gmail.com";
        $mobile = $data["mobile"] ?? "6263294002";
        $role_id = $data["role_id"] ?? 1;
        $password = "123";

        send_json($user->createUser($name, $email, $mobile, $password, $role_id));
    }

    // --------------------------------
    // CHECK-IN
    // --------------------------------
    if ($function === "check_in") {

        $latitude = $data["latitude"] ?? "";
        $longitude = $data["longitude"] ?? "";

        send_json($user->check_in($latitude, $longitude));
    }

    // --------------------------------
    // CHECK-OUT
    // --------------------------------
    if ($function === "check_out") {

        $latitude = $data["latitude"] ?? "";
        $longitude = $data["longitude"] ?? "";

        send_json($user->check_out($latitude, $longitude));
    }


    //  USER PROFILE 

    if ($function === "get_user_profile") {
        $email = $data["email"] ?? "";
        $response = $user->get_user_profile($email);
        send_json($response);

    }



    if ($function === "update_profile") {
        $response = $user->update_profile($data);
        send_json($response);
    }

    if ($function === "get_company_profile") {
        $response = $user->get_company_profile();
        send_json($response);
    }



    // --------------------------------
    // LOGOUT
    // --------------------------------
    if ($function === "logout") {
        send_json($user->logout());
    }




    // --------------------------------
    // INVALID FUNCTION
    // --------------------------------
    send_json([
        "status" => false,
        "message" => "Unknown function: {$function}"
    ], 404);

} catch (Throwable $e) {

    // Return JSON even when PHP fails
    send_json([
        "status" => false,
        "message" => "Server Error",
        "error" => $e->getMessage()  // remove in production
    ], 500);
}
?>