<?php
/**
 * ============================================================
 * FILE:        add_student.php
 * MODULE:      Student Profile Management
 * DEVELOPER:   Deepa Thapa | SRS-84
 * PROJECT:     Edu Team – Student Record System
 * LAYER:       Presentation + Middle + Data Layer
 * DESCRIPTION: Form to add a new student to the database.
 *              Validates all inputs before inserting.
 *              Teacher dropdown pulls from Sita's teachers table.
 *              Course stored as VARCHAR name (not ID number).
 * ============================================================
 */

// ── MIDDLE LAYER: Start session + access control ──────────────
// Uses student_teacher_id (not teacher_id) to avoid clash with Isha's session
session_start();
if (!isset($_SESSION['student_teacher_id'])) {
    header('Location: student_login.php'); exit();
}

// ── DATA LAYER: Database connection ──────────────────────────
require_once '../db.php';

$errors = [];

// ── MIDDLE LAYER: Process form on POST ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name     = trim($_POST['full_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $course        = trim($_POST['course'] ?? '');
    $teacher_id    = intval($_POST['teacher_id'] ?? 0);
    $enrolled_date = trim($_POST['enrolled_date'] ?? '');
    $password      = trim($_POST['password'] ?? '');

    // VALIDATE: Required fields
    if (empty($full_name))     $errors[] = 'Full name is required.';
    if (empty($email))         $errors[] = 'Email address is required.';
    if (empty($course))        $errors[] = 'Please select a course.';
    if ($teacher_id === 0)     $errors[] = 'Please select a teacher.';
    if (empty($enrolled_date)) $errors[] = 'Enrolled date is required.';
    if (empty($password))      $errors[] = 'Password is required.';
    if (!empty($password) && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    // VALIDATE: Check duplicate email
    if (empty($errors)) {
        $chk = mysqli_query($conn, "SELECT student_id FROM students WHERE email='" . mysqli_real_escape_string($conn,$email) . "'");
        if (mysqli_num_rows($chk) > 0) $errors[] = 'This email is already registered.';
    }

    // DATA LAYER: Insert into students table
    if (empty($errors)) {
        $hashed = md5($password);
        $sql = "INSERT INTO students (full_name, email, course, teacher_id, enrolled_date, password)
                VALUES ('" . mysqli_real_escape_string($conn,$full_name) . "',
                        '" . mysqli_real_escape_string($conn,$email) . "',
                        '" . mysqli_real_escape_string($conn,$course) . "',
                        $teacher_id,
                        '" . mysqli_real_escape_string($conn,$enrolled_date) . "',
                        '$hashed')";
        if (mysqli_query($conn,$sql)) {
            header('Location: student_list.php?success=Student added successfully!'); exit();
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}

// DATA LAYER: Get teachers from Sita's table
$tr       = mysqli_query($conn, "SELECT teacher_id, CONCAT(first_name,' ',last_name) AS full_name FROM teachers WHERE is_active=1 ORDER BY first_name ASC");
$teachers = $tr ? mysqli_fetch_all($tr, MYSQLI_ASSOC) : [];

$courses = ['Computer Science','Software Engineering','Data Science','Information Technology','Cybersecurity'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student – Edu Team</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f3effe; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { height: 56px; background: #3b1f6e; display: flex; align-items: center; padding: 0 40px; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand-logo { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; }
        .brand-text { color: #fff; font-size: 15px; font-weight: 700; }
        .nav-links { display: flex; gap: 24px; }
        .nav-links a { color: rgba(255,255,255,0.65); font-size: 13px; text-decoration: none; font-weight: 500; }
        .nav-links a:hover { color: white; }
        .nav-links a.danger { color: #fca5a5; }
        .main { flex: 1; padding: 40px 32px 60px; max-width: 720px; margin: 0 auto; width: 100%; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: #9b8bb8; font-size: 13px; text-decoration: none; margin-bottom: 28px; }
        .back-link:hover { color: #3b1f6e; }
        .page-title { font-size: 28px; font-weight: 800; color: #2d1657; margin-bottom: 6px; }
        .page-sub { font-size: 14px; color: #9b8bb8; margin-bottom: 32px; }
        .form-card { background: #fff; border: 1.5px solid #e2d9f3; border-radius: 20px; padding: 32px; box-shadow: 0 4px 24px rgba(59,31,110,0.07); }
        .sec-label { font-size: 11px; font-weight: 700; color: #9b8bb8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #f0eaf8; }
        .error-box { background: #fde8f0; border: 1px solid #f0b8d0; border-radius: 10px; padding: 12px 16px; font-size: 13px; color: #8b1a42; margin-bottom: 24px; }
        .error-box ul { margin: 6px 0 0 18px; line-height: 1.8; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #2d1657; margin-bottom: 8px; }
        .req { color: #ef4444; }
        .form-input, .form-select { width: 100%; height: 46px; padding: 0 14px; border: 1.5px solid #e2d9f3; border-radius: 10px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; color: #2d1657; background: #faf8ff; outline: none; transition: border-color .2s; }
        .form-input:focus, .form-select:focus { border-color: #3b1f6e; box-shadow: 0 0 0 3px rgba(59,31,110,0.08); background: #fff; }
        .form-input::placeholder { color: #c4b8e0; }
        .form-select option { background: #fff; color: #2d1657; }
        .hint { font-size: 11px; color: #9b8bb8; margin-top: 5px; }
        .divider { border: none; border-top: 1px solid #f0eaf8; margin: 24px 0; }
        .form-actions { display: flex; gap: 12px; margin-top: 8px; }
        .btn-add { flex: 1; height: 48px; background: #3b1f6e; color: white; border: none; border-radius: 10px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .btn-add:hover { background: #4e2b8f; }
        .btn-cancel { flex: 1; height: 48px; background: #f3effe; color: #3b1f6e; border: 1.5px solid #e2d9f3; border-radius: 10px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 15px; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; }
        .btn-cancel:hover { background: #e8d9f8; }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">
        <div class="brand-logo">🎓</div>
        <span class="brand-text">EduTeam</span>
    </a>
    <div class="nav-links">
        <a href="student_list.php">Students</a>
        <a href="student_logout.php" class="danger">Logout</a>
    </div>
</nav>
<div class="main">
    <a href="student_list.php" class="back-link">← Back to Students</a>
    <h1 class="page-title">Add New Student</h1>
    <p class="page-sub">Developer: Deepa Thapa | SRS-84</p>
    <div class="form-card">
        <div class="sec-label">Student Details</div>
        <?php if (!empty($errors)): ?>
        <div class="error-box">⚠️ Please fix the following:
            <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <form method="POST" action="add_student.php">
            <div class="form-group">
                <label class="form-label">Full Name <span class="req">*</span></label>
                <input class="form-input" type="text" name="full_name" placeholder="e.g. John Smith" value="<?= htmlspecialchars($_POST['full_name']??'') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address <span class="req">*</span></label>
                <input class="form-input" type="email" name="email" placeholder="student@email.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
                <div class="hint">Must be unique — used for student login</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Course <span class="req">*</span></label>
                    <select class="form-select" name="course" required>
                        <option value="">Select a course</option>
                        <?php foreach($courses as $c): ?>
                        <option value="<?= $c ?>" <?= ($_POST['course']??'')===$c?'selected':'' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Enrolled Date <span class="req">*</span></label>
                    <input class="form-input" type="date" name="enrolled_date" value="<?= htmlspecialchars($_POST['enrolled_date']??'') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Assign Teacher <span class="req">*</span> <span style="font-size:11px;color:#9b8bb8;font-weight:400">— Sita's module</span></label>
                <select class="form-select" name="teacher_id" required>
                    <option value="">Select a teacher</option>
                    <?php foreach($teachers as $t): ?>
                    <option value="<?= $t['teacher_id'] ?>" <?= ($_POST['teacher_id']??'')==$t['teacher_id']?'selected':'' ?>><?= htmlspecialchars($t['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">Teachers pulled from Sita Subedi's Teacher module</div>
            </div>
            <div class="divider"></div>
            <div class="form-group">
                <label class="form-label">Login Password <span class="req">*</span></label>
                <input class="form-input" type="password" name="password" placeholder="Minimum 6 characters" required>
                <div class="hint">Student uses this to log in</div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-add">+ Add Student</button>
                <a href="student_list.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>