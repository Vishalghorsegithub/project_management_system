<?php
require_once "../db.php";

class User
{

    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function login($email, $password)
    {
        $email = $this->conn->real_escape_string($email);

        // Fetch user + role + today's attendance
        $sql = "SELECT 
                u.id AS user_id,
                u.name AS user_name,
                u.email,
                u.mobile,
                u.password,
                r.role AS role,
                a.check_in,
                a.check_out
            FROM tbl_users u
            LEFT JOIN tbl_role_master r ON u.role_id = r.id
            LEFT JOIN tbl_attendance a 
                    ON u.id = a.user_id 
                   AND a.date = CURDATE()
            WHERE u.email = '$email'
            LIMIT 1";

        $res = $this->conn->query($sql);

        if ($res->num_rows == 0) {
            return [
                "status" => false,
                "message" => "Email not found"
            ];
        }

        $user = $res->fetch_assoc();

        // Fetch company information BEFORE closing the connection
        $companyQuery = "
        SELECT 
            open_time,
            close_time,
            (TIME_TO_SEC(close_time) - TIME_TO_SEC(open_time)) / 60 AS total_minutes
        FROM tbl_company_information 
        LIMIT 1
    ";
        $companyRes = $this->conn->query($companyQuery);

        $company_data = ($companyRes && $companyRes->num_rows > 0)
            ? $companyRes->fetch_assoc()
            : ["total_minutes" => 0];

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [
                "status" => false,
                "message" => "Incorrect password"
            ];
        }

        // Save user session
        session_start();

        $_SESSION["user"] = [
            "user_id" => $user['user_id'],
            "name" => $user['user_name'],
            "email" => $user['email'],
            "mobile" => $user['mobile'],
            "role" => $user['role'],
            "day_minutes" => $company_data['total_minutes'], // total shift minutes
            "checked_in" => !empty($user["check_in"]) ? "Y" : "N",
            "checked_out" => !empty($user["check_out"]) ? "Y" : "N"
        ];

        // Decide redirect
        $redirect = ($user['role'] == 'SuperAdmin')
            ? 'superadmin_dashboard.php'
            : 'dashboard.php';

        return [
            "status" => true,
            "message" => "Login successful",
            "data" => [
                "user_id" => $user['user_id'],
                "name" => $user['user_name'],
                "email" => $user['email'],
                "mobile" => $user['mobile'],
                "role" => $user['role'],
                "redirect" => $redirect
            ]
        ];
    }




    public function createUser($name, $email, $mobile, $password, $role_id)
    {
        // Escape inputs
        $name = $this->conn->real_escape_string($name);
        $email = $this->conn->real_escape_string($email);
        $mobile = $this->conn->real_escape_string($mobile);
        $role_id = (int) $role_id;

        // Validate required fields
        if (empty($name) || empty($email) || empty($password)) {
            return [
                "status" => false,
                "message" => "Name, email, and password are required"
            ];
        }

        // Check if email already exists
        $checkSql = "SELECT id FROM tbl_users WHERE email='$email' LIMIT 1";
        $checkRes = $this->conn->query($checkSql);

        if ($checkRes->num_rows > 0) {
            return [
                "status" => false,
                "message" => "Email already registered"
            ];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert User
        $sql = "INSERT INTO tbl_users (name, email, mobile, password, role_id)
            VALUES ('$name', '$email', '$mobile', '$hashedPassword', '$role_id')";

        if ($this->conn->query($sql) === TRUE) {
            return [
                "status" => true,
                "message" => "User created successfully",
                "user_id" => $this->conn->insert_id
            ];
        } else {
            return [
                "status" => false,
                "message" => "Failed to create user",
                "error" => $this->conn->error
            ];
        }
    }


    public function check_in($lat, $long)
    {
        session_start();
        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized User"
            ];
        }


        $user_lat = $lat ?? null;
        $user_long = $long ?? null;


        if (!$user_lat || !$user_long) {
            return [
                "status" => false,
                "message" => "Location (lat/long) required for check-in"
            ];
        }

        // 1️⃣ Prevent double check-in same day
        $checkSql = "SELECT id FROM tbl_attendance 
                 WHERE user_id = '$user_id' AND date = CURDATE() LIMIT 1";
        $checkRes = $this->conn->query($checkSql);

        if ($checkRes->num_rows > 0) {
            return [
                "status" => false,
                "message" => "Already checked in today"
            ];
        }

        // 2️⃣ Fetch company settings
        $sql_company = "SELECT * FROM tbl_company_information LIMIT 1";
        $companyRes = $this->conn->query($sql_company);

        if (!$companyRes || $companyRes->num_rows == 0) {
            return [
                "status" => false,
                "message" => "Company settings missing"
            ];
        }

        $company = $companyRes->fetch_assoc();

        // Company GPS
        $company_lat = $company['lat'];
        $company_long = $company['long'];

        // 3️⃣ Calculate Distance (Haversine Formula)
        $distance = $this->calculate_distance($user_lat, $user_long, $company_lat, $company_long);

        if ($distance > 100) { // more than 100 meters  $distance > 100
            return [
                "status" => false,
                "message" => "You must be within 100 meters of the office to check in",
                "distance_in_meters" => round($distance)
            ];
        }

        // 4️⃣ Time status (Present / Late)
        $company_start_time = date("H:i:s", strtotime($company['open_time']));
        $current_time = date("H:i:s");
        $grace_period_time = date("H:i:s", strtotime($company_start_time . " + {$company['grace_period_mins']} minutes"));

        if ($current_time <= $grace_period_time) {
            $status = "Present";
        } else {
            $status = "Late";
        }

        // 5️⃣ Insert Attendance
        $sql = "INSERT INTO tbl_attendance (user_id, date, check_in, status)
            VALUES ('$user_id', CURDATE(), NOW(), '$status')";

        if ($this->conn->query($sql)) {

            $_SESSION["user"]["checked_in"] = "Y";

            return [
                "status" => true,
                "message" => "Check-in successful",
                "attendance_status" => $status,
                "distance_in_meters" => round($distance)
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to check in",
            "error" => $this->conn->error
        ];
    }


    private function calculate_distance($lat1, $lon1, $lat2, $lon2)
    {
        // Validate input
        if (!is_numeric($lat1) || !is_numeric($lon1) || !is_numeric($lat2) || !is_numeric($lon2)) {
            return 999999999; // Fail-safe (invalid coordinates)
        }

        $earth_radius = 6371000; // meters

        // Convert degrees → radians ONCE
        $lat1_rad = deg2rad($lat1);
        $lon1_rad = deg2rad($lon1);
        $lat2_rad = deg2rad($lat2);
        $lon2_rad = deg2rad($lon2);

        // Differences
        $dLat = $lat2_rad - $lat1_rad;
        $dLon = $lon2_rad - $lon1_rad;

        // Haversine formula
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos($lat1_rad) * cos($lat2_rad)
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c; // distance in meters
    }


    public function check_out($lat, $long)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return ["status" => false, "message" => "Unauthorized User"];
        }

        // -----------------------------------------------------
        // 1️⃣ COMPANY INFO
        // -----------------------------------------------------
        $companySql = "SELECT 
                        `lat` AS latitude, 
                        `long` AS longitude,
                        open_time, 
                        close_time, 
                        grace_period_mins 
                   FROM tbl_company_information 
                   LIMIT 1";

        $companyRes = $this->conn->query($companySql);
        $company = $companyRes->fetch_assoc();

        $company_lat = floatval($company["latitude"]);
        $company_lng = floatval($company["longitude"]);
        $company_open = $company["open_time"];
        $company_close = $company["close_time"];
        $grace_minutes = intval($company["grace_period_mins"]);

        // -----------------------------------------------------
        // 2️⃣ GPS CHECK (100 meters)
        // -----------------------------------------------------
        $distance = $this->calculate_distance($lat, $long, $company_lat, $company_lng);

        if ($distance > 100) {
            return [
                "status" => false,
                "message" => "You must be within 100 meters of the office to check out.",
                "distance" => round($distance, 2) . " meters"
            ];
        }

        // -----------------------------------------------------
        // 3️⃣ ATTENDANCE CHECK
        // -----------------------------------------------------
        $checkSql = "SELECT id, check_in 
                 FROM tbl_attendance 
                 WHERE user_id='$user_id' 
                 AND date = CURDATE() 
                 LIMIT 1";

        $checkRes = $this->conn->query($checkSql);

        if ($checkRes->num_rows == 0) {
            return ["status" => false, "message" => "You have not checked in today"];
        }

        $attendance = $checkRes->fetch_assoc();
        $attendanceId = $attendance["id"];
        $checkInTime = $attendance["check_in"];

        // Check if already checked out
        $alreadySql = "SELECT check_out 
                   FROM tbl_attendance 
                   WHERE id='$attendanceId' 
                   LIMIT 1";

        $already = $this->conn->query($alreadySql)->fetch_assoc();

        if (!empty($already["check_out"])) {
            return ["status" => false, "message" => "Already checked out today"];
        }

        // -----------------------------------------------------
        // 4️⃣ CALCULATE TOTAL MINUTES WORKED
        // -----------------------------------------------------
        $minutesSql = "SELECT TIMESTAMPDIFF(MINUTE, '$checkInTime', NOW()) AS total_minutes";
        $minRes = $this->conn->query($minutesSql);
        $totalMinutes = intval($minRes->fetch_assoc()["total_minutes"]);

        $workedHours = $totalMinutes / 60;
        $requiredHours = 8;
        $halfDayHours = $requiredHours / 2;

        // -----------------------------------------------------
        // 5️⃣ ATTENDANCE TYPE LOGIC
        // -----------------------------------------------------
        $currentTime = date("H:i:s");

        // Late cutoff = open time + grace
        $lateCutoff = date("H:i:s", strtotime("+$grace_minutes minutes", strtotime($company_open)));

        // Check if user was on time
        $isOnTime = strtotime($checkInTime) <= strtotime($lateCutoff);

        if (!$isOnTime) {

            // ❌ Late → HALF DAY (even if he works full hours)
            $finalStatus = "Half Day";

        } elseif (strtotime($currentTime) >= strtotime($company_close)) {

            // ✔ On-time + checkout after office close → PRESENT
            $finalStatus = "Present";

        } elseif ($workedHours >= $halfDayHours) {

            // ✔ Worked >= half → HALF DAY
            $finalStatus = "Half Day";

        } else {

            // ❌ Too early → Early Checkout
            $finalStatus = "Early Checkout";
        }

        // -----------------------------------------------------
        // 6️⃣ UPDATE RECORD
        // -----------------------------------------------------
        $updateSql = "
        UPDATE tbl_attendance 
        SET 
            check_out = NOW(),
            status = '$finalStatus',
            total_minutes = '$totalMinutes'
        WHERE id = '$attendanceId'
    ";

        if ($this->conn->query($updateSql)) {

            $_SESSION["user"]["checked_out"] = "Y";
            $_SESSION["user"]["total_working_minutes"] = $totalMinutes;

            return [
                "status" => true,
                "message" => "Check-out successful",
                "status_today" => $finalStatus,
                "worked_minutes" => $totalMinutes
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to check out",
            "error" => $this->conn->error
        ];
    }




    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        return [
            "status" => true,
            "message" => "Logout successful"
        ];
    }


    public function get_user_profile($email)
    {
        $email = $this->conn->real_escape_string($email);

        $sql = "SELECT id, name, email, mobile, role_id 
                FROM tbl_users 
                WHERE email = '$email' 
                LIMIT 1";

        $res = $this->conn->query($sql);

        if ($res->num_rows == 0) {
            return [
                "status" => false,
                "message" => "User not found"
            ];
        }

        $user = $res->fetch_assoc();

        return [
            "status" => true,
            "message" => "User profile fetched successfully",
            "data" => $user
        ];
    }

    public function update_profile($data)
    {
        $name = $this->conn->real_escape_string($data["full_name"]);
        $mobile = $this->conn->real_escape_string($data["mobile"]);
        $email = $this->conn->real_escape_string($data["email"]);
        $password = $data["password"] ?? "";

        if ($password !== "") {
            $password = password_hash($password, PASSWORD_BCRYPT);
            $passwordQuery = ", password='$password'";
        } else {
            $passwordQuery = "";
        }

        $sql = "UPDATE tbl_users SET 
            name='$name',
            mobile='$mobile'
            $passwordQuery
            WHERE email='$email'";

        if ($this->conn->query($sql)) {
            return ["status" => true];
        }

        return ["status" => false, "message" => $this->conn->error];
    }


    public function get_company_profile()
    {
        $sql = "SELECT * FROM tbl_company_information WHERE id=1 LIMIT 1";
        $res = $this->conn->query($sql);

        if ($res && $res->num_rows > 0) {
            return ["status" => true, "data" => $res->fetch_assoc()];
        }

        return ["status" => false, "message" => "Company info not found"];
    }



}
