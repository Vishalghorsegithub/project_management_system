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
    <title>My Profile</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
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



        .profile-container {
            max-width: 700px;
            margin: 40px auto;
        }

        .card-custom {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-size: 0.85rem;
            color: #4b5563;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Password Toggle Switch */
        .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        /* Password section fade animation */
        #passwordContainer {
            transition: all 0.3s ease-in-out;
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


    <div class="container profile-container">

        <div class="card card-custom p-4 p-md-5">

            <h4 class="fw-bold mb-4 text-center">Update Profile</h4>

            <!-- Back Button -->
            <a href="superadmin_dashboard.php" class="btn btn-outline-primary mb-4 d-flex align-items-center gap-2"
                style="border-radius: 10px; font-weight: 600; text-decoration:none;width: fit-content;">
                <i class="bi bi-arrow-left"></i> Back
            </a>

            <!-- Form -->
            <form id="profileForm">

                <div id="msgBox" class="alert mt-3 d-none text-center small border-0"></div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Full Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control bg-light text-muted" name="email" id="email" readonly>
                    </div>
                </div>

                <hr class="text-secondary opacity-25 my-4">

                <!-- Password Change Section -->
                <div class="mb-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <label class="form-label mb-0"><i class="bi bi-shield-lock me-1"></i> Security</label>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="changePassToggle">
                            <label class="form-check-label small fw-semibold text-muted" for="changePassToggle">
                                Change Password
                            </label>
                        </div>
                    </div>

                    <div id="passwordContainer" class="d-none bg-light p-3 rounded-3 border">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">New Password</label>
                                <input type="password" class="form-control" id="password" placeholder="Min 6 chars">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-center gap-3 mt-5">

                    <button type="submit" id="saveBtn" class="btn btn-primary px-5 fw-medium"
                        style="background-color: #4f46e5; border: none;">
                        <span id="btnText">Save Changes</span>
                        <div id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></div>
                    </button>

                   

                </div>

            </form>



        </div>
    </div>

    <!-- Bootstrap JS -->
     <script src="assets/bootstrap.bundle.min.js"></script>
    <script>




        // --- 1. Get Email From URL ---
        const urlParams = new URLSearchParams(window.location.search);
        const emailParam = urlParams.get("email");

        if (!emailParam) {
            alert("Invalid profile link");
        }

        // --- 2. Fetch User Details from API ---
        document.addEventListener("DOMContentLoaded", () => {

            fetch("api/login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    function: "get_user_profile",
                    email: emailParam
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status) {

                        const user = response.data;

                        full_name.value = user.name;
                        mobile.value = user.mobile;
                        email.value = user.email;

                    } else {
                        alert("User not found");
                    }
                })
                .catch(err => {
                    console.error(err);
                });
        });












        // --- Load existing user data ---
        document.addEventListener("DOMContentLoaded", () => {

            // TODO: Replace with API call
            const userData = {
                full_name: "Admin User",
                mobile: "9876543210",
                email: "admin@example.com"
            };

            full_name.value = userData.full_name;
            mobile.value = userData.mobile;
            email.value = userData.email;
        });

        // Toggle password fields
        changePassToggle.addEventListener("change", function () {
            if (this.checked) {
                passwordContainer.classList.remove("d-none");
            } else {
                passwordContainer.classList.add("d-none");
                password.value = "";
                confirm_password.value = "";
            }
        });

        // Submit form
        profileForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const msgBox = document.getElementById("msgBox");
            const btn = document.getElementById("saveBtn");
            const spinner = document.getElementById("btnSpinner");

            let passwordToSend = "";

            if (changePassToggle.checked) {

                if (!password.value || !confirm_password.value) {
                    showError("Please fill in both password fields.");
                    return;
                }
                if (password.value !== confirm_password.value) {
                    showError("Passwords do not match.");
                    return;
                }
                if (password.value.length < 6) {
                    showError("Password must be at least 6 characters.");
                    return;
                }

                passwordToSend = password.value;
            }

            const payload = {
                function: "update_profile",
                full_name: full_name.value,
                mobile: mobile.value,
                email: email.value,
                password: passwordToSend
            };

            // Loading UI
            btn.disabled = true;
            spinner.classList.remove("d-none");
            msgBox.classList.add("d-none");

            fetch("api/login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status) {
                        msgBox.className = "alert alert-success bg-success-subtle text-success mt-3 border-0";
                        msgBox.innerText = "Profile updated successfully!";
                        msgBox.classList.remove("d-none");

                        // Reset password toggle
                        if (changePassToggle.checked) {
                            changePassToggle.checked = false;
                            changePassToggle.dispatchEvent(new Event('change'));
                        }
                    } else {
                        showError(response.message || "Update failed.");
                    }
                })
                .catch(() => showError("Server connection error."))
                .finally(() => {
                    btn.disabled = false;
                    spinner.classList.add("d-none");
                });
        });

        function showError(msg) {
            msgBox.className = "alert alert-danger bg-danger-subtle text-danger mt-3 border-0";
            msgBox.innerText = msg;
            msgBox.classList.remove("d-none");
        }

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