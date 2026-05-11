<?php
// ============================================================
// FILE: grade_login.php
// MODULE: Grade & Result Tracking
// PROJECT: EduTeam - Student Record System
// DEVELOPER: Binu Karki
// LAYER: Presentation Layer
// DESCRIPTION: Login page for Grade module
//              Uses Grade class (middle layer) for authentication
// ============================================================

// MIDDLE LAYER: Start session to store login info
session_start();

// If already logged in, redirect to correct dashboard
if (isset($_SESSION['grade_user'])) {
    if ($_SESSION['grade_role'] === 'teacher') {
        header("Location: teacher_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

// DATA LAYER: Include shared database connection
require_once '../../db.php';

// MIDDLE LAYER: Include Grade class
require_once 'Grade.php';

// Create Grade object - passing $conn to constructor
$gradeObj = new Grade($conn);

$error = '';

// ============================================================
// PRESENTATION LAYER: Handle Login Form Submission
// Calls Grade class login() method to verify credentials
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate fields are not empty
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {

        // MIDDLE LAYER: Call login() method from Grade class
        $user = $gradeObj->login($username, $password);

        if ($user) {
            // Store user info in session
            $_SESSION['grade_user']       = $user['username'];
            $_SESSION['grade_role']       = $user['role'];
            $_SESSION['grade_user_id']    = $user['user_id'];
            $_SESSION['grade_student_id'] = $user['student_id'];

            // Redirect based on role
            if ($user['role'] === 'teacher') {
                header("Location: teacher_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Login | EduTeam</title>

    <!-- PRESENTATION LAYER: Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- PRESENTATION LAYER: Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ============================================================
           PRESENTATION LAYER: CSS Styles
           EduTeam Purple Design Theme
           Primary: #4a235a | Hover: #6b3580 | Background: #f0f0f5
        ============================================================ */

        :root {
            --primary:    #4a235a;
            --hover:      #6b3580;
            --bg:         #f0f0f5;
            --white:      #ffffff;
            --error:      #c0392b;
            --text:       #2c2c2c;
            --light-text: #7a7a9d;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ---- NAVBAR ---- */
        .navbar {
            background: var(--primary);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(74,35,90,0.3);
        }

        .navbar-brand {
            color: white;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
        }

        /* ---- MAIN CENTER LAYOUT ---- */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        /* ---- LOGIN HEADER ---- */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-icon {
            width: 75px;
            height: 75px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 8px 25px rgba(74,35,90,0.3);
        }

        .login-icon i { color: white; font-size: 2rem; }

        .login-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .login-header p {
            color: var(--light-text);
            font-size: 0.95rem;
        }

        /* ---- LOGIN CARD ---- */
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(74,35,90,0.12);
        }

        /* ---- ROLE TABS ---- */
        .role-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            background: var(--bg);
            padding: 5px;
            border-radius: 10px;
        }

        .role-tab {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            background: transparent;
            color: var(--light-text);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .role-tab.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(74,35,90,0.3);
        }

        .role-tab:hover:not(.active) {
            background: rgba(74,35,90,0.1);
            color: var(--primary);
        }

        /* ---- FORM ELEMENTS ---- */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
        }

        .input-wrapper { position: relative; }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 2px solid #e8e8f0;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--text);
            transition: border-color 0.3s;
            outline: none;
            background: #fafafa;
        }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
        }

        /* ---- ERROR MESSAGE ---- */
        .error-msg {
            background: #fdecea;
            border-left: 4px solid var(--error);
            color: var(--error);
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.88rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ---- LOGIN BUTTON ---- */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 5px;
        }

        .btn-login:hover {
            background: var(--hover);
            box-shadow: 0 6px 20px rgba(74,35,90,0.35);
            transform: translateY(-1px);
        }

        /* ---- HINT BOX ---- */
        .hint-box {
            margin-top: 22px;
            background: #f5f0f8;
            border-radius: 10px;
            padding: 14px 18px;
        }

        .hint-box p {
            font-size: 0.82rem;
            color: var(--light-text);
            margin-bottom: 6px;
            font-weight: 500;
        }

        .hint-box ul {
            list-style: none;
            font-size: 0.82rem;
            color: var(--primary);
        }

        .hint-box ul li { padding: 2px 0; }

        /* ---- FOOTER ---- */
        footer {
            text-align: center;
            padding: 15px;
            color: var(--light-text);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<!-- PRESENTATION LAYER: Navbar -->
<nav class="navbar">
    <a href="#" class="navbar-brand">
        <i class="fas fa-graduation-cap"></i> EduTeam
    </a>
    <span class="navbar-subtitle">Student Record System</span>
</nav>

<!-- PRESENTATION LAYER: Login Form -->
<div class="main">
    <div class="login-wrapper">

        <!-- Login Header -->
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h1>Grade Portal</h1>
            <p>Sign in to access your grade dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="login-card">

            <!-- Role Tabs (visual only) -->
            <div class="role-tabs">
                <button class="role-tab active" id="teacherTab" onclick="setRole('teacher')">
                    <i class="fas fa-chalkboard-teacher"></i> Teacher
                </button>
                <button class="role-tab" id="studentTab" onclick="setRole('student')">
                    <i class="fas fa-user-graduate"></i> Student
                </button>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control"
                               placeholder="Enter your username"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control"
                               placeholder="Enter your password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <!-- Test credentials hint -->
            <div class="hint-box">
                <p><i class="fas fa-info-circle"></i> Test Credentials:</p>
                <ul>
                    <li><i class="fas fa-circle" style="font-size:0.5rem;"></i> Teacher: <strong>teacher1</strong> / teacher123</li>
                    <li><i class="fas fa-circle" style="font-size:0.5rem;"></i> Student: <strong>student101</strong> / student123</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<footer>
    &copy; 2026 EduTeam | Grade &amp; Result Tracking Module | Developed by Binu Karki
</footer>

<!-- PRESENTATION LAYER: JavaScript for role tab switching -->
<script>
    // Switch active tab between Teacher and Student (visual only)
    function setRole(role) {
        document.getElementById('teacherTab').classList.remove('active');
        document.getElementById('studentTab').classList.remove('active');
        if (role === 'teacher') {
            document.getElementById('teacherTab').classList.add('active');
        } else {
            document.getElementById('studentTab').classList.add('active');
        }
    }
</script>

</body>
</html>
