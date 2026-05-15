<?php
/**
 * ============================================================
 * FILE:        student_login.php
 * MODULE:      Student Profile Management
 * DEVELOPER:   Deepa Thapa | SRS-84
 * PROJECT:     Edu Team – Student Record System
 * LAYER:       Presentation Layer + Middle Layer + Data Layer
 * DESCRIPTION: Login page with Teacher and Student tabs.
 *              Teacher: username + password → teachers table
 *              Student: email + password → students table
 *
 * ── ETHICAL CONSIDERATIONS ───────────────────────────────────
 * 1. DATA PRIVACY: Student personal data is protected by
 *    session-based authentication. Only authenticated users
 *    can access student records.
 *
 * 2. ROLE-BASED ACCESS: Teachers have full access (add/edit/
 *    delete). Students can only view their own records.
 *    This prevents unauthorised data manipulation.
 *
 * 3. PASSWORD SECURITY: Passwords are stored as MD5 hashes —
 *    never as plain text. This protects user credentials
 *    even if the database is compromised.
 *
 * 4. INPUT VALIDATION: All inputs are validated before
 *    processing to prevent malicious data entry and
 *    SQL injection attacks.
 *
 * 5. SESSION MANAGEMENT: Separate session variables for
 *    teacher (student_teacher_id) and student
 *    (student_logged_in) ensure secure access control.
 * ============================================================
 */

// ── MIDDLE LAYER: Start session ───────────────────────────────
session_start();

// ── ETHICS: Access control — skip login if already authenticated
if (isset($_SESSION['student_teacher_id']) || isset($_SESSION['student_logged_in'])) {
    header('Location: student_list.php');
    exit();
}

// ── DATA LAYER: Database connection ──────────────────────────
require_once '../db.php';

// ── MIDDLE LAYER: Initialise variables ───────────────────────
$error = '';
$tab   = $_POST['tab'] ?? 'teacher';

// ── MIDDLE LAYER: Handle form submission ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tab      = $_POST['tab'] ?? 'teacher';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ── ETHICS: Input Validation ──────────────────────────────
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';

    } else {

        // ── TEACHER LOGIN ─────────────────────────────────────
        if ($tab === 'teacher') {

            // ── ETHICS: Password Security ─────────────────────
            $hashed = md5($password);

            // ── DATA LAYER: Prepared statement — prevents SQL injection
            $stmt = mysqli_prepare($conn,
                "SELECT * FROM teachers WHERE username = ? AND password = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed);
            mysqli_stmt_execute($stmt);
            $result  = mysqli_stmt_get_result($stmt);
            $teacher = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($teacher) {
                // ── ETHICS: Session Management ────────────────
                $_SESSION['student_teacher_id']   = $teacher['teacher_id'];
                $_SESSION['student_teacher_name'] = $teacher['first_name'] . ' ' . $teacher['last_name'];
                header('Location: student_list.php');
                exit();
            } else {
                // ── ETHICS: Generic error — prevents user enumeration
                $error = 'Invalid username or password.';
            }

        // ── STUDENT LOGIN ─────────────────────────────────────
        } else {

            // ── ETHICS: Password Security ─────────────────────
            $hashed = md5($password);

            // ── DATA LAYER: Prepared statement — prevents SQL injection
            $stmt = mysqli_prepare($conn,
                "SELECT * FROM students WHERE email = ? AND password = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed);
            mysqli_stmt_execute($stmt);
            $result  = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($student) {
                // ── ETHICS: Data Privacy ──────────────────────
                $_SESSION['student_logged_in'] = true;
                $_SESSION['student_id']        = $student['student_id'];
                $_SESSION['student_name']      = $student['full_name'];
                header('Location: student_list.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Edu Team SRS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── PRESENTATION LAYER: Styles ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f3effe;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            height: 56px; background: #3b1f6e;
            display: flex; align-items: center;
            padding: 0 40px; justify-content: space-between;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .nav-brand-text { color: #fff; font-size: 15px; font-weight: 700; }
        .nav-right { color: rgba(255,255,255,0.65); font-size: 13px; }

        /* Main */
        .main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 24px; }
        .wrapper { width: 100%; max-width: 460px; text-align: center; }

        /* Icon + title */
        .page-icon { width: 72px; height: 72px; border-radius: 50%; background: #3b1f6e; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 32px; }
        .page-title { font-size: 28px; font-weight: 800; color: #2d1657; margin-bottom: 6px; }
        .page-sub   { font-size: 14px; color: #9b8bb8; margin-bottom: 32px; }

        /* Card */
        .card { background: #fff; border: 1.5px solid #e2d9f3; border-radius: 20px; padding: 32px; text-align: left; box-shadow: 0 4px 24px rgba(59,31,110,0.07); }

        /* Tabs */
        .tabs { display: flex; background: #f3effe; border-radius: 12px; padding: 4px; margin-bottom: 28px; gap: 4px; }
        .tab { flex: 1; height: 42px; border: none; border-radius: 9px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; transition: all .2s; background: transparent; color: #9b8bb8; }
        .tab.active { background: #3b1f6e; color: #fff; box-shadow: 0 4px 14px rgba(59,31,110,0.25); }
        .tab:not(.active):hover { background: #e8d9f8; color: #3b1f6e; }

        /* Error */
        .error-box { background: #fde8f0; border: 1px solid #f0b8d0; border-radius: 10px; padding: 12px 16px; font-size: 13px; color: #8b1a42; margin-bottom: 20px; }

        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #2d1657; margin-bottom: 8px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; color: #9b8bb8; pointer-events: none; }
        .form-input { width: 100%; height: 46px; padding: 0 14px 0 42px; border: 1.5px solid #e2d9f3; border-radius: 10px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; color: #2d1657; background: #faf8ff; outline: none; transition: border-color .2s; }
        .form-input::placeholder { color: #c4b8e0; }
        .form-input:focus { border-color: #3b1f6e; box-shadow: 0 0 0 3px rgba(59,31,110,0.1); background: #fff; }

        /* Button */
        .btn-signin { width: 100%; height: 48px; background: #3b1f6e; color: #fff; border: none; border-radius: 10px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 4px; transition: background .2s; }
        .btn-signin:hover { background: #4e2b8f; }

        /* Back link */
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 13px; color: #9b8bb8; text-decoration: none; }
        .back-link:hover { color: #3b1f6e; }

        /* Footer */
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #c4b8e0; }
    </style>
</head>
<body>

<!-- PRESENTATION LAYER: Navbar -->
<nav class="navbar">
    <a class="nav-brand" href="dashboard.php">
        <div class="nav-logo">🎓</div>
        <span class="nav-brand-text">EduTeam</span>
    </a>
    <span class="nav-right">Student Record System</span>
</nav>

<!-- PRESENTATION LAYER: Login form -->
<div class="main">
    <div class="wrapper">

        <div class="page-icon">📋</div>
        <h1 class="page-title">Student Portal</h1>
        <p class="page-sub">Sign in to manage student records</p>

        <div class="card">

            <!-- PRESENTATION LAYER: Error message -->
            <?php if ($error): ?>
            <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="student_login.php">
                <input type="hidden" name="tab" id="tab-value" value="<?= htmlspecialchars($tab) ?>">

                <!-- PRESENTATION LAYER: Teacher | Student tabs -->
                <!-- ETHICS: Two roles ensure appropriate access levels -->
                <div class="tabs">
                    <button type="button"
                        class="tab <?= $tab==='teacher'?'active':'' ?>"
                        onclick="switchTab('teacher',event)">
                        👨‍🏫 Teacher
                    </button>
                    <button type="button"
                        class="tab <?= $tab==='student'?'active':'' ?>"
                        onclick="switchTab('student',event)">
                        👨‍🎓 Student
                    </button>
                </div>

                <!-- PRESENTATION LAYER: Username/Email field -->
                <div class="form-group">
                    <label class="form-label" id="lbl-username">
                        <?= $tab==='teacher' ? 'Username' : 'Email Address' ?>
                    </label>
                    <div class="input-wrap">
                        <span class="input-icon" id="ico-username">
                            <?= $tab==='teacher' ? '👤' : '📧' ?>
                        </span>
                        <input class="form-input" type="text"
                            name="username" id="fld-username"
                            placeholder="<?= $tab==='teacher' ? 'Enter your username' : 'Enter your email' ?>"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>

                <!-- PRESENTATION LAYER: Password field -->
                <!-- ETHICS: Password field masked for security -->
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input class="form-input" type="password"
                            name="password" placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" class="btn-signin" id="btn-signin">
                    <?= $tab==='teacher' ? '➔ Sign In as Teacher' : '➔ Sign In as Student' ?>
                </button>

            </form>

        </div>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</div>

<div class="footer">
    © <?= date('Y') ?> EduTeam | Student Profile Management | Developed by Deepa Thapa | SRS-84
</div>

<!-- PRESENTATION LAYER: JavaScript for tab switching -->
<script>
    /**
     * switchTab() — switches between Teacher and Student tabs
     * ETHICS: Ensures correct credentials are used for each role
     */
    function switchTab(tab, event) {
        document.getElementById('tab-value').value = tab;
        document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
        event.currentTarget.classList.add('active');
        if (tab === 'teacher') {
            document.getElementById('lbl-username').textContent = 'Username';
            document.getElementById('ico-username').textContent = '👤';
            document.getElementById('fld-username').placeholder = 'Enter your username';
            document.getElementById('btn-signin').textContent   = '➔ Sign In as Teacher';
        } else {
            document.getElementById('lbl-username').textContent = 'Email Address';
            document.getElementById('ico-username').textContent = '📧';
            document.getElementById('fld-username').placeholder = 'Enter your email';
            document.getElementById('btn-signin').textContent   = '➔ Sign In as Student';
        }
        document.getElementById('fld-username').value = '';
    }
</script>

</body>
</html>