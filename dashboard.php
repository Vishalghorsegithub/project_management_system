<?php
session_start();

// If user not logged in → redirect to login page
if (!isset($_SESSION['user']) || empty($_SESSION['user']['user_id'])) {
    header("Location: login.php");
    exit;
}


$checkInTime = $_SESSION["user"]["checked_in_time"] ?? null;
$checkOut = $_SESSION["user"]["checked_out"] ?? "";
$checkOutTime = $_SESSION["user"]["checked_out_time"] ?? "";



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// no user = not logged in
if (empty($_SESSION["user"])) {
    // redirect to login or return unauthorized
    header("Location: login.php");
    exit;
}

// check custom timeout
$createdAt = $_SESSION["created_at"] ?? 0;
$lifetime = $_SESSION["session_lifetime"] ?? (6 * 60 * 60);

if (time() - $createdAt > $lifetime) {
    // session expired: clear and destroy
    session_unset();
    session_destroy();
    // redirect to login
    header("Location: login.php");
    exit;
}



?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevTrack | Time Analytics</title>
    <!-- Bootstrap 5 CSS -->

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <!-- Google Fonts -->

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">


    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- <link rel="stylesheet" href="assets/bootstrap-icons.css"> -->
    <!-- Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <script src="assets/chart.js"></script>

    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #10b981;
            --bg-light: #f3f4f6;
            --card-bg: #ffffff;
            --text-dark: #1f2937;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* --- Header --- */
        .navbar {
            background-color: var(--card-bg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            position: relative;
        }

        .brand-logo {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.5rem;
            text-decoration: none;
        }

        /* --- Cards --- */
        .custom-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease;
            height: 100%;
            overflow: hidden;
        }

        /* Sticky Sidebar Logic */
        @media (min-width: 992px) {
            .sticky-sidebar {
                position: -webkit-sticky;
                position: sticky;
                top: 20px;
                z-index: 100;
                /* Height auto to ensure it doesn't stretch weirdly if content is small */
                height: auto;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
            }
        }

        /* Scrollable Areas for Tabs */
        .tab-scrollable-area {
            max-height: 500px;
            /* Adjust height as needed */
            overflow-y: auto;
            padding-right: 5px;
            /* Prevent scrollbar overlap */
        }

        /* Custom Scrollbar Styling */
        .tab-scrollable-area::-webkit-scrollbar,
        .sticky-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .tab-scrollable-area::-webkit-scrollbar-track,
        .sticky-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .tab-scrollable-area::-webkit-scrollbar-thumb,
        .sticky-sidebar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .tab-scrollable-area::-webkit-scrollbar-thumb:hover,
        .sticky-sidebar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-primary-custom {
            background-color: var(--primary);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
        }

        .btn-primary-custom:hover {
            background-color: #4338ca;
        }

        /* --- Modern Segmented Tabs (Left Column) --- */
        .segmented-control {
            background-color: #f3f4f6;
            padding: 4px;
            border-radius: 50px;
            display: flex;
            position: relative;
        }

        .segmented-control .nav-link {
            border-radius: 50px;
            color: #6b7280;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 8px 16px;
            border: none;
            transition: all 0.2s ease;
        }

        .segmented-control .nav-link.active {
            background-color: white;
            color: var(--primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }

        /* --- Right Column Tabs (Standard) --- */
        .nav-pills .nav-link {
            color: #6b7280;
            font-weight: 500;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.9rem;
            margin-right: 5px;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
        }

        .tab-content {
            padding-top: 20px;
            min-height: 300px;
        }

        /* --- Table --- */
        .table-custom th {
            background-color: #f9fafb;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            padding: 12px 16px;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 14px 16px;
            font-size: 0.9rem;
        }

        /* --- Stats Bars --- */
        .project-stat-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-label {
            width: 140px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .progress-container {
            flex-grow: 1;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            margin: 0 15px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .stat-value {
            width: 80px;
            text-align: right;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary);
        }

        /* --- Recent List --- */
        .recent-item {
            border-left: 4px solid transparent;
            transition: background-color 0.2s;
        }

        .recent-item:hover {
            background-color: #f9fafb;
        }

        .profile-icon-container {
            width: 40px;
            height: 40px;
            background-color: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .profile-icon-container:hover {
            background-color: #e5e7eb;
        }


        /*  progress Bar  */

        .project-stat-row {
            display: grid;
            grid-template-columns: 25% 50% 25%;
            align-items: center;
            width: 100%;
            gap: 15px;
        }


        .stat-label {
            width: 90%;
            font-weight: 500;
        }

        .progress-container {
            width: 90%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .stat-value {
            width: 40%;
            text-align: start;
            font-weight: 600;
        }


        .chart-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .project-chart-container {
            width: 100%;
            padding-right: 10px;
        }

        .chart-empty-msg {
            display: none;
            position: absolute;
            left: 0;
            right: 0;
            text-align: center;
            top: 40%;
            color: #6b7280;
        }


        #attendance-table thead tr,
        th {
            background-color: var(--primary) !important;
            color: #fffffe !important;
        }
    </style>
</head>

<body>


    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-4 border-bottom">
        <div class="container">
            <!-- Brand / Logo -->
            <a href="#" class="brand-logo">
                <i class="bi bi-code-square me-2 text-primary"></i>DevTrack
            </a>

            <!-- Right Side Items -->
            <div class="d-flex align-items-center gap-3 ms-auto">

                <!-- NEW: Check In / Check Out Buttons -->
                <div class="d-flex gap-2">
                    <?php
                    $checkedIn = $_SESSION["user"]["checked_in"] ?? "N";
                    $checkedOut = $_SESSION["user"]["checked_out"] ?? "N";
                    ?>

                    <!-- SHOW CHECK-IN -->
                    <?php if ($checkedIn === "N") { ?>
                        <button type="button" class="btn btn-success btn-sm btn-check-in px-3" onclick="checkIn()">
                            <i class="bi bi-clock-fill me-1"></i> Check In
                        </button>

                        <!-- SHOW CHECK-OUT -->
                    <?php } elseif ($checkedIn === "Y" && $checkedOut === "N") { ?>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-check-out px-3" onclick="checkOut()">
                            <i class="bi bi-box-arrow-right me-1"></i> Check Out
                        </button>

                        <!-- CHECKED-IN & CHECKED-OUT → Show Nothing -->
                    <?php } else { ?>
                        <!-- Optionally show a message -->
                        <!-- <span class="badge bg-secondary">minutes</span> -->
                    <?php } ?>

                </div>

                <!-- Divider (Optional for visual separation) -->
                <div class="vr d-none d-sm-block mx-1"></div>

                <!-- Total Analytics Display -->
                <div class="d-none d-sm-block text-end">
                    <!-- PHP Placeholder Logic for Preview -->
                    <span class="d-block small text-muted text-uppercase fw-bold"
                        style="font-size: 0.7rem; letter-spacing: 0.5px;">

                        <?php
                        if (isset($_SESSION['user']['name'])) {
                            $use_name = $_SESSION['user']['name'];
                        } else {
                            $use_name = "Not Logged In";
                        }
                        ?>
                        <?php echo $use_name; ?>
                        <br>

                        <span id="total_working_display"> </span>
                    </span>
                    <!-- <span class="d-block small text-success" style="font-size: 0.65rem;">Online</span> -->
                </div>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <div class="profile-icon-container" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill fs-5 text-secondary"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                        <li>
                            <!-- PHP Placeholder -->
                            <h6 class="dropdown-header">Signed in as <?php echo $use_name; ?></h6>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i>Export Data</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" onclick="log_out();" href="#">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row g-4">

            <!-- Left Column: Input Form & Project Creator -->
            <div class="col-lg-4">
                <!-- Added 'sticky-sidebar' class here -->
                <div class="custom-card p-4 sticky-sidebar">

                    <!-- Modern Segmented Tabs -->
                    <ul class="nav nav-pills nav-fill segmented-control mb-4" id="leftTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-log-time" data-bs-toggle="pill"
                                data-bs-target="#content-log-time" type="button" role="tab">
                                Log Time
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-new-project" data-bs-toggle="pill"
                                data-bs-target="#content-new-project" type="button" role="tab">
                                <i class="bi bi-plus-lg small me-1"></i>New Project
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-0" style="min-height: auto;">

                        <!-- 1. Log Time Form -->
                        <div class="tab-pane fade show active" id="content-log-time" role="tabpanel">
                            <form id="timeForm">
                                <!-- Project Dropdown -->
                                <div class="mb-3">
                                    <label class="form-label">Project Name</label>
                                    <select class="form-select" id="projectSelect" required>
                                        <option value="" disabled selected>Select a project...</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Project Duration</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="projectTime" readonly
                                            disabled="true">

                                    </div>
                                </div>

                                <!-- Start Time -->
                                <div class="mb-3">
                                    <label class="form-label">Start Time <span id="time_alert_error"
                                            class="text-danger fw-bold"></span>
                                    </label>
                                    <div class="input-group">
                                        <input type="datetime-local" class="form-control" id="startTime" required>
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="setNow('startTime')"><i class="bi bi-clock"></i></button>
                                    </div>
                                </div>

                                <!-- End Time -->
                                <div class="mb-3">
                                    <label class="form-label">End Time</label>
                                    <div class="input-group">
                                        <input type="datetime-local" class="form-control" id="endTime" required>
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="setNow('endTime')"><i class="bi bi-clock"></i></button>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="description" rows="2"
                                        placeholder="What did you work on?"></textarea>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" id="timeFormSubmitBtn"
                                        class="btn btn-primary btn-primary-custom">
                                        <i class="bi bi-plus-circle me-2"></i>Add Entry
                                    </button>

                                </div>
                            </form>
                        </div>

                        <!-- 2. Create Project Form -->
                        <div class="tab-pane fade" id="content-new-project" role="tabpanel">
                            <div class="text-center mb-4">
                                <div class="bg-light rounded-circle d-inline-flex p-3 mb-3 text-primary">
                                    <i class="bi bi-folder-plus fs-3"></i>
                                </div>
                                <h6 class="fw-bold">Create New Project</h6>
                                <p class="small text-muted">Add a new project to your list to start tracking time
                                    against it.</p>
                            </div>

                            <form id="createProjectForm">
                                <div class="mb-4">
                                    <label class="form-label">Project Title</label>
                                    <input type="text" class="form-control" id="newProjectName"
                                        placeholder="e.g., Mobile App Design" required>
                                </div>

                                <button type="submit" id="projectSubmitBtn" class="btn btn-success btn-primary-custom"
                                    style="background-color: var(--secondary);">
                                    <i class="bi bi-check-lg me-2"></i>Create Project
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Filter & Tabs -->
            <div class="col-lg-8">
                <div class="custom-card p-4">

                    <!-- Date Filter Section -->
                    <div class="row g-2 mb-4 align-items-end bg-light p-3 rounded">
                        <div class="col-md-4 d-flex align-items-center">
                            <label class="small text-muted fw-bold me-2 mb-0">From </label>
                            <input type="date" class="form-control form-control-sm" id="filterStart">
                        </div>

                        <div class="col-md-4 d-flex align-items-center">
                            <label class="small text-muted fw-bold me-2 mb-0">To </label>
                            <input type="date" class="form-control form-control-sm" id="filterEnd">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-sm btn btn-primary btn-primary-custom w-100 p-2"
                                onclick="updateUI()">
                                <i class="bi bi-funnel me-1"></i> Apply
                            </button>
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-sm btn-outline-secondary w-100 p-2" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Tabs (Reordered: Graph -> Recent -> Month -> All) -->
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-graph-tab" onclick="fetchGraphData()"
                                data-bs-toggle="pill" data-bs-target="#pills-graph" type="button" role="tab"
                                aria-selected="true">
                                <i class="bi bi-pie-chart-fill me-1"></i> Graph
                            </button>
                        </li>
                        <!-- <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-recent-tab" onclick="fetchRecentData()"
                                data-bs-toggle="pill" data-bs-target="#pills-recent" type="button" role="tab"
                                aria-selected="false">
                                <i class="bi bi-clock-history me-1"></i> Recent
                            </button>
                        </li> -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-summary-tab" onclick="fetch_attendance()"
                                data-bs-toggle="pill" data-bs-target="#pills-summary" type="button" role="tab"
                                aria-selected="false">
                                <i class="bi bi-calendar-month me-1"></i> Attendance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-all-tab" onclick="fetch_all_activity();"
                                data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab"
                                aria-selected="false">
                                <i class="bi bi-list-ul me-1"></i> All Activity
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="pills-tabContent">

                        <!-- Tab 1: Graph Data (Filtered) -->
                        <div class="tab-pane fade show active" id="pills-graph" role="tabpanel">
                            <h6 class="fw-bold mb-3 text-secondary">Time Distribution (Filtered)</h6>

                            <div class="chart-wrapper">
                                <div id="projectChart" class="project-chart-container"></div>
                                <div id="chart-empty" class="chart-empty-msg">No data in selected range</div>
                            </div>
                        </div>


                        <!-- Tab 2: Recent Activity (Filtered) -->
                        <div class="tab-pane fade" id="pills-recent" role="tabpanel">
                            <h6 class="fw-bold mb-3 text-secondary">Recent Entries (Filtered)</h6>
                            <!-- Added tab-scrollable-area class here -->
                            <div class="list-group list-group-flush tab-scrollable-area" id="recent-activity-list">
                                <!-- Recent items list -->
                            </div>
                            <div id="empty-state-recent" class="text-center py-4 text-muted" style="display:none;">
                                No recent activity in range.
                            </div>
                        </div>

                        <!-- Tab 3: Monthly Summary (Always Current Month) -->
                        <div class="tab-pane fade" id="pills-summary" role="tabpanel">

                            <!-- HEADER -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-secondary mb-0">
                                    Monthly Summary – <span id="current-month-name"></span>
                                </h6>

                                <div class="d-flex align-items-center gap-2">
                                    <input type="month" id="month-select" onchange="fetch_attendance();"
                                        class="form-control form-control-sm shadow-sm" style="width:160px;">
                                </div>
                            </div>

                            <!-- CONTAINER -->
                            <div id="month-stats-container" class="tab-scrollable-area p-2">
                                <!-- Summary items will be generated here -->
                            </div>

                        </div>


                        <!-- Tab 4: All Activity (Filtered) -->
                        <div class="tab-pane fade" id="pills-all" role="tabpanel">
                            <h6 class="fw-bold mb-3 text-secondary">Log History (Filtered)</h6>
                            <!-- Added tab-scrollable-area class logic via CSS -->
                            <div class="table-responsive tab-scrollable-area">
                                <table class="table table-custom table-hover mb-0">
                                    <thead class="sticky-top bg-white">
                                        <tr>
                                            <th>Project</th>
                                            <th>Desc</th>
                                            <th>Date</th>
                                            <th>Duration</th>
                                            <th>Act</th>
                                        </tr>
                                    </thead>
                                    <tbody id="all-activity-body">
                                        <!-- All rows -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" id="pagination-container"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="empty-state-all" class="text-center py-4 text-muted" style="display:none;">
                                No entries found in range.
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="assets/bootstrap.bundle.min.js"></script>
    <script>
        let projectList = [];
        let currentPage = 1;

        let limit = 10;
        let myChart = null;
        let projectColors = {};


        let startTime = document.getElementById('filterStart');
        let endTime = document.getElementById('filterEnd');
        const getRandomColor = () => '#' + Math.floor(Math.random() * 16777215).toString(16);


        document.addEventListener('DOMContentLoaded', function () {
            fetch_project();
            fetchGraphData();
        });

        function updateUI() {
            // fetchRecentData();
            fetch_attendance();
            fetch_all_activity(1);
            // fetch_this_month();
            fetchGraphData();
        }


        document.getElementById('createProjectForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            document.getElementById('projectSubmitBtn').disabled = true; // Disable button to prevent multiple submissions

            const newName = document.getElementById('newProjectName').value.trim();
            if (!newName) {
                alert("Enter project name!");
                return;
            }

            // Prepare request
            const payload = {
                function: "create_project",
                project_name: newName
            };

            // Send to backend API
            const res = await fetch("api/Projects.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();

            if (result.status) {
                alert("Project created successfully!");
                fetch_project();
                document.getElementById('createProjectForm').reset();
                document.getElementById('tab-log-time').click();
                document.getElementById('projectSubmitBtn').disabled = false;
                // Refresh the project list after creation
            } else {
                alert("Failed to create project! || project may already exist.");
                document.getElementById('createProjectForm').reset();
                document.getElementById('projectSubmitBtn').disabled = false;
            }
        });




        function fetch_project() {
            fetch("api/Projects.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    function: "fetch_projects_name"
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status && response.data) {
                        projectList = response.data;
                        renderProjectOptions();
                    }
                })
                .catch(err => {
                    console.log("MONTH ERROR:", err);
                });
        }

        function renderProjectOptions() {
            let data = projectList;
            const select = document.getElementById('projectSelect');

            if (!select) {
                console.error("Dropdown element 'projectSelect' not found.");
                return;
            }

            // Reset dropdown
            select.innerHTML = `<option value="" disabled selected>Select a project...</option>`;

            // STATIC DUMMY DATA (Correct object array)


            // Loop and render
            projectList.forEach(project => {
                console.log("Current Project Data:", project);

                const option = document.createElement("option");
                option.value = project.id;
                option.textContent = project.project_name;

                if (project.color) {
                    option.style.color = project.color;
                }

                select.appendChild(option);
            });

        }



        let restrict_time = "";

        /* -------------------------------
           FETCH RESTRICT TIME ON PROJECT CHANGE
        -------------------------------- */
        document.getElementById('projectSelect').addEventListener('change', () => {
            let project_id = document.getElementById('projectSelect').value;

            fetch("api/Projects.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "include",
                body: JSON.stringify({
                    function: "fetch_projects_spend_time",
                    project_id: project_id
                })
            })
                .then(res => res.json())
                .then(response => {
                    restrict_time = response.restrict_time || "";
                    formatReadableTime(response.restrict_time);

                    document.getElementById('projectTime').value = response.duration || "";

                    applyRestrictTime(); // Apply time restrictions after fetching
                })
                .catch(err => {
                    console.log("ERROR:", err);
                });
        });


        // Convert restrict_time to readable format
        function formatReadableTime(timeString) {
            if (!timeString) return "";




            // Assign restrict time from API
            restrict_time = timeString || "";

            const msgBox = document.getElementById('time_alert_error');

            // CASE 1 — No restriction
            if (restrict_time === "") {
                msgBox.innerHTML = "";
            }

            // CASE 2 — Not checked in
            else if (restrict_time === "NOT_CHECKED_IN") {
                msgBox.innerHTML = `
        <span class="text-danger fw-bold">
            You have not checked in today.
        </span>`;
            }

            // CASE 3 — First log allowed
            else if (restrict_time === "ALLOW") {
                msgBox.innerHTML = `
        <span class="text-success fw-bold">
            You can start logging time now.
        </span>`;
            }

            // CASE 4 — Restriction exists → show readable time
            else {
                let readable = extractTime(timeString);

                msgBox.innerHTML = `
        <span class="text-danger fw-bold">
            Please select time <b>after ${readable}</b>.
        </span>`;
            }

        }



        function extractTime(timeString) {
            if (!timeString || typeof timeString !== "string") return "";
            // Split by space → ["2025-12-03", "11:40:00"]
            let parts = timeString.split(" ");
            return parts[1] || ""; // return only time portion
        }


        document.getElementById('timeForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            document.getElementById('timeFormSubmitBtn').disabled = true; // Disable button to prevent multiple submissions


            const project_id = document.getElementById('projectSelect').value;
            const start = document.getElementById('startTime').value;
            const end = document.getElementById('endTime').value;
            const desc = document.getElementById('description').value;

            if (!start || !end) {
                alert("Please select start and end times.");
                return;
            }

            const startDate = new Date(start);
            const endDate = new Date(end);

            if (endDate <= startDate) {
                alert("End time must be after Start time!");
                return;
            }

            const durationMinutes = (endDate - startDate) / (1000 * 60);


            // Prepare payload for API
            const payload = {
                function: "save_project_log",
                project_id: project_id,
                start_time: start,
                end_time: end,
                description: desc,
                duration: durationMinutes
            };

            // Call backend
            const res = await fetch("api/Projects.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include", // IMPORTANT for PHP session
                body: JSON.stringify(payload)
            });

            const result = await res.json();

            if (result.status) {
                alert("Time logged successfully!");
                document.getElementById('timeFormSubmitBtn').disabled = false;
                document.getElementById('timeForm').reset();
                updateUI();
            } else {
                alert("Failed to save time log!");
                document.getElementById('timeForm').reset();
                document.getElementById('timeFormSubmitBtn').disabled = false;
            }
        });

        // all recent activity code start here

        // function fetchRecentData() {
        //     fetch("api/Projects.php", {
        //         method: "POST",
        //         headers: { "Content-Type": "application/json" },
        //         credentials: "include",
        //         body: JSON.stringify({
        //             function: "fetch_recent_logs"
        //         })
        //     })
        //         .then(res => res.text())  // 👈 get raw text
        //         .then(text => {
        //             console.log("RAW RESPONSE >>>", text);  // 👈 NOW WE SEE THE PROBLEM
        //             return JSON.parse(text);  // After checking, convert to JSON
        //         })
        //         .then(response => {
        //             if (!response.status || !response.data) {
        //                 renderRecentActivityTab([]);
        //                 return;
        //             }

        //             renderRecentActivityTab(response.data);
        //         })
        //         .catch(err => {
        //             console.error(err);
        //             alert("Error loading recent activity!");
        //         });
        // }

        // function renderRecentActivityTab(data) {
        //     const list = document.getElementById('recent-activity-list');
        //     const emptyState = document.getElementById('empty-state-recent');
        //     list.innerHTML = '';

        //     if (!data || data.length === 0) {
        //         emptyState.style.display = 'block';
        //         return;
        //     }

        //     emptyState.style.display = 'none';

        //     const recent = [...data].sort((a, b) => new Date(b.start) - new Date(a.start));

        //     recent.forEach(entry => {
        //         const color = entry.color || '#6b7280';

        //         const html = `
        //     <div class="list-group-item recent-item py-3" style="border-left-color: ${color}">
        //         <div class="d-flex w-100 justify-content-between align-items-center mb-1">
        //             <h6 class="mb-0 fw-bold">${entry.project}</h6>
        //             <small class="text-muted">${formatDate(entry.start)}</small>
        //         </div>
        //         <p class="mb-1 small text-muted">${entry.desc || 'No description'}</p>
        //         <small class="fw-bold text-primary">${formatDuration(entry.duration)}</small>
        //     </div>
        // `;

        //         list.insertAdjacentHTML('beforeend', html);
        //     });
        // }



        // all recent activity code end here

        // all this month  code start here

        // function fetch_this_month() {
        //     fetch("api/Projects.php", {
        //         method: "POST",
        //         headers: { "Content-Type": "application/json" },
        //         credentials: "include",
        //         body: JSON.stringify({
        //             function: "fetch_this_month_logs",
        //             startTime: startTime.value,
        //             endTime: endTime.value
        //         })
        //     })
        //         .then(res => res.json())
        //         .then(response => {
        //             if (response.status && response.data) {
        //                 renderMonthlySummary(response.data);
        //             }
        //         })
        //         .catch(err => {
        //             console.log("MONTH ERROR:", err);
        //         });
        // }

        // function renderMonthlySummary(data) {
        //     const container = document.getElementById('month-stats-container');

        //     if (!data || data.length === 0) {
        //         container.innerHTML = '<p class="text-muted text-center py-5">No data for this month yet.</p>';
        //         return;
        //     }

        //     container.innerHTML = '';

        //     let grandTotal = 0;

        //     data.forEach(row => {
        //         grandTotal += Number(row.duration);
        //     });

        //     data.forEach(row => {
        //         const minutes = Number(row.duration);
        //         const percent = (minutes / grandTotal) * 100;

        //         const html = `
        //     <div class="project-stat-row mb-2">
        //         <div class="stat-label text-truncate">${row.project}</div>

        //         <div class="progress-container">
        //             <div class="progress-bar-fill"
        //                 style="width: ${percent}%; background-color: ${row.color};">
        //             </div>
        //         </div>

        //         <div class="stat-value">${formatDuration(minutes)}</div>
        //     </div>
        // `;

        //         container.insertAdjacentHTML('beforeend', html);
        //     });
        // }


        function fetch_attendance() {

            let monthSelect = document.getElementById('month-select');
            let selectedMonth = monthSelect.value; // YYYY-MM


            fetch("api/Projects.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    function: "fetch_attendance",
                    month: selectedMonth
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status && response.data) {
                        renderAttendanceSummary(response.data, response.month, response.total_days);
                    } else {
                        renderAttendanceSummary([]);
                    }
                })
                .catch(err => {
                    console.log("ATTENDANCE ERROR:", err);
                });
        }

        function renderAttendanceSummary(data, month, total_days) {
            const container = document.getElementById('month-stats-container');

            document.getElementById('current-month-name').innerText = formatMonthYear(month);

            if (!data || data.length === 0) {
                container.innerHTML = `
            <p class="text-muted text-center py-5">
                No attendance records available for this month.
            </p>`;
                return;
            }

            let html = `
            <div class="mb-3">
                <strong>Total Working on This Month :</strong> ${total_days}
        </div>
        <table class="table table-hover table-bordered"  id="attendance-table">
            <thead class="" >
                <tr style="background-color: var(--primary) !important; color: #fff;">
                    <th class="text-center">S.No.</th>    
                    <th class="text-center">Date</th>
                    <th class="text-center">Check-In</th>
                    <th class="text-center" >Check-Out</th>
                    <th class="text-center">Working Hours</th>
                </tr>
            </thead>
            <tbody>
    `;

            data.forEach((row, index) => {
                html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${formatDateAttendance(row.date)}</td>
                <td class="text-center">${row.check_in ? formatTime(row.check_in) : "-"}</td>
                <td class="text-center">${row.check_out ? formatTime(row.check_out) : "-"}</td>
                <td class="text-center"><b>${row.working_formatted || "0m"}</b></td>
            </tr>
        `;
            });

            html += `
            </tbody>
        </table>
    `;

            container.innerHTML = html;
        }



        // all this month  code end here

        // all activity code start here

        function fetch_all_activity(page = 1) {
            currentPage = page;



            const offset = (page - 1) * limit;

            fetch("api/Projects.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    function: "fetch_all_activity_logs",
                    startTime: startTime,
                    endTime: endTime,
                    limit: limit,
                    offset: offset,
                    startTime: startTime.value,
                    endTime: endTime.value
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status) {
                        renderAllActivity(response.data);
                        renderPagination(response.total, response.limit, page);
                    }
                })
                .catch(err => console.log("ACTIVITY ERROR:", err));
        }

        function renderAllActivity(data) {
            const tbody = document.getElementById('all-activity-body');
            const emptyState = document.getElementById('empty-state-all');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            data.forEach(entry => {
                const row = `
            <tr>
                <td class="fw-bold" style="color:${entry.color || '#333'}">${entry.project}</td>
                <td class="text-muted small text-truncate" style="max-width: 150px;">${entry.desc || '-'}</td>
                <td>${formatDate(entry.start)}</td>
                <td>${formatDuration(entry.duration)}</td>
                <td>
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteEntry(${entry.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                </td>
            </tr>`;

                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        function renderPagination(total, limit, page) {
            const totalPages = Math.ceil(total / limit);
            const container = document.getElementById("pagination-container");

            container.innerHTML = "";

            if (totalPages <= 1) return;

            let html = `<ul class="pagination justify-content-center">`;

            // -------------------------
            // PREVIOUS BUTTON
            // -------------------------
            const prevDisabled = page <= 1 ? "disabled" : "";
            html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="javascript:void(0)" onclick="fetch_all_activity(${page - 1})">Prev</a>
        </li>
    `;

            // -------------------------
            // PAGE RANGE (show 5 before & after current page)
            // -------------------------
            let start = Math.max(1, page - 5);
            let end = Math.min(totalPages, page + 5);

            // If starting pages skipped, show "1 ..."
            if (start > 1) {
                html += `
            <li class="page-item">
                <a class="page-link" onclick="fetch_all_activity(1)">1</a>
            </li>
            <li class="page-item disabled"><a class="page-link">...</a></li>
        `;
            }

            // Main page buttons
            for (let i = start; i <= end; i++) {
                const active = i === page ? "active" : "";
                html += `
            <li class="page-item ${active}">
                <a class="page-link" href="javascript:void(0)" onclick="fetch_all_activity(${i})">${i}</a>
            </li>
        `;
            }

            // If ending pages skipped
            if (end < totalPages) {
                html += `
            <li class="page-item disabled"><a class="page-link">...</a></li>
            <li class="page-item">
                <a class="page-link" onclick="fetch_all_activity(${totalPages})">${totalPages}</a>
            </li>
        `;
            }

            // -------------------------
            // NEXT BUTTON
            // -------------------------
            const nextDisabled = page >= totalPages ? "disabled" : "";
            html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="javascript:void(0)" onclick="fetch_all_activity(${page + 1})">Next</a>
        </li>
    `;

            html += `</ul>`;

            container.innerHTML = html;
        }

        // all activity code end here

        // graph data code start here 

        function fetchGraphData() {

            fetch("api/Projects.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    function: "fetch_graph_data",
                    startTime: startTime.value,
                    endTime: endTime.value
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status && Array.isArray(response.data)) {
                        renderGraphTab(response.data);
                    } else {
                        renderGraphTab([]);
                    }
                })
                .catch(err => {
                    console.error("GRAPH ERROR:", err);
                    renderGraphTab([]);
                });
        }

        function renderGraphTab(data) {
            const container = document.getElementById('projectChart');

            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center py-5">No data for this month yet.</p>';
                return;
            }

            container.innerHTML = '';

            let grandTotal = 0;

            data.forEach(row => {
                grandTotal += Number(row.duration);
            });

            data.forEach(row => {
                const minutes = Number(row.duration);
                const percent = (minutes / grandTotal) * 100;

                const html = `
            <div class="project-stat-row mb-2">
                <div class="stat-label text-truncate">${row.project}</div>

                <div class="progress-container">
                    <div class="progress-bar-fill"
                        style="width: ${percent}%; background-color: ${row.color};">
                    </div>
                </div>

                <div class="stat-value">${formatDuration(minutes)}</div>
            </div>
        `;

                container.insertAdjacentHTML('beforeend', html);
            });
        }




        document.addEventListener('DOMContentLoaded', () => {
            // getGeotag();
        });

        let latitude = '';
        let longitude = '';

        function getGeotag() {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;

                    console.log("Latitude:", latitude);
                    console.log("Longitude:", longitude);


                },
                (error) => {
                    console.error(error);
                    alert("Unable to get your current location. Please enable GPS.");
                }, {
                enableHighAccuracy: true,
                timeout: 7000,
                maximumAge: 0
            }
            );
        }





        function deleteEntry(id) {

            if (!confirm("Are you sure you want to delete this entry?")) {
                return;
            }

            fetch("api/Projects.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "include",
                body: JSON.stringify({
                    function: "delete_time_entry",
                    entry_id: id
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status) {
                        alert("Entry deleted successfully.");
                        fetch_all_activity(currentPage); // Refresh current page
                    } else {
                        alert("Failed to delete entry.");
                    }
                })
                .catch(err => {
                    console.error("DELETE ERROR:", err);
                    alert("Error deleting entry.");
                });
        }


        // graph data code end here


        function checkIn() {

            fetch("api/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    function: "check_in",
                    latitude: latitude,
                    longitude: longitude
                })
            })
                .then(res => res.json())
                .then(response => {
                    alert(response.message);
                    if (response.status) {
                        location.reload();
                    }
                })
                .catch(err => {

                });
        }

        function checkOut() {

            fetch("api/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    function: "check_out",
                    latitude: latitude,
                    longitude: longitude
                })
            })
                .then(res => res.json())
                .then(response => {
                    alert(response.message);
                    if (response.status) {
                        location.reload();
                    }
                })
                .catch(err => {

                });
        }


        //  logout code start here #
        function log_out() {

            if (confirm("Are you sure you want to logout?")) {
                fetch("api/login.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    credentials: "include",
                    body: JSON.stringify({
                        function: "logout"
                    })
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status) {
                            window.location.href = "login.php";
                        } else {
                            alert("Logout failed!");
                        }
                    })
                    .catch(err => {
                        console.error("LOGOUT ERROR:", err);
                    });
            }
        }

        //  logout code end here 

        //  helper function start

        // function formatDuration(minutes) {
        //     minutes = Number(minutes); // ensure numeric
        //     const h = Math.floor(minutes / 60);
        //     const m = minutes % 60;
        //     return `${h}h ${m}m`;
        // }



        var minute_per_day = Number("<?php echo $_SESSION['user']['day_minutes']; ?>");

        function formatDuration(minutes) {
            minutes = Number(minutes);

            // 1️⃣ Calculate days based on company minutes per day
            const days = Math.floor(minutes / minute_per_day);

            // 2️⃣ Remaining minutes after full days
            let remaining = minutes % minute_per_day;

            // 3️⃣ Convert remaining minutes into hours + minutes
            const hours = Math.floor(remaining / 60);
            const mins = remaining % 60;

            // 4️⃣ Build result string
            let str = "";
            if (days > 0) str += `${days}d `;
            if (hours > 0) str += `${hours}h `;
            if (mins > 0) str += `${mins}m`;

            return str.trim();
        }


        /* ----------------------------------------------------
           APPLY RESTRICT TIME RULES (MAIN FUNCTION)
        -----------------------------------------------------*/
        function applyRestrictTime() {
            const startInput = document.getElementById("startTime");
            const endInput = document.getElementById("endTime");

            console.log("Restrict Time:", restrict_time);

            /* ----------------------------------------------------
               1️⃣ USER NOT CHECKED IN TODAY
            -----------------------------------------------------*/
            if (restrict_time === "NOT_CHECKED_IN") {
                startInput.value = "";
                endInput.value = "";
                startInput.disabled = true;
                endInput.disabled = true;
                alert("You have not checked in today. Please check in first.");
                return;
            }

            /* ----------------------------------------------------
               2️⃣ FIRST LOG OF THE DAY → ALLOW ONLY TODAY
            -----------------------------------------------------*/
            if (restrict_time === "ALLOW") {
                startInput.disabled = false;
                endInput.disabled = false;

                // restrict only to today's date range
                applyTodayMinMax(startInput);
                applyTodayMinMax(endInput);

                console.log("First log → allowed for today only.");
                return;
            }

            /* ----------------------------------------------------
               3️⃣ RESTRICT BASED ON LAST PROJECT'S END TIME
            -----------------------------------------------------*/
            if (!restrict_time || restrict_time === "" || restrict_time === "0") {
                // No valid tracking time
                startInput.disabled = true;
                endInput.disabled = true;
                alert("Previous project’s time missing.");
                return;
            }

            startInput.disabled = false;
            endInput.disabled = false;

            // convert restrict time to date
            let lastEnd = new Date(restrict_time);

            // add 1 minute
            lastEnd.setMinutes(lastEnd.getMinutes() + 1);

            // convert to datetime-local compatible format
            let minAllowed = toLocalInputFormat(lastEnd);

            // apply dynamic restriction
            startInput.min = minAllowed;
            endInput.min = minAllowed;

            // limit to today also
            applyTodayMinMax(startInput);
            applyTodayMinMax(endInput);

            // ensure min stays after restrict_time
            if (startInput.min < minAllowed) startInput.min = minAllowed;
            if (endInput.min < minAllowed) endInput.min = minAllowed;

            console.log("Allowed from:", startInput.min);
        }

        /* ----------------------------------------------------
           END TIME MUST BE > START TIME
        -----------------------------------------------------*/
        document.getElementById("endTime").addEventListener("change", () => {
            let start = document.getElementById("startTime").value;
            let end = document.getElementById("endTime").value;

            if (!start) {
                alert("Select Start Time first!");
                document.getElementById("endTime").value = "";
                return;
            }

            let startDate = new Date(start);
            let endDate = new Date(end);

            // Current IST Time
            const now = getISTDate();

            // ❌ Condition 1: End time < Start time
            if (endDate < startDate) {
                alert("End Time cannot be earlier than Start Time.");
                document.getElementById("endTime").value = "";
                return;
            }

            // ❌ Condition 2: End time > Current IST time
            if (endDate > now) {
                alert("End Time cannot be greater than the current time.");
                document.getElementById("endTime").value = "";
                return;
            }
        });

        /* ----------------------------------------------------
           APPLY ONLY TODAY RANGE (min & max)
        -----------------------------------------------------*/
        function applyTodayMinMax(input) {
            const now = getISTDate();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, "0");
            const d = String(now.getDate()).padStart(2, "0");

            input.min = `${y}-${m}-${d}T00:00`;
            input.max = `${y}-${m}-${d}T23:59`;
        }

        /* ----------------------------------------------------
           GET IST DATE
        -----------------------------------------------------*/
        function getISTDate() {
            const now = new Date();
            const utc = now.getTime() + now.getTimezoneOffset() * 60000;
            return new Date(utc + 330 * 60000);
        }





        /* ----------------------------------------------------
           FORMAT FOR datetime-local
        -----------------------------------------------------*/
        function toLocalInputFormat(dateObj) {
            const pad = (n) => n.toString().padStart(2, "0");

            return (
                dateObj.getFullYear() +
                "-" +
                pad(dateObj.getMonth() + 1) +
                "-" +
                pad(dateObj.getDate()) +
                "T" +
                pad(dateObj.getHours()) +
                ":" +
                pad(dateObj.getMinutes())
            );
        }

        /* ----------------------------------------------------
           SET NOW BUTTON
        -----------------------------------------------------*/
        function setNow(inputId) {
            const input = document.getElementById(inputId);
            const istNow = getISTDate();
            applyTodayMinMax(input); // keep only today's date allowed
            input.value = toLocalInputFormat(istNow);
        }

        /* ----------------------------------------------------
           INITIALIZE ON PAGE LOAD
        -----------------------------------------------------*/
        window.onload = function () {
            applyTodayMinMax(document.getElementById("startTime"));
            applyTodayMinMax(document.getElementById("endTime"));
        };




        function formatDate(dateString) {
            const options = {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        function formatDateAttendance(dateString) {
            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            };
            return new Date(dateString).toLocaleDateString('en-GB', options);
        }


        function formatMonthYear(ym) {
            const date = new Date(ym + "-01");
            return date.toLocaleDateString("en-US", {
                month: "long",
                year: "numeric"
            });
        }



        function formatTime(dateTimeStr) {
            const d = new Date(dateTimeStr);
            return d.toLocaleTimeString("en-IN", {
                hour: "2-digit",
                minute: "2-digit"
            });
        }

        function clearFilters() {
            document.getElementById('filterStart').value = '';
            document.getElementById('filterEnd').value = '';
            updateUI();
        }

        document.getElementById("month-select").addEventListener("change", () => {
            const value = document.getElementById("month-select").value; // YYYY-MM

            if (!value) return;

            const dateObj = new Date(value + "-01");
            document.getElementById("current-month-name").innerText =
                dateObj.toLocaleString("en-IN", {
                    month: "long",
                    year: "numeric"
                });

            fetch_attendance(value);
        });


        //  counter 
        // Values from PHP
        const CHECK_IN_TIME = "<?php echo $checkInTime; ?>";      // Example: 2025-01-10 10:22:00
        const CHECK_OUT = "<?php echo $checkOut; ?>";             // "Y" or ""
        const CHECK_OUT_TIME = "<?php echo $checkOutTime; ?>";    // Example: 2025-01-10 18:10:00

        function startWorkingCounter(startTime) {

            const displayEl = document.getElementById("total_working_display");

            // If no check-in time
            if (!startTime || startTime === "0" || startTime === "" || startTime === "null") {
                displayEl.textContent = "00 : 00 : 00";
                return;
            }

            // If already checked out → show static final duration
            if (CHECK_OUT === "Y" && CHECK_OUT_TIME) {
                displayEl.textContent = calculateWorkedTime(startTime, CHECK_OUT_TIME);
                return;
            }

            const startTimestamp = new Date(startTime).getTime();

            function updateCounter() {

                // STOP LIVE COUNTER if user checked out
                if (CHECK_OUT === "Y") return;

                let now = Date.now();
                let diff = now - startTimestamp;  // milliseconds difference

                displayEl.textContent = formatTimeMs(diff);
            }

            updateCounter();                     // Run immediately
            setInterval(updateCounter, 1000);    // Update every second
        }

        // Convert milliseconds → HH:MM:SS
        function formatTimeMs(ms) {
            let seconds = Math.floor(ms / 1000);
            let hrs = Math.floor(seconds / 3600);
            seconds %= 3600;
            console.log('ms', ms);
            let mins = Math.floor(seconds / 60);
            seconds = seconds % 60;

            return `${String(hrs).padStart(2, '0')} : ${String(mins).padStart(2, '0')} : ${String(seconds).padStart(2, '0')}`;
        }

        // Calculate exact worked time between two timestamps
        function calculateWorkedTime(start, end) {
            if (!end) return "00 : 00 : 00";
            console.log(start, end);
            const diff = new Date(end).getTime() - new Date(start).getTime();
            return formatTimeMs(diff);
        }

        // Start counter
        startWorkingCounter(CHECK_IN_TIME);



    </script>
</body>

</html>