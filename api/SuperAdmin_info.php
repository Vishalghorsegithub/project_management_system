<?php
require_once "../db.php";

class SuperAdmin_info
{

    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create_user()
    {
        session_start();

        // Inputs
        $name = $this->conn->real_escape_string($_POST["name"] ?? "");
        $email = $this->conn->real_escape_string($_POST["email"] ?? "");
        $mobile = $this->conn->real_escape_string($_POST["mobile"] ?? "");
        $password = $_POST["password"] ?? "";
        $role_id = 2;   // Default Employee

        // ------------------------------------
        // VALIDATION
        // ------------------------------------
        if (empty($name) || empty($email) || empty($password)) {
            return [
                "status" => false,
                "message" => "Name, Email & Password are required"
            ];
        }

        // ------------------------------------
        // CHECK DUPLICATE USER
        // ------------------------------------
        $check = $this->conn->query("
            SELECT id FROM tbl_users 
            WHERE email = '$email' OR name = '$name'
        ");

        if ($check->num_rows > 0) {
            return [
                "status" => false,
                "message" => "User already exists with same Email or Name"
            ];
        }

        // ------------------------------------
        // HANDLE PHOTO UPLOAD
        // ------------------------------------
        $photoName = "";
        if (!empty($_FILES["profile_image"]["name"])) {

            $file = $_FILES["profile_image"];
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

            $folder = "../uploads/users/";

            // If folder doesn't exist create it
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $photoName = "user_" . time() . "." . $ext;
            $uploadPath = $folder . $photoName;

            move_uploaded_file($file["tmp_name"], $uploadPath);
        }

        // ------------------------------------
        // HASH PASSWORD
        // ------------------------------------
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // ------------------------------------
        // INSERT USER
        // ------------------------------------
        $sql = "INSERT INTO tbl_users 
                (role_id, name, email, password, photo, mobile) 
                VALUES 
                ('$role_id', '$name', '$email', '$hashedPassword', '$photoName', '$mobile')";

        if ($this->conn->query($sql)) {
            return [
                "status" => true,
                "message" => "User created successfully"
            ];
        } else {
            return [
                "status" => false,
                "message" => "Database error",
                "error" => $this->conn->error
            ];
        }
    }






    // public function fetch_all_users_display()
    // {
    //     // SQL Query
    //     $sql = "
    //     WITH RankedProjects AS (
    //         SELECT 
    //             pt.user_id,
    //             p.project_name,
    //             SUM(pt.duration) AS total_duration,
    //             MAX(pt.created_date) AS created_date,
    //             ROW_NUMBER() OVER (
    //                 PARTITION BY pt.user_id 
    //                 ORDER BY MAX(pt.created_date) DESC
    //             ) AS rn
    //         FROM tbl_projects_tracking pt
    //         JOIN tbl_projects p ON pt.project_id = p.id
    //         GROUP BY pt.user_id, pt.project_id
    //     )
    //     SELECT 
    //         u.id,
    //         u.name,
    //         u.email,
    //         u.mobile,
    //         GROUP_CONCAT(rp.project_name ORDER BY rp.created_date DESC SEPARATOR ', ') AS project_names,
    //         GROUP_CONCAT(rp.total_duration ORDER BY rp.created_date DESC SEPARATOR ', ') AS project_durations
    //     FROM tbl_users u
    //     LEFT JOIN RankedProjects rp 
    //         ON u.id = rp.user_id AND rp.rn <= 4
    //     WHERE u.role_id = 2
    //     GROUP BY u.id;
    // ";

    //     // Run Query
    //     $result = $this->conn->query($sql);

    //     if (!$result) {
    //         return [
    //             "status" => false,
    //             "message" => "Database Error",
    //             "error" => $this->conn->error
    //         ];
    //     }

    //     // Fetch all users
    //     $users = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $users[] = [
    //             "id" => $row["id"],
    //             "name" => $row["name"],
    //             "email" => $row["email"],
    //             "mobile" => $row["mobile"],
    //             "project_names" => $row["project_names"] ?: "",
    //             "project_durations" => $row["project_durations"] ?: ""
    //         ];
    //     }

    //     return [
    //         "status" => true,
    //         "data" => $users
    //     ];
    // }




    //   analitics Display 



    public function fetch_all_users_display()
    {
        // SQL Query to get Today's Data
        $sql = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.mobile,
        
        -- UPDATED: Fetch Status as String. 
        -- If NULL (no record today), return 'Absent'.
        COALESCE(MAX(a.status), '-') as attendance_status,
        
        -- Concatenate Project Names (comma separated)
        GROUP_CONCAT(today_projects.project_name ORDER BY today_projects.total_duration DESC SEPARATOR ', ') as project_names,
        
        -- Concatenate Durations (comma separated)
        GROUP_CONCAT(today_projects.total_duration ORDER BY today_projects.total_duration DESC SEPARATOR ', ') as project_durations
        
    FROM tbl_users u
    
    -- 1. JOIN ATTENDANCE (Today Only)
    LEFT JOIN tbl_attendance a 
        ON u.id = a.user_id AND DATE(a.created_date) = CURDATE()
        
    -- 2. JOIN PROJECTS TRACKING (Today Only, Grouped by Project to sum duration)
    LEFT JOIN (
        SELECT 
            pt.user_id,
            p.project_name,
            SUM(pt.duration) as total_duration
        FROM tbl_projects_tracking pt
        JOIN tbl_projects p ON pt.project_id = p.id
        WHERE DATE(pt.created_date) = CURDATE()
        GROUP BY pt.user_id, pt.project_id
    ) today_projects ON u.id = today_projects.user_id
    
    WHERE u.role_id = 2
    GROUP BY u.id
    ORDER BY u.id DESC;
    ";

        // Run Query
        $result = $this->conn->query($sql);

        if (!$result) {
            return [
                "status" => false,
                "message" => "Database Error",
                "error" => $this->conn->error
            ];
        }

        // Fetch all users
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                "id" => $row["id"],
                "name" => $row["name"],
                "email" => $row["email"],
                "mobile" => $row["mobile"],

                // UPDATED: Return the raw string (e.g., "Present", "Half Day", "Absent")
                // Removed (int) casting
                "attendance_status" => ucfirst($row["attendance_status"]),

                "project_names" => $row["project_names"] ?: "",
                "project_durations" => $row["project_durations"] ?: ""
            ];
        }

        return [
            "status" => true,
            "data" => $users
        ];
    }


    public function fetch_all_users()
    {

        $sql = "
        WITH LatestProjects AS (
            SELECT 
                pt.user_id,
                p.project_name,
                SUM(pt.duration) AS total_duration,
                MAX(pt.created_date) AS last_date,
                ROW_NUMBER() OVER (PARTITION BY pt.user_id ORDER BY MAX(pt.created_date) DESC) AS rn
            FROM tbl_projects_tracking pt
            JOIN tbl_projects p ON pt.project_id = p.id
            GROUP BY pt.user_id, pt.project_id
        )
        SELECT
            u.id,
            u.name,
            u.email,
            u.mobile,
            GROUP_CONCAT(lp.project_name ORDER BY lp.last_date DESC SEPARATOR ', ') AS project_names,
            GROUP_CONCAT(lp.total_duration ORDER BY lp.last_date DESC SEPARATOR ', ') AS project_durations
        FROM tbl_users u
        LEFT JOIN LatestProjects lp ON u.id = lp.user_id AND lp.rn <= 4
        WHERE u.role_id = 2
        GROUP BY u.id
        ORDER BY u.id DESC;
        ";

        $res = $this->conn->query($sql);

        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }

        return [
            "status" => true,
            "data" => $rows
        ];
    }


    // ======================================================
    // ✅ 2. FETCH USER GRAPH DATA
    // ======================================================

    public function fetch_user_graph($userId)
    {

        $userId = intval($userId);

        $sql = "
            SELECT 
                p.project_name,
                SUM(pt.duration) AS total_duration
            FROM tbl_projects_tracking pt
            JOIN tbl_projects p ON pt.project_id = p.id
            WHERE pt.user_id = $userId
            GROUP BY pt.project_id
            ORDER BY total_duration DESC
        ";

        $res = $this->conn->query($sql);

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = [
                "project" => $row["project_name"],
                "duration" => intval($row["total_duration"])
            ];
        }

        return [
            "status" => true,
            "data" => $data
        ];
    }



    // ======================================================
    // ✅ 3. FETCH USER THIS MONTH DATA
    // ======================================================

    public function fetch_user_month($userId)
    {

        $userId = intval($userId);

        $first = date("Y-m-01 00:00:00");
        $last = date("Y-m-t 23:59:59");

        $sql = "
            SELECT 
                p.project_name,
                SUM(pt.duration) AS total_duration
            FROM tbl_projects_tracking pt
            JOIN tbl_projects p ON pt.project_id = p.id
            WHERE pt.user_id = $userId
            AND pt.created_date BETWEEN '$first' AND '$last'
            GROUP BY pt.project_id
            ORDER BY total_duration DESC
        ";

        $res = $this->conn->query($sql);

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = [
                "project" => $row["project_name"],
                "duration" => intval($row["total_duration"])
            ];
        }

        return [
            "status" => true,
            "data" => $data
        ];
    }



    // ======================================================
    // ✅ 4. FETCH USER ALL ACTIVITY
    // ======================================================

    public function fetch_user_activity($userId, $fromDate = "", $toDate = "", $page = 1, $limit = 10)
    {
        $userId = intval($userId);
        $offset = ($page - 1) * $limit;

        // Date filter
        $dateWhere = "";
        if (!empty($fromDate) && !empty($toDate)) {
            $dateWhere = " AND pt.start_time BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59' ";
        }

        // COUNT total rows
        $countSql = "
        SELECT COUNT(*) AS total
        FROM tbl_projects_tracking pt
        WHERE pt.user_id = $userId
        $dateWhere
    ";
        $countRes = $this->conn->query($countSql);
        $totalRows = $countRes->fetch_assoc()['total'];

        // Main data query
        $sql = "
        SELECT 
            pt.id,
            p.project_name,
            pt.desc,
            pt.duration,
            pt.start_time,
            pt.end_time
        FROM tbl_projects_tracking pt
        LEFT JOIN tbl_projects p ON pt.project_id = p.id
        WHERE pt.user_id = $userId
        $dateWhere
        ORDER BY pt.start_time DESC
        LIMIT $limit OFFSET $offset
    ";

        $res = $this->conn->query($sql);

        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = [
                "id" => $row["id"],
                "project" => $row["project_name"],
                "desc" => $row["desc"],
                "duration" => intval($row["duration"]),
                "start" => $row["start_time"],
                "end" => $row["end_time"]
            ];
        }

        return [
            "status" => true,
            "data" => $list,
            "total" => intval($totalRows),
            "limit" => intval($limit),
            "page" => intval($page),
            "pages" => ceil($totalRows / $limit)
        ];
    }


    public function fetch_user_attendance($userId, $month = "")
    {
        $userId = intval($userId);

        // Default range: current month
        if (empty($month)) {
            $fromDate = date("Y-m-01");
            $toDate = date("Y-m-t");  // last day of current month
        } else {

            // Convert "2025-10" to 1st day of that month
            $firstDay = $month . "-01";

            $fromDate = date("Y-m-01", strtotime($firstDay));
            $toDate = date("Y-m-t", strtotime($firstDay));  // last day of selected month
        }

        $sql = "
        SELECT 
            date,
            status,
            check_in,
            check_out,
            TIMESTAMPDIFF(MINUTE, check_in, check_out) AS duration
        FROM tbl_attendance
        WHERE user_id = $userId
        AND date BETWEEN '$fromDate' AND '$toDate'
        ORDER BY date DESC
    ";

        $res = $this->conn->query($sql);

        if (!$res) {
            return [
                "status" => false,
                "message" => "SQL Error",
                "error" => $this->conn->error,
                "sql" => $sql
            ];
        }

        $data = [];

        while ($row = $res->fetch_assoc()) {
            $minutes = intval($row["duration"]);
            $row["duration"] = floor($minutes / 60) . "h " . ($minutes % 60) . "m";

            $data[] = $row;
        }

        return [
            "status" => true,
            "data" => $data
        ];
    }



    public function update_company_profile($data)
    {
        $company = $this->conn->real_escape_string($data["company_name"]);
        $lat = $this->conn->real_escape_string($data["latitude"]);
        $lng = $this->conn->real_escape_string($data["longitude"]);
        $open = $this->conn->real_escape_string($data["open_time"]);
        $close = $this->conn->real_escape_string($data["close_time"]);

        // SQL - use backticks for column names
        $sql = "UPDATE tbl_company_information SET 
                `name`='$company',
                `lat`='$lat',
                `long`='$lng',
                `open_time`='$open',
                `close_time`='$close'
            WHERE id=1";

        if ($this->conn->query($sql)) {
            return ["status" => true];
        }

        return ["status" => false, "message" => $this->conn->error];
    }




}
