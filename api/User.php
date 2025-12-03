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


        $session_lifetime = 6 * 60 * 60; // 6 hours in seconds

        ini_set('session.gc_maxlifetime', $session_lifetime);
        ini_set('session.cookie_lifetime', $session_lifetime);

        session_set_cookie_params($session_lifetime);


        $_SESSION["created_at"] = time();
        $_SESSION["session_lifetime"] = $session_lifetime;


        $_SESSION["user"] = [
            "user_id" => $user['user_id'],
            "name" => $user['user_name'],
            "email" => $user['email'],
            "mobile" => $user['mobile'],
            "role" => $user['role'],
            "day_minutes" => $company_data['total_minutes'], // total shift minutes
            "checked_in" => !empty($user["check_in"]) ? "Y" : "N",
            "checked_out" => !empty($user["check_out"]) ? "Y" : "N",
            "checked_in_time" => !empty($user["check_in"]) ? $user["check_in"] : "",
            "checked_out_time" => !empty($user["check_out"]) ? $user["check_out"] : ""

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


    public function check_in()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return ["status" => false, "message" => "Unauthorized User"];
        }

        // Prevent double check-in
        $checkSql = "SELECT id FROM tbl_attendance 
                 WHERE user_id = '$user_id' AND date = CURDATE() LIMIT 1";

        $checkRes = $this->conn->query($checkSql);

        if ($checkRes->num_rows > 0) {
            return ["status" => false, "message" => "Already checked in today"];
        }

        // Insert attendance
        $sql = "INSERT INTO tbl_attendance (user_id, date, check_in, status)
            VALUES ('$user_id', CURDATE(), NOW(), '')";

        if ($this->conn->query($sql)) {

            $lastId = $this->conn->insert_id;

            $getSql = "SELECT check_in FROM tbl_attendance WHERE id='$lastId' LIMIT 1";
            $row = $this->conn->query($getSql)->fetch_assoc();

            $check_in_time = $row["check_in"];

            $_SESSION["user"]["checked_in"] = "Y";
            $_SESSION["user"]["checked_in_time"] = $check_in_time;
            $_SESSION["user"]["checked_out"] = "N";  // reset

            return [
                "status" => true,
                "message" => "Check-in successful",
                "check_in_time" => $check_in_time
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to check in",
            "error" => $this->conn->error
        ];
    }

    public function check_out()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return ["status" => false, "message" => "Unauthorized User"];
        }

        // Fetch today's attendance
        $checkSql = "SELECT id, check_in FROM tbl_attendance 
                 WHERE user_id='$user_id' AND date = CURDATE() LIMIT 1";

        $checkRes = $this->conn->query($checkSql);

        if ($checkRes->num_rows == 0) {
            return ["status" => false, "message" => "You have not checked in today"];
        }

        $attendance = $checkRes->fetch_assoc();
        $attendanceId = $attendance["id"];
        $checkInTime = $attendance["check_in"];

        // Prevent double check-out
        $alreadySql = "SELECT check_out FROM tbl_attendance WHERE id='$attendanceId' LIMIT 1";
        $already = $this->conn->query($alreadySql)->fetch_assoc();

        if (!empty($already["check_out"])) {
            return ["status" => false, "message" => "Already checked out today"];
        }

        // Calculate working minutes
        $minutesSql = "SELECT TIMESTAMPDIFF(MINUTE, '$checkInTime', NOW()) AS total_minutes";
        $row = $this->conn->query($minutesSql)->fetch_assoc();

        $totalMinutes = intval($row["total_minutes"]);
        $workedHours = $totalMinutes / 60;

        // Fixed values
        $requiredHours = 8;
        $halfDayHours = $requiredHours / 2;

        // Determine status
        if ($workedHours >= $requiredHours) {
            $finalStatus = "Present";
        } elseif ($workedHours >= $halfDayHours) {
            $finalStatus = "Half Day";
        } else {
            $finalStatus = "Early Departure";
        }

        // Update record
        $updateSql = "
        UPDATE tbl_attendance 
        SET 
            check_out = NOW(),
            status = '$finalStatus',
            total_minutes = '$totalMinutes'
        WHERE id = '$attendanceId'
    ";

        if ($this->conn->query($updateSql)) {

            // Fetch updated checkout time
            $checkOutSql = "SELECT check_out FROM tbl_attendance WHERE id='$attendanceId' LIMIT 1";
            $row = $this->conn->query($checkOutSql)->fetch_assoc();

            $checkOutTime = $row["check_out"];

            // Update session
            $_SESSION["user"]["checked_out"] = "Y";
            $_SESSION["user"]["checked_out_time"] = $checkOutTime;
            $_SESSION["user"]["total_working_minutes"] = $totalMinutes;

            return [
                "status" => true,
                "message" => "Check-out successful",
                "status_today" => $finalStatus,
                "worked_minutes" => $totalMinutes,
                "check_out_time" => $checkOutTime
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
