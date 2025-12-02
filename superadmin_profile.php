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
    <title>Update Company Profile</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <!-- Bootstrap -->
   

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
            background: var(--bg-light);
            font-family: "Inter", sans-serif;
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






        .profile-card {
            max-width: 650px;
            margin: 50px auto;
            background: var(--card-bg);
            border-radius: 14px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        h4 {
            color: var(--primary);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 10px 0;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background: #4f46e5;
        }

        #resultBox {
            border-radius: 10px;
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







    <div class="profile-card">

        <h4 class="mb-3 fw-bold">Update Company Profile</h4>
        <hr>

        <a href="superadmin_dashboard.php" class="btn btn-outline-primary mb-3 d-flex align-items-center gap-2" style="
       border-radius: 10px; 
       font-weight: 600; 
       width: fit-content;
       padding: 10px 16px;
       transition: 0.2s ease;
       text-decoration: none;
   ">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

        <form id="companyForm">
            <div class="alert mt-3 d-none" id="resultBox"></div>

            <!-- Company Name -->
            <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" name="company_name" id="company_name"
                    placeholder="Enter company name">
            </div>

            <!-- Latitude & Longitude -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control" name="latitude" id="latitude" placeholder="Enter latitude">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control" name="longitude" id="longitude"
                        placeholder="Enter longitude">
                </div>
            </div>

            <!-- Opening & Closing Time -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Time</label>
                    <input type="time" class="form-control" name="open_time" id="open_time">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Closing Time</label>
                    <input type="time" class="form-control" name="close_time" id="close_time">
                </div>
            </div>

            <!-- Duration (auto) -->
            <div class="mb-3">
                <label class="form-label">Working Duration</label>
                <input type="text" class="form-control bg-light" name="duration" id="duration" readonly>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Update Profile</button>
        </form>


    </div>

    <!-- Bootstrap JS -->
      <script src="assets/bootstrap.bundle.min.js"></script>
    <script>

        // ------------------------------
        // 1) FETCH COMPANY PROFILE ON PAGE LOAD
        // ------------------------------
        document.addEventListener("DOMContentLoaded", loadCompanyProfile);

        function loadCompanyProfile() {

            fetch("api/login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ function: "get_company_profile" })
            })
                .then(res => res.json())
                .then(response => {

                    if (!response.status) {
                        console.error("Failed to load company profile");
                        return;
                    }

                    const info = response.data;

                    // Fill Inputs
                    document.getElementById("company_name").value = info.name || "";
                    document.getElementById("latitude").value = info.lat || "";
                    document.getElementById("longitude").value = info.long || "";
                    document.getElementById("open_time").value = info.open_time || "";
                    document.getElementById("close_time").value = info.close_time || "";

                    // Auto calculate duration
                    calculateDuration();
                })
                .catch(err => console.error("Fetch Error:", err));
        }


        // ------------------------------
        // 2) VALIDATE LAT / LONG
        // ------------------------------
        function validateLatLong() {
            const latitude = document.getElementById("latitude");
            const longitude = document.getElementById("longitude");

            const lat = parseFloat(latitude.value);
            const lng = parseFloat(longitude.value);

            const box = document.getElementById("resultBox");
            box.classList.add("d-none");

            if (latitude.value.trim() === "" || longitude.value.trim() === "") {
                showError("Latitude and Longitude cannot be empty.");
                return false;
            }

            if (isNaN(lat) || isNaN(lng)) {
                showError("Latitude and Longitude must be valid numbers.");
                return false;
            }

            if (lat < -90 || lat > 90) {
                showError("Latitude must be between -90 and +90.");
                return false;
            }

            if (lng < -180 || lng > 180) {
                showError("Longitude must be between -180 and +180.");
                return false;
            }

            return true;
        }




        // ------------------------------
        // 3) SHOW ERROR MESSAGE
        // ------------------------------
        function showError(msg) {
            const box = document.getElementById("resultBox");
            box.className = "alert alert-danger mt-3";
            box.innerText = msg;
            box.classList.remove("d-none");
        }


        // ------------------------------
        // 4) AUTO CALCULATE DURATION
        // ------------------------------
        function calculateDuration() {
            let start = document.getElementById("open_time").value;
            let end = document.getElementById("close_time").value;

            if (!start || !end) {
                document.getElementById("duration").value = "";
                return;
            }

            const s = new Date(`2000-01-01 ${start}`);
            const e = new Date(`2000-01-01 ${end}`);

            let diff = (e - s) / (1000 * 60 * 60);
            if (diff < 0) diff += 24;

            document.getElementById("duration").value = diff.toFixed(2) + " Hrs";
        }

        document.getElementById("open_time").addEventListener("change", calculateDuration);
        document.getElementById("close_time").addEventListener("change", calculateDuration);



        // ------------------------------
        // 5) SUBMIT FORM (UPDATE PROFILE)
        // ------------------------------
        document.getElementById("companyForm").addEventListener("submit", function (e) {
            e.preventDefault();

            if (!validateLatLong()) return;

            const payload = {
                function: "update_company_profile",
                company_name: company_name.value,
                latitude: latitude.value,
                longitude: longitude.value,
                open_time: open_time.value,
                close_time: close_time.value
            };

            fetch("api/SuperAdmin.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(response => {
                    const box = document.getElementById("resultBox");

                    if (response.status) {
                        box.className = "alert alert-success mt-3";
                        box.innerText = "Profile Updated Successfully!";
                    } else {
                        box.className = "alert alert-danger mt-3";
                        box.innerText = response.message || "Update failed";
                    }

                    box.classList.remove("d-none");
                })
                .catch(err => console.error(err));
        });


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

    </script>



</body>

</html>