<?php
session_start();

// If user not logged in → redirect to login page
if (!isset($_SESSION['user']) || empty($_SESSION['user']['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevTrack | Super Admin Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <script src="assets/chart.js"></script>


    <style>
        :root {

            --primary: #6366f1;
            --bg-light: #f8fafc;
            --secondary: #10b981;
            --card-bg: #ffffff;
            --text-dark: #1f2937;
            --card-shadow: 0 4px 6px -1px #242065c1, 0 2px 4px -1px #24206ecb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #334155;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar-admin {
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            z-index: 100;
        }

        .brand-text {
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .admin-badge {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            font-size: 0.7rem;
            padding: 0.25em 0.6em;
            border-radius: 4px;
            text-transform: uppercase;
            margin-left: 8px;
        }

        /* Cards */
        .admin-card {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .user-card-header {
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
        }

        .user-details-collapse {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        /* Sidebar Transition */
        #leftSidebar {
            transition: all 0.3s ease;
        }

        /* Avatar */
        .profile-avatar-placeholder {
            width: 80px;
            height: 80px;
            background: #e0e7ff;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto;
        }

        .avatar-small {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
            margin: 0;
        }

        /* Utilities */
        .btn-primary-custom {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #334155;
            font-size: 1.1rem;
        }

        /* Pills */
        .project-badge-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .project-pill {
            font-size: 0.75rem;
            padding: 4px 10px;
            background-color: #f1f5f9;
            color: #475569;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        /* Fullscreen Logic */
        .card-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1050;
            padding: 2rem;
            background-color: #f8fafc;
            overflow-y: auto;
            margin: 0;
            border-radius: 0;
        }

        .zoom-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #cbd5e1;
            background: transparent;
            border: none;
            z-index: 10;
        }

        .zoom-btn:hover {
            color: var(--primary);
        }



        /*  user card  */

        /* --- Project Chip Styling --- */
        .chip-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .chip {
            background-color: #eef6fc;
            /* Light Blue Background */
            color: #333;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .chip:hover {
            border-color: #2f80ed;
        }

        .chip-time {
            background-color: rgba(255, 255, 255, 0.8);
            color: #2f80ed;
            /* Primary Blue */
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        /* --- Date & Status Header --- */
        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .date-text {
            color: #555;
            font-weight: 500;
        }

        .badge-present {
            background-color: #27ae60;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-admin sticky-top">
        <div class="container">
            <a href="#" class="text-decoration-none d-flex align-items-center">
                <i class="bi bi-layers-fill fs-4 text-primary me-2"></i>
                <span class="fs-5 brand-text">DevTrack</span>
                <span class="admin-badge">Super Admin</span>
            </a>
            <div class="dropdown">
                <button class="btn border-0 p-1 pe-3 rounded-pill shadow-sm d-flex align-items-center gap-2"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown"
                    style="background: #f0f2f5; transition: all 0.2s ease;">

                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        AU
                    </div>

                    <?php
                    if (isset($_SESSION['user']['name'])) {
                        $use_name = $_SESSION['user']['name'];
                        $email = $_SESSION['user']['email'];
                    } else {
                        $use_name = "Not Logged In";
                        $email = "Not Logged In";
                    }
                    ?>


                    <div class="d-none d-sm-block text-start lh-1" style="margin-right: 5px;">
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo $use_name; ?></div>
                        <span class="text-muted" style="font-size: 0.75rem;">Super Admin</span>
                    </div>

                    <i class="bi bi-chevron-down text-muted small"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2 rounded-3" style="width: 220px;">

                    <li class="px-3 py-2 mb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white small"
                                style="width: 32px; height: 32px; background: #ccc;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>

                                <h6 class="mb-0 text-dark small"><?php echo $use_name; ?></h6>
                                <small class="text-muted" style="font-size: 11px;"> <?php echo $email; ?> </small>
                            </div>
                        </div>
                    </li>

                    <li>
                        <a class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2"
                            href="user_profile.php?email=<?php echo htmlspecialchars(urlencode($email)); ?>">
                            <i class="bi bi-person opacity-50"></i> My Profile
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider my-2 opacity-50">
                    </li>

                    <li>
                        <a class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2"
                            href="superadmin_profile.php">
                            <i class="bi bi-person opacity-50"></i> Company profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2"
                            href="javascript:void(0);">
                            <i class="bi bi-sliders opacity-50"></i> Account Settings
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-2 opacity-50">
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2 text-danger bg-danger-subtle-hover"
                            href="javascript:void(0);" onclick="log_out();" id="logoutBtn">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </a>
                    </li>
                </ul>
            </div>

            <style>
                .dropdown-toggle::after {
                    display: none;
                }

                /* Hide default arrow */

                /* Dropdown Item Hover */
                .dropdown-item:active {
                    background-color: #f8f9fa;
                    color: #000;
                }

                /* Logout Red Hover Effect */
                .bg-danger-subtle-hover:hover {
                    background-color: #fff5f5 !important;
                    color: #dc3545 !important;
                }

                /* Button Hover Effect: Darkens the gray slightly when hovered */
                #userDropdown:hover {
                    background: #e4e6e9 !important;
                    /* Slightly darker gray on hover */
                }
            </style>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row g-4">

            <!-- LEFT COLUMN: ADD USER FORM (HIDDEN BY DEFAULT) -->
            <div class="col-lg-4 d-none" id="leftSidebar">
                <div class="admin-card p-4 sticky-sidebar">
                    <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                                <i class="bi bi-person-plus-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Onboard Employee</h6>
                                <small class="text-muted">Create new account</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" onclick="toggleSidebar()" aria-label="Close"></button>
                    </div>

                    <form id="createUserForm" enctype="multipart/form-data">
                        <!-- Photo -->
                        <div class="text-center mb-4">
                            <label class="position-relative d-inline-block avatar-upload" style="cursor: pointer;">
                                <div
                                    class="profile-avatar-placeholder shadow-sm border border-2 border-light position-relative overflow-hidden">
                                    <i class="bi bi-camera-fill text-muted opacity-50"></i>
                                    <img id="avatarPreview" src=""
                                        class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover d-none">
                                </div>
                                <input type="file" name="profile_image" class="d-none" accept="image/*"
                                    onchange="previewImage(this)">
                                <span
                                    class="badge bg-dark position-absolute bottom-0 start-50 translate-middle-x mb-2 small shadow-sm">Upload</span>
                            </label>
                        </div>

                        <!-- Fields -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="e.g. John Doe"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="name@company.com"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Mobile</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted"><i class="bi bi-phone"></i></span>
                                <input type="tel" name="mobile" class="form-control" placeholder="+1 234 567 890">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••"
                                    required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom fw-bold py-2 shadow-sm">Create
                                Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: USER LIST (FULL WIDTH BY DEFAULT) -->
            <div class="col-lg-12" id="rightContent">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Employee Directory</h5>
                        <small class="text-muted">Manage your team members</small>
                    </div>

                    <div class="d-flex gap-2">
                        <!-- Add User Toggle Button -->
                        <button class="btn btn-primary-custom shadow-sm px-3 d-flex align-items-center"
                            onclick="toggleSidebar()">
                            <i class="bi bi-plus-lg me-2"></i> Add Employee
                        </button>

                        <div class="input-group w-auto shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="UserSearch"
                                placeholder="Search users...">
                        </div>
                    </div>
                </div>
                <div id="rightContentUserCards">
                    <!-- User Cards Injected via JS -->
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Loading users...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="assets/bootstrap.bundle.min.js"></script>
    <script>

        var usersData = [];


        // --- 1. UI Logic: Sidebar Toggle ---
        function toggleSidebar() {
            const sidebar = document.getElementById('leftSidebar');
            const content = document.getElementById('rightContent');

            if (sidebar.classList.contains('d-none')) {
                // Show Sidebar
                sidebar.classList.remove('d-none');
                content.classList.remove('col-lg-12');
                content.classList.add('col-lg-8');
            } else {
                // Hide Sidebar
                sidebar.classList.add('d-none');
                content.classList.remove('col-lg-8');
                content.classList.add('col-lg-12');
            }
        }

        // --- 2. Image Preview ---
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('avatarPreview');
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- 3. Toggle Individual Card Zoom ---
        function toggleZoom(cardId) {
            const card = document.getElementById(cardId);
            const btn = card.querySelector('.zoom-btn i');
            card.classList.toggle('card-fullscreen');

            if (card.classList.contains('card-fullscreen')) {
                document.body.style.overflow = 'hidden';
                btn.classList.remove('bi-arrows-angle-expand');
                btn.classList.add('bi-arrows-angle-contract');
                // Ensure details are open
                const collapseEl = card.querySelector('.collapse');
                if (collapseEl) {
                    const bsCollapse = new bootstrap.Collapse(collapseEl, {
                        toggle: false
                    });
                    bsCollapse.show();
                }
            } else {
                document.body.style.overflow = 'auto';
                btn.classList.add('bi-arrows-angle-expand');
                btn.classList.remove('bi-arrows-angle-contract');
            }
        }

        // --- 4. DATA LOGIC ---


        document.addEventListener("DOMContentLoaded", function () {
            fetch_data();
        });

        // Form Submit
        document.getElementById("createUserForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("createUserForm");
            let formData = new FormData(form);
            formData.append("function", "create_user");

            fetch("api/SuperAdmin.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(response => {
                    alert(response.message || "User created!");
                    form.reset();
                    document.getElementById('avatarPreview').classList.add('d-none');
                    toggleSidebar(); // Hide sidebar after success
                    fetch_data(); // Refresh list
                })
                .catch(err => {
                    console.error(err);
                    alert("API Error. Check console.");
                });
        });



        function fetch_data() {
            fetch("api/SuperAdmin.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    function: "fetch_all_users_display"
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.data) {
                        usersData = data.data;
                        renderUserCards(data.data);
                    } else {
                        renderUserCards([]); // Fallback
                    }
                })
                .catch(err => {
                    console.error("Fetch error, using mock data", err);
                    renderUserCards([]);
                });
        }




        function debounce(func, delay = 300) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(this, args), delay);
            };
        }


        document.addEventListener('DOMContentLoaded', () => {
            const UserSearch = document.getElementById("UserSearch");

            UserSearch.addEventListener("input", debounce(() => {

                const keyword = UserSearch.value.trim().toLowerCase();

                // If input is empty → show all users
                if (keyword === "") {
                    renderUserCards(usersData);
                    return;
                }

                // Filter users by name, email, or mobile
                let filtered = usersData.filter(user => {
                    return (
                        user.name.toLowerCase().includes(keyword) ||
                        (user.email && user.email.toLowerCase().includes(keyword)) ||
                        (user.mobile && user.mobile.includes(keyword))
                    );
                });

                renderUserCards(filtered);

            }, 300));   // 300ms debounce

        })



        function renderUserCards(users) {
            const container = document.getElementById("rightContentUserCards");
            container.innerHTML = "";

            if (users.length === 0) {
                container.innerHTML = `Records not found.`;
                return;
            }

            users.forEach((user) => {
                const initials = (user.name || "U").split(" ").filter(w => w.length > 0).map(w => w[0].toUpperCase()).join("").substring(0, 2);

                // 2. Process Projects & Durations
                // Split strings into arrays
                let projectList = user.project_names ? user.project_names.split(",") : [];
                let durationList = user.project_durations ? user.project_durations.split(",") : [];

                // Generate HTML for Chips (Name + Time)
                // We use map on projectList and get the corresponding duration by index
                let projectsHTML = projectList.slice(0, 5).map((name, index) => {
                    let time = durationList[index] ? durationList[index].trim() : "0h";
                    return `
                <div class="chip">
                    <span>${name.trim()}</span>
                    <span class="chip-time">${calculateHours(time)}</span>
                </div>
            `;
                }).join("");

                // If no projects exist
                if (projectsHTML === "") {
                    projectsHTML = `<span class="text-muted small">No active projects</span>`;
                }

                const cardId = `card-user-${user.id}`;
                const collapseId = `detailsUser-${user.id}`;
                const chartId = `chartUser-${user.id}`;
                const tabGraph = `tab-graph-${user.id}`;
                const tabAttend = `tab-attend-${user.id}`;
                const tabActivity = `tab-activity-${user.id}`;

                const html = `
                <div class="admin-card" id="${cardId}">
                    <button class="zoom-btn" onclick="toggleZoom('${cardId}')" title="Expand View">
                        <i class="bi bi-arrows-angle-expand fs-5"></i>
                    </button>

                    <div class="user-card-header">
                        <div class="row align-items-center g-3">
                            <div class="col-md-4 border-end-md">
                                <div class="d-flex align-items-center mb-3">
                                    <a href="user_profile.php?email=${encodeURIComponent(user.email)}" class="text-decoration-none d-flex align-items-center">  
                                        <div class="profile-avatar-placeholder avatar-small me-3 shadow-sm">
                                          <span class="fw-bold">${initials}</span>
                                        </div>
                                     </a>
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate">${user.name}</h6>
                                        <small class="text-primary fw-medium">Developer</small>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-1 ps-1"> 
                                    <small class="text-muted text-truncate"><i class="bi bi-envelope me-2 text-secondary"></i>${user.email}</small>
                                    <small class="text-muted"><i class="bi bi-phone me-2"></i>${user.mobile || 'N/A'}</small>
                                </div>
                            </div>
                        <div class="col-md-6 d-flex flex-column justify-content-between">
                        
                        <div class="status-row">
                            <div class="date-text">
                                <span class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">Today: <span class="attendance-badge badge-present">* ${user.attendance_status || ''} </span></span> 
                             
                            </div>
                            
                        </div>

                        <div class="info-label mb-1">Active Projects</div>
                        <div class="chip-container">
                            ${projectsHTML}
                        </div>

                        <div class="d-flex align-items-center border-top pt-2 mt-auto">
                            <span class="info-label me-2 mb-0">TOTAL HOURS:</span>
                            <div class="info-value d-flex align-items-center">
                                <i class="bi bi-clock-history text-success me-2"></i>
                                <span class="fw-bold text-dark">${calculateHours(durationList)}</span> 
                                <span class="text-muted ms-1 fs-6 fw-normal">hrs</span>
                            </div>
                        </div>
                    </div>
                            <div class="col-md-2 d-flex align-items-center justify-content-center">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-4 w-100 fw-medium"
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#${collapseId}"
                                    onclick="loadAnalytics(${user.id}, '${chartId}', '${tabAttend}', '${tabActivity}')">
                                    Analytics
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="collapse user-details-collapse" id="${collapseId}">
                        <div class="p-4">
                            <!-- HEADER: TABS + FILTERS -->
                            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 border-bottom pb-2 gap-3">
                                <!-- TABS -->
                                <ul class="nav nav-pills mb-0" role="tablist">
                                    <li class="nav-item"><button class="nav-link active rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#${tabGraph}">
                                        <i class="bi bi-bar-chart-fill me-2"></i>Graph
                                    </button></li>
                                    <li class="nav-item"><button class="nav-link rounded-pill px-3" onclick="fetchAttendance(${user.id}, '', '${tabAttend}')" data-bs-toggle="pill" data-bs-target="#${tabAttend}">
                                        <i class="bi bi-calendar-check-fill me-2"></i>Attendance
                                    </button></li>
                                    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#${tabActivity}">
                                        <i class="bi bi-list-task me-2"></i>Activity
                                    </button></li>
                                </ul>

                                <!-- GLOBAL DATE FILTER (For Graph & Activity) -->
                                <div class="d-flex align-items-center gap-2 bg-white p-1 rounded border shadow-sm">
                                    <span class="small text-muted fw-bold ps-2">Filter:</span>
                                    <input type="date" id="from-${user.id}" class="form-control form-control-sm border-0 bg-transparent text-secondary fw-medium" style="max-width: 130px">
                                    <span class="text-muted">-</span>
                                    <input type="date" id="to-${user.id}" class="form-control form-control-sm border-0 bg-transparent text-secondary fw-medium" style="max-width: 130px">
                                    <button class="btn btn-sm btn-primary-custom px-3" 
                                        onclick="applyGlobalFilters(${user.id}, '${chartId}', '${tabActivity}')">
                                        Go
                                    </button>
                                </div>
                            </div>

                            <div class="tab-content">
                                <!-- 1. GRAPH TAB (Horizontal Bar) -->
                                <div class="tab-pane fade show active" id="${tabGraph}">
                                    <div class="bg-white p-4 rounded border shadow-sm">
                                        <h6 class="fw-bold mb-3">Project Duration Distribution</h6>
                                        <div style="height: 300px;">
                                            <canvas id="${chartId}"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. ATTENDANCE TAB (Month Filter) -->
                                <div class="tab-pane fade" id="${tabAttend}">
                                    <div class="bg-white p-4 rounded border shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h6 class="fw-bold mb-0">Attendance Sheet</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="small fw-bold text-muted">Month:</label>
                                                <input type="month" class="form-control form-control-sm" style="width: 150px;" 
                                                    value="${new Date().toISOString().slice(0, 7)}"
                                                    onchange="fetchAttendance(${user.id}, this.value, '${tabAttend}')">
                                            </div>
                                        </div>
                                        <div id="${tabAttend}-content">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Hours</th></tr></thead>
                                                    <tbody>
                                                        <!-- Fake data for UI preview -->
                                                        <tr><td>Nov 28, 2025</td><td><span class="badge bg-success bg-opacity-10 text-success">Present</span></td><td>09:00 AM</td><td>06:00 PM</td><td>9h</td></tr>
                                                        <tr><td>Nov 27, 2025</td><td><span class="badge bg-warning bg-opacity-10 text-warning">Late</span></td><td>09:45 AM</td><td>06:15 PM</td><td>8.5h</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. ALL ACTIVITY TAB (Developer Logs) -->
                                <div class="tab-pane fade" id="${tabActivity}">
                                    <div class="bg-white p-4 rounded border shadow-sm">
                                        <div id="${tabActivity}-content">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Project</th>
                                                            <th>Start Time</th>
                                                            <th>End Time</th>
                                                            <th>Duration</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Fake data for UI preview -->
                                                        <tr>
                                                            <td>Nov 28</td>
                                                            <td><span class="badge bg-light text-dark border">Dashboard API</span></td>
                                                            <td>10:00 AM</td>
                                                            <td>12:00 PM</td>
                                                            <td class="fw-bold text-dark">2h 00m</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Nov 28</td>
                                                            <td><span class="badge bg-light text-dark border">Bug Fixes</span></td>
                                                            <td>01:00 PM</td>
                                                            <td>04:30 PM</td>
                                                            <td class="fw-bold text-dark">3h 30m</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
                container.insertAdjacentHTML("beforeend", html);
            });
        }

        function calculateHours(duration) {
            let totalMin = parseInt(duration || 0);

            if (duration == "" || duration === NaN) {
                return "0 hrs 0 min";
            }

            let hours = Math.floor(totalMin / 60);
            let minutes = totalMin % 60;

            return `${hours} hrs ${minutes} min`;
        }



        // --- 5. ANALYTICS LOADING & FILTERING ---

        // Initial Load when clicking "Analytics"
        function loadAnalytics(userId, chartId, attendTabId, activityTabId) {
            // Check if dates are set, otherwise use defaults or empty
            const fromDate = document.getElementById(`from-${userId}`).value;
            const toDate = document.getElementById(`to-${userId}`).value;

            fetchGraphData(userId, chartId, fromDate, toDate);
            fetchActivityLogs(userId, activityTabId, fromDate, toDate);
        }

        // Applied when clicking "Go" next to date filters
        function applyGlobalFilters(userId, chartId, activityTabId) {
            const fromDate = document.getElementById(`from-${userId}`).value;
            const toDate = document.getElementById(`to-${userId}`).value;

            // 1. Refresh Graph
            fetchGraphData(userId, chartId, fromDate, toDate);

            // 2. Refresh Activity Log
            fetchActivityLogs(userId, activityTabId, fromDate, toDate);
        }

        // --- Graph: Horizontal Bar ---
        const chartInstances = {};

        function fetchGraphData(userId, chartId, fromDate, toDate) {

            fetch("api/SuperAdmin.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    function: "fetch_user_graph",
                    user_id: userId,
                    from_date: fromDate,
                    to_date: toDate
                })
            })
                .then(res => res.json())
                .then(result => {
                    const data = result.status ? result.data : [{
                        project: "Design",
                        duration: 120
                    }, {
                        project: "Dev",
                        duration: 300
                    }];
                    renderUserGraph(chartId, data);
                })
                .catch(err => {
                    // Mock data fallback
                    renderUserGraph(chartId, [{
                        project: "Dashboard",
                        duration: 120
                    }, {
                        project: "API",
                        duration: 240
                    }, {
                        project: "Testing",
                        duration: 90
                    }]);
                });
        }

        function renderUserGraph(chartId, data) {
            const ctx = document.getElementById(chartId).getContext("2d");
            const labels = data.map(e => e.project);
            const values = data.map(e => (e.duration / 60).toFixed(1));

            if (chartInstances[chartId]) chartInstances[chartId].destroy();

            chartInstances[chartId] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hours Worked',
                        data: values,
                        backgroundColor: '#6366f1',
                        borderRadius: 4,
                        barThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: '#f1f5f9'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }


        function fetchAttendance(userId, month, tabID) {
            console.log(`Fetching attendance for ${tabID} User ${userId} `);

            const container = document.getElementById(`${tabID}-content`);
            container.innerHTML = '<p class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span> Loading...</p>';

            fetch("api/SuperAdmin.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    function: "fetch_user_attendance",
                    user_id: userId,
                    month: month
                })
            })
                .then(res => res.text()) // ← IMPORTANT: read as text first
                .then(text => {
                    console.log("RAW RESPONSE:", text); // ← See actual backend output

                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch (e) {
                        container.innerHTML = `<p class="text-danger">Invalid JSON response from server.</p>`;
                        return;
                    }

                    if (result.status) {
                        renderAttendanceTable(tabID, result.data);
                    } else {
                        renderAttendanceTable(tabID, []);
                    }
                })
                .catch(err => {
                    console.error("Attendance Fetch Error:", err);
                    container.innerHTML = `<p class="text-danger text-center">Error loading data.</p>`;
                });
        }

        function renderAttendanceTable(tabID, data) {
            const container = document.getElementById(`${tabID}-content`);
            if (!container) return;

            if (!data || data.length === 0) {
                container.innerHTML = `
            <div class="text-center py-4 text-muted border rounded bg-light">
                <i class="bi bi-calendar-x fs-4 d-block mb-2"></i>
                No attendance records found for this period.
            </div>`;
                return;
            }

            let rows = "";

            data.forEach(row => {
                const formattedDate = formatDateAttendance(row.date);

                rows += `
            <tr>
                <td class="fw-medium">${formattedDate}</td>
                <td><span class="badge bg-info">${row.status || ''}</span></td>
                <td>${row.check_in ?? "-"}</td>
                <td>${row.check_out ?? "-"}</td>
                <td><b>${row.duration}</b></td>
            </tr>
        `;
            });

            container.innerHTML = `
        <div class="table-responsive rounded border">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Worked</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
        }

        function formatDateAttendance(dateStr) {
            return new Date(dateStr).toLocaleDateString("en-US", {
                day: "2-digit",
                month: "short",
                year: "numeric"
            });
        }



        // --- Fetch Activity Logs (Range) ---
        function fetchActivityLogs(userId, tabID, fromDate = "", toDate = "", page = 1) {

            fetch("api/SuperAdmin.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    function: "fetch_user_activity",
                    user_id: userId,
                    from_date: fromDate,
                    to_date: toDate,
                    page: page,
                    limit: 10
                })
            })
                .then(res => res.text())
                .then(text => {
                    console.log("RAW", text);
                    let result = JSON.parse(text);

                    if (result.status) {
                        renderAllActivity(tabID, result.data);
                        renderPagination(tabID, result.total, result.limit, result.page);
                    }
                })
                .catch(err => console.error("Activity Fetch Error:", err));
        }

        //     function renderAllActivity(containerId, data) {
        //         const tbody = document.getElementById(`${containerId}-content`);
        //         const emptyState = document.getElementById(`${containerId}-empty`);

        //         tbody.innerHTML = "";

        //         if (!data || data.length === 0) {
        //             emptyState.style.display = "block";
        //             return;
        //         }

        //         emptyState.style.display = "none";

        //         data.forEach(entry => {
        //             const row = `
        //         <tr>
        //             <td class="fw-bold">${entry.project}</td>
        //             <td class="text-muted">${entry.desc || "-"}</td>
        //             <td>${formatDate(entry.start)}</td>
        //             <td>${formatDuration(entry.duration)}</td>
        //         </tr>
        //     `;
        //             tbody.insertAdjacentHTML("beforeend", row);
        //         });
        //     }


        //     function renderPagination(containerId, total, limit, page) {
        //         const totalPages = Math.ceil(total / limit);
        //         const container = document.getElementById(`${containerId}-pagination`);
        //         container.innerHTML = "";

        //         if (totalPages <= 1) return;

        //         let html = `<ul class="pagination justify-content-center">`;

        //         // Prev
        //         html += `
        //     <li class="page-item ${page <= 1 ? "disabled" : ""}">
        //         <a class="page-link" href="#" onclick="fetchActivityLogs(${containerId.split('-')[2]}, '${containerId}', '', '', ${page - 1})">Prev</a>
        //     </li>
        // `;

        //         // Page Numbers
        //         for (let i = 1; i <= totalPages; i++) {
        //             html += `
        //         <li class="page-item ${i === page ? "active" : ""}">
        //             <a class="page-link" href="#" onclick="fetchActivityLogs(${containerId.split('-')[2]}, '${containerId}', '', '', ${i})">${i}</a>
        //         </li>
        //     `;
        //         }

        //         // Next
        //         html += `
        //     <li class="page-item ${page >= totalPages ? "disabled" : ""}">
        //         <a class="page-link" href="#" onclick="fetchActivityLogs(${containerId.split('-')[2]}, '${containerId}', '', '', ${page + 1})">Next</a>
        //     </li>
        // `;

        //         html += `</ul>`;

        //         container.innerHTML = html;
        //     }

        // 4. Render All Activity Table
        function renderAllActivity(containerId, data) {
            // Target the content div inside the tab
            const container = document.getElementById(`${containerId}-content`);
            if (!container) return;

            if (!data || data.length === 0) {
                container.innerHTML = `
            <div class="text-center py-5 text-muted bg-light rounded border border-dashed my-3">
                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                No activity logs found.
            </div>
            <div id="${containerId}-pagination"></div>
        `;
                return;
            }

            let rows = data.map(entry => `
        <tr>
            <td class="fw-bold text-dark">${entry.project}</td>
            <td class="text-muted text-truncate" style="max-width: 200px;">${entry.desc || "-"}</td>
            <td class="text-nowrap small text-muted">${formatDateHelper(entry.start_time || entry.start)}</td>
            <td class="text-nowrap small text-muted">${formatDateHelper(entry.end_time || entry.end)}</td>
            <td class="fw-bold text-dark bg-light">${formatDurationHelper(entry.duration)}</td>
        </tr>
    `).join('');

            container.innerHTML = `
        <div class="table-responsive rounded border mb-3">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Description</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
        <div id="${containerId}-pagination"></div>
    `;
        }

        // 5. Render Pagination Controls
        function renderPagination(containerId, total, limit, page, userId) {
            const container = document.getElementById(`${containerId}-pagination`);
            if (!container) return;

            const totalPages = Math.ceil(total / limit);
            if (totalPages <= 1) {
                container.innerHTML = "";
                return;
            }

            let html = `<nav><ul class="pagination pagination-sm justify-content-center mb-0">`;

            // Previous Button
            const prevDisabled = page <= 1 ? "disabled" : "";
            html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" onclick="event.preventDefault(); fetchActivityLogs(${userId}, '${containerId}', '', '', ${page - 1})">
                Prev
            </a>
        </li>
    `;

            // Page Numbers (Logic to show range like: 1 ... 4 5 6 ... 10)
            let startPage = Math.max(1, page - 2);
            let endPage = Math.min(totalPages, page + 2);

            if (startPage > 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                const active = i === page ? "active" : "";
                html += `
            <li class="page-item ${active}">
                <a class="page-link" href="#" onclick="event.preventDefault(); fetchActivityLogs(${userId}, '${containerId}', '', '', ${i})">${i}</a>
            </li>
        `;
            }

            if (endPage < totalPages) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            // Next Button
            const nextDisabled = page >= totalPages ? "disabled" : "";
            html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" onclick="event.preventDefault(); fetchActivityLogs(${userId}, '${containerId}', '', '', ${page + 1})">
                Next
            </a>
        </li>
    `;

            html += `</ul></nav>`;
            container.innerHTML = html;
        }

        // --- Helpers ---


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



        function formatDateHelper(dateString) {
            if (!dateString) return "-";
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        function formatDurationHelper(minutes) {
            if (!minutes) return "-";
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            return `${h}h ${m}m`;
        }
    </script>
</body>

</html>