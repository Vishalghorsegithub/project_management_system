<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Login | Welcome Back</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --text-color: #333;
            --input-bg: #f4f7f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: #f5f7fa;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-weight: 700;
            display: inline-block;
        }

        h2 {
            color: var(--text-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #e1e8ed;
            background-color: #fff;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
            background-color: #fff;
            border-color: var(--secondary-color);
            outline: none;
        }

        .form-control:hover {
            border-color: #c1cbd5;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 6px;
            color: #333;
            display: block;
        }

        .btn-custom-primary {
            background: var(--secondary-color);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-custom-primary:hover {
            background: #1d65d9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 117, 252, 0.3);
        }

        .btn-custom-primary:active {
            transform: translateY(0);
        }

        .forgot-password {
            font-size: 0.85rem;
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: var(--secondary-color);
        }

        .form-check-input:checked {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(37, 117, 252, 0.25);
        }

        .social-login-text {
            font-size: 0.85rem;
            color: #999;
            position: relative;
            text-align: center;
            margin: 1.5rem 0;
            font-weight: 400;
        }

        .social-login-text::before,
        .social-login-text::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #e1e8ed;
        }

        .social-login-text::before {
            left: 0;
        }

        .social-login-text::after {
            right: 0;
        }

        .btn-google {
            background-color: #fff;
            border: 1px solid #e1e8ed;
            color: #333;
            font-weight: 500;
            padding: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #c1cbd5;
        }

        .signup-link {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .signup-link:hover {
            color: var(--primary-color);
        }

        /* Mobile adjustments */
        @media (max-width: 576px) {
            .login-card {
                padding: 2rem 1.5rem;
                width: 90%;
                margin: 1rem;
            }

            .logo-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="text-center">
            <div class="logo-icon">✦</div>
            <h2 class="fw-bold mb-1">Welcome Back</h2>
            <p class="text-muted mb-4">Sign in to your account</p>
        </div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" placeholder="••••••••" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="showpassword">
                    <label class="form-check-label text-muted" style="font-size: 0.85rem; font-weight: 500;"
                        for="showpassword">
                        View Password
                    </label>
                </div>
                <!-- <a href="#" class="forgot-password">Forgot Password?</a> -->
            </div>

            <button type="submit" class="btn btn-custom-primary">Log In</button>
        </form>
    </div>


    <!-- Custom Message Modal -->
    <div class="modal fade" id="msgModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="msgTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="msgBody"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="msgOkBtn">OK</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        function showMessage(title, message, type = "success", redirectUrl = null) {

            // Set modal title + body
            document.getElementById("msgTitle").textContent = title;
            document.getElementById("msgBody").textContent = message;

            // Change title color based on success/fail
            if (type === "success") {
                document.getElementById("msgTitle").style.color = "green";
            } else {
                document.getElementById("msgTitle").style.color = "red";
            }

            // Show modal
            var modal = new bootstrap.Modal(document.getElementById("msgModal"));
            modal.show();

            // On OK button click → Close + Redirect
            document.getElementById("msgOkBtn").onclick = function() {
                modal.hide();
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            };

            // Auto-close after 2 seconds + redirect
            setTimeout(() => {
                modal.hide();
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }, 2000);
        }







        document.getElementById("loginForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            const res = await fetch("api/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    email,
                    password,
                    function: 'login'
                })
            });

            const result = await res.json();

            if (result.status) {
                alert("Login Successful");
                window.location.href = result.data.redirect || "dashboard.php";
            } else {
                alert(result.message);
            }
        });



        document.getElementById('showpassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
</body>

</html>