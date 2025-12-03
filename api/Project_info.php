<?php
require_once "../db.php";

class project_info
{

    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create_project($project_name)
    {

        $project_name = $this->conn->real_escape_string($project_name);

        // Get user ID from session
        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "User not logged in (Session missing)"
            ];
        }

        // ---------------------------
        // ✔ Check duplicate project name
        // ---------------------------
        $check = $this->conn->query("SELECT id FROM tbl_projects WHERE project_name='$project_name' LIMIT 1");

        if ($check->num_rows > 0) {
            return [
                "status" => false,
                "message" => "Project name already exists!"
            ];
        }

        // ---------------------------
        // ✔ Color pattern auto-rotation
        // ---------------------------
        $colors = ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

        // Count rows to determine color index
        $countQuery = $this->conn->query("SELECT COUNT(*) AS total FROM tbl_projects");
        $row = $countQuery->fetch_assoc();
        $rowCount = (int) $row['total'];

        $colorIndex = $rowCount % count($colors);
        $selectedColor = $colors[$colorIndex];

        // ---------------------------
        // ✔ Insert new project
        // ---------------------------
        $sql = "INSERT INTO tbl_projects (project_name, color, created_by)
            VALUES ('$project_name', '$selectedColor', '$user_id')";





        if ($this->conn->query($sql)) {
            return [
                "status" => true,
                "message" => "Project created successfully",
                "color" => $selectedColor
            ];
        } else {
            return [
                "status" => false,
                "message" => "Insert failed",
                "error" => $this->conn->error
            ];
        }
    }


    public function fetch_projects_name()
    {
        // 1. Added 'color' to the select so frontend can use it
        $query = $this->conn->query("SELECT `id`, `project_name`, `color` FROM `tbl_projects`");

        if (!$query) {
            return [
                "status" => false,
                "message" => "Database error",
                "error" => $this->conn->error
            ];
        }

        $projects = [];

        // 2. CHANGED: fetch_assoc() returns ["id" => "1", "project_name" => "web"]
        // instead of [0 => "1", 1 => "web"]
        while ($row = $query->fetch_assoc()) {
            $projects[] = $row;
        }

        return [
            "status" => true,
            "message" => "Projects fetched successfully",
            "data" => $projects
        ];
    }


    public function save_project_log($data)
    {

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized. Please log in."
            ];
        }

        // Sanitize input
        $project_id = $this->conn->real_escape_string($data["project_id"]);
        $start_time = $this->conn->real_escape_string($data["start_time"]);
        $end_time = $this->conn->real_escape_string($data["end_time"]);
        $duration = $this->conn->real_escape_string($data["duration"]);
        $description = $this->conn->real_escape_string($data["description"]);

        // INSERT INTO tbl_projects_tracking
        $sql = "INSERT INTO tbl_projects_tracking 
            (project_id, user_id, start_time, end_time, duration, `desc`)
            VALUES 
            ('$project_id', '$user_id', '$start_time', '$end_time', '$duration', '$description')";

        if ($this->conn->query($sql)) {
            return [
                "status" => true,
                "message" => "Project log saved successfully."
            ];
        } else {
            return [
                "status" => false,
                "message" => "Database error",
                "error" => $this->conn->error
            ];
        }
    }


    public function fetch_recent_logs()
    {
        session_start();

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        $sql = "SELECT  
                u.name,
                p.project_name,
                t.`desc`,
                t.`duration`,
                t.`start_time`,
                t.`end_time`,
                p.`color`
            FROM tbl_projects_tracking AS t
            LEFT JOIN tbl_projects AS p ON t.project_id = p.id
            LEFT JOIN tbl_users AS u ON t.user_id = u.id
            WHERE t.user_id = '$user_id'
            ORDER BY t.`created_date` DESC
            LIMIT 10";

        $result = $this->conn->query($sql);

        $logs = [];

        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                "project" => $row["project_name"],
                "desc" => $row["desc"],
                "duration" => $row["duration"],
                "start" => $row["start_time"],
                "end" => $row["end_time"],
                "user" => $row["name"]
            ];
        }

        return [
            "status" => true,
            "data" => $logs
        ];
    }



    public function fetch_this_month_logs($startTime, $endTime)
    {
        session_start();

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        // Build WHERE conditions
        $where = "t.user_id = '$user_id'";

        // If BOTH empty → use current month
        if (empty($startTime) && empty($endTime)) {

            $firstDay = date('Y-m-01 00:00:00');
            $lastDay = date('Y-m-t 23:59:59');

            $where .= " AND t.created_date BETWEEN '$firstDay' AND '$lastDay' ";
        } else {

            // Sanitize inputs
            if (!empty($startTime)) {
                $start = $this->conn->real_escape_string($startTime);
                $where .= " AND t.created_date >= '$start 00:00:00' ";
            }

            if (!empty($endTime)) {
                $end = $this->conn->real_escape_string($endTime);
                $where .= " AND t.created_date <= '$end 23:59:59' ";
            }
        }

        // SQL Query
        $sql = "SELECT  
                p.project_name,
                SUM(t.duration) AS total_duration,
                p.color
            FROM tbl_projects_tracking AS t
            LEFT JOIN tbl_projects AS p ON t.project_id = p.id
            WHERE $where
            GROUP BY t.project_id
            ORDER BY total_duration DESC";

        $result = $this->conn->query($sql);

        $logs = [];

        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                "project" => $row["project_name"],
                "duration" => intval($row["total_duration"]), // MINUTES
                "color" => $row["color"]
            ];
        }

        return [
            "status" => true,
            "data" => $logs
        ];
    }


    public function fetch_all_activity_logs($startTime, $endTime, $limit, $offset)
    {
        session_start();
        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        // Base WHERE condition
        $where = " t.user_id = '$user_id' ";

        // Filtering by created_date (not start_time/end_time)
        if (!empty($startTime)) {
            $startTime = $this->conn->real_escape_string($startTime);
            $where .= " AND t.created_date >= '{$startTime} 00:00:00' ";
        }

        if (!empty($endTime)) {
            $endTime = $this->conn->real_escape_string($endTime);
            $where .= " AND t.created_date <= '{$endTime} 23:59:59' ";
        }

        // If BOTH empty → show all data (no date filter)
        // (nothing to change because no extra conditions added)

        // -------------------------------
        // 1. GET TOTAL COUNT
        // -------------------------------
        $countSql = "SELECT COUNT(*) AS total 
                 FROM tbl_projects_tracking AS t
                 WHERE $where";

        $countResult = $this->conn->query($countSql);
        $totalRows = $countResult->fetch_assoc()['total'];

        // Safe values
        $limit = intval($limit);
        $offset = intval($offset);

        // -------------------------------
        // 2. GET PAGINATED DATA
        // -------------------------------
        $sql = "SELECT  
                t.id,
                u.name,
                p.project_name,
                p.color,
                t.`desc`,
                t.`duration`,
                t.`start_time`,
                t.`end_time`,
                t.created_date
            FROM tbl_projects_tracking AS t
            LEFT JOIN tbl_projects AS p ON t.project_id = p.id
            LEFT JOIN tbl_users AS u ON t.user_id = u.id
            WHERE $where
            ORDER BY t.created_date DESC
            LIMIT $limit OFFSET $offset";

        $result = $this->conn->query($sql);

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                "id" => $row["id"],
                "project" => $row["project_name"],
                "color" => $row["color"],
                "desc" => $row["desc"],
                "duration" => $row["duration"],
                "start" => $row["start_time"],
                "end" => $row["end_time"],
                "created" => $row["created_date"],
                "user" => $row["name"]
            ];
        }

        return [
            "status" => true,
            "data" => $logs,
            "total" => intval($totalRows),
            "limit" => $limit,
            "offset" => $offset,
            "pages" => ceil($totalRows / $limit)
        ];
    }



    public function fetch_graph_data($startTime, $endTime)
    {
        session_start();
        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        // Base WHERE condition
        $where = "t.user_id = '$user_id'";

        // If no dates → use CURRENT DAY
        if (empty($startTime) && empty($endTime)) {

            $todayStart = date("Y-m-d 00:00:00");
            $todayEnd = date("Y-m-d 23:59:59");

            $where .= " AND t.created_date BETWEEN '$todayStart' AND '$todayEnd' ";
        } else {

            // If startTime provided
            if (!empty($startTime)) {
                $startTime = $this->conn->real_escape_string($startTime);
                $startTime .= " 00:00:00";
                $where .= " AND t.created_date >= '$startTime' ";
            }

            // If endTime provided
            if (!empty($endTime)) {
                $endTime = $this->conn->real_escape_string($endTime);
                $endTime .= " 23:59:59";
                $where .= " AND t.created_date <= '$endTime' ";
            }
        }

        // Final SQL
        $sql = "SELECT 
                p.project_name,
                p.color,
                SUM(t.duration) AS total_duration
            FROM tbl_projects_tracking AS t
            LEFT JOIN tbl_projects AS p ON t.project_id = p.id
            WHERE $where
            GROUP BY t.project_id
            ORDER BY total_duration DESC";

        $res = $this->conn->query($sql);
        print_r($sql);
        $data = [];

        while ($row = $res->fetch_assoc()) {
            $data[] = [
                "project" => $row["project_name"],
                "color" => $row["color"],
                "duration" => intval($row["total_duration"])
            ];
        }

        return [
            "status" => true,
            "data" => $data
        ];
    }


    public function fetch_attendance($month_Year = '')
    {
        session_start();

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        // If month not passed → use current month
        if (empty($month_Year)) {
            $month_Year = date('Y-m');
        } else {
            $month_Year = date('Y-m', strtotime($month_Year));
        }

        // Prepare WHERE
        $where = "user_id = '$user_id' AND DATE_FORMAT(date, '%Y-%m') = '$month_Year'";

        // SQL Query
        $sql = "SELECT 
                id,
                date,
                check_in,
                check_out,
                SUM(total_minutes) AS working_minutes
            FROM tbl_attendance
            WHERE $where
            GROUP BY date
            ORDER BY date ASC";

        $res = $this->conn->query($sql);

        $data = [];
        $totalMinutes = 0;  // 🔥 Monthly total minutes

        while ($row = $res->fetch_assoc()) {

            $minutes = intval($row["working_minutes"]);
            $totalMinutes += $minutes; // 🔥 Add to monthly total

            // Format daily duration
            $h = floor($minutes / 60);
            $m = $minutes % 60;

            $row["working_formatted"] = "{$h}h {$m}m";

            $data[] = $row;
        }

        // -------------------------------
        // 🔥 Format monthly total minutes
        // -------------------------------
        $days = floor($totalMinutes / (8 * 60));  // 480 mins = 1 day
        $remainingAfterDays = $totalMinutes % (8 * 60);

        $hours = floor($remainingAfterDays / 60);
        $minutesLeft = $remainingAfterDays % 60;

        $totalFormatted = "{$days}d {$hours}h {$minutesLeft}m";

        return [
            "status" => true,
            "month" => $month_Year,
            "total_minutes" => $totalMinutes,
            "total_days" => $totalFormatted,  // 🔥 FULL MONTH FORMAT
            "data" => $data
        ];
    }



    public function delete_time_entry($entry_id)
    {
        session_start();

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        // Sanitize entry_id
        $entry_id = $this->conn->real_escape_string($entry_id);

        // Verify ownership
        $checkSql = "SELECT id FROM tbl_projects_tracking 
             WHERE id = '$entry_id' 
             AND user_id = '$user_id' 
             AND DATE(created_date) = CURDATE()
             LIMIT 1";

        $checkRes = $this->conn->query($checkSql);

        if ($checkRes->num_rows == 0) {
            return [
                "status" => false,
                "message" => "Entry not found or access denied"
            ];
        }

        // Proceed to delete
        $deleteSql = "DELETE FROM tbl_projects_tracking WHERE id = '$entry_id'";

        if ($this->conn->query($deleteSql)) {
            return [
                "status" => true,
                "message" => "Time entry deleted successfully"
            ];
        } else {
            return [
                "status" => false,
                "message" => "Database error",
                "error" => $this->conn->error
            ];
        }
    }



    public function fetch_projects_spend_time($project_id)
    {
        session_start();

        $user_id = $_SESSION["user"]["user_id"] ?? 0;

        if ($user_id == 0) {
            return [
                "status" => false,
                "message" => "Unauthorized"
            ];
        }

        if (empty($project_id)) {
            return [
                "status" => false,
                "message" => "Project not selected"
            ];
        }

        // ---- 1️⃣ GET TOTAL SPEND TIME ----
        $sql = "
        SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS total_minutes
        FROM tbl_projects_tracking
        WHERE user_id = '$user_id'
        AND project_id = '$project_id'
    ";

        $res = $this->conn->query($sql);
        $row = $res->fetch_assoc();

        $totalMinutes = intval($row["total_minutes"] ?? 0);

        // Format time
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        $days = floor($hours / 8);
        $remainingHours = $hours % 8;

        if ($days > 0) {
            $formatted = "{$days} day" . ($days > 1 ? "s" : "");
            if ($remainingHours > 0)
                $formatted .= " {$remainingHours}h";
            if ($minutes > 0)
                $formatted .= " {$minutes}m";
        } else {
            $formatted = "{$hours}h {$minutes}m";
        }

        // ---- 2️⃣ GET RESTRICT TIME (LAST END TIME OF TODAY) ----
        $sql1 = "
        SELECT 
    CASE
        -- 1️⃣ Not checked in today → block
        WHEN NOT EXISTS (
            SELECT 1 
            FROM tbl_attendance 
            WHERE user_id = '$user_id' 
            AND date = CURDATE()
        )
        THEN 'NOT_CHECKED_IN'

        -- 2️⃣ Checked in today and NO project logged → return check-in time
        WHEN NOT EXISTS (
            SELECT 1
            FROM tbl_projects_tracking
            WHERE user_id = '$user_id'
            AND DATE(created_date) = CURDATE()
        )
        THEN (
            SELECT check_in 
            FROM tbl_attendance
            WHERE user_id = '$user_id'
            AND date = CURDATE()
            LIMIT 1
        )

        -- 3️⃣ Tracking exists → return last end time
        ELSE (
            SELECT MAX(end_time)
            FROM tbl_projects_tracking
            WHERE user_id = '$user_id'
            AND DATE(created_date) = CURDATE()
        )
    END AS restrict_time;

    ";

        $res1 = $this->conn->query($sql1);
        $row1 = $res1->fetch_assoc();

        // Keep datetime as string
        $restrict_time = $row1["restrict_time"] ?? "";

        // ---- 3️⃣ RETURN RESPONSE ----
        return [
            "status" => true,
            "project_id" => $project_id,
            "duration" => $formatted,
            "restrict_time" => $restrict_time
        ];
    }





}
