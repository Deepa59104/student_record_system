<?php
/**
 * ============================================================
 * FILE:        dashboard.php
 * MODULE:      Student Profile Management
 * DEVELOPER:   Deepa Thapa | SRS-84
 * PROJECT:     Edu Team – Student Record System
 * LAYER:       Presentation Layer
 * DESCRIPTION: Main dashboard page showing all 5 modules.
 *              Students card links to student_login.php
 * ============================================================
 */

// MIDDLE LAYER: Start session
session_start();

$teacher_name = $_SESSION['teacher_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Edu Team SRS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #1a0a2e;
        }

        /* ── Navbar ── */
        .navbar {
            height: 56px;
            background: #3b1f6e;
            display: flex;
            align-items: center;
            padding: 0 40px;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .nav-brand-text { color: #fff; font-size: 15px; font-weight: 700; }
        .nav-center { color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; position: absolute; left: 50%; transform: translateX(-50%); }
        .nav-right { display: flex; align-items: center; gap: 24px; }
        .nav-right a { color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; text-decoration: none; }
        .nav-right a:hover { color: white; }
        .nav-right a.active { color: white; font-weight: 700; }
        .nav-right a.danger { color: #f87171; }

        /* ── Page ── */
        .page { max-width: 1100px; margin: 0 auto; padding: 60px 32px; }

        /* ── Header ── */
        .header { text-align: center; margin-bottom: 52px; }
        .header-icon { width: 72px; height: 72px; border-radius: 50%; background: #3b1f6e; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 32px; }
        .header-title { font-size: 36px; font-weight: 800; color: #2d1657; letter-spacing: -0.5px; margin-bottom: 8px; }
        .header-sub { font-size: 14px; color: #9b8bb8; }

        /* ── Cards ── */
        .grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
        .grid-bottom { display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .grid-bottom .card { width: calc(33.333% - 10px); }

        .card {
            background: #fff; border: 1.5px solid #e2d9f3;
            border-radius: 18px; padding: 36px 24px;
            text-align: center; text-decoration: none; display: block;
            transition: all .25s ease;
        }
        .card:hover {
            background: #3b1f6e; border-color: #3b1f6e;
            transform: translateY(-6px);
            box-shadow: 0 20px 44px rgba(59,31,110,0.18);
        }
        .card:hover .card-name  { color: #ffffff; }
        .card:hover .card-desc  { color: rgba(255,255,255,0.6); }
        .card:hover .card-arrow { color: #ffffff; }
        .card:hover .card-icon-wrap { background: rgba(255,255,255,0.12); }

        .card-icon-wrap { width: 68px; height: 68px; border-radius: 18px; background: #f3effe; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 32px; transition: background .25s; }
        .card-name  { font-size: 16px; font-weight: 700; color: #3b1f6e; margin-bottom: 6px; transition: color .25s; }
        .card-desc  { font-size: 13px; color: #9b8bb8; margin-bottom: 16px; transition: color .25s; }
        .card-arrow { font-size: 18px; color: #3b1f6e; display: inline-block; transition: all .25s; }
        .card:hover .card-arrow { transform: translateX(5px); }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a class="nav-brand" href="dashboard.php">
        <div class="nav-logo">🎓</div>
        <span class="nav-brand-text">EduTeam</span>
    </a>
    <span class="nav-center">Student Record System</span>
    <div class="nav-right">
        <a href="dashboard.php" class="active">Dashboard</a>
        <?php if (
            isset($_SESSION['teacher_id']) ||
            isset($_SESSION['student_teacher_id']) ||
            isset($_SESSION['student_logged_in'])
        ): ?>
            <a href="../isha/logout.php" class="danger">Logout</a>
        <?php endif; ?>
    </div>
</nav>

<div class="page">
    <div class="header">
        <div class="header-icon">🎓</div>
        <h1 class="header-title">Welcome to EduTeam</h1>
        <p class="header-sub">Select a module below to manage your records</p>
    </div>

    <div class="grid">
        <a href="../sita/teacher_list.php" class="card">
            <div class="card-icon-wrap">👨‍🏫</div>
            <p class="card-name">Teachers</p>
            <p class="card-desc">Manage teacher records</p>
            <span class="card-arrow">→</span>
        </a>
        <a href="student_login.php" class="card">
            <div class="card-icon-wrap">👨‍🎓</div>
            <p class="card-name">Students</p>
            <p class="card-desc">Manage student profiles</p>
            <span class="card-arrow">→</span>
        </a>
        <a href="../isha/course_list.php" class="card">
            <div class="card-icon-wrap">📚</div>
            <p class="card-name">Courses</p>
            <p class="card-desc">Manage course records</p>
            <span class="card-arrow">→</span>
        </a>
    </div>

    <div class="grid-bottom">
        <a href="../binu/grades/grade_list.php" class="card">
            <div class="card-icon-wrap">📝</div>
            <p class="card-name">Grades</p>
            <p class="card-desc">Track student results</p>
            <span class="card-arrow">→</span>
        </a>
        <a href="../satinder/attendance_list.php" class="card">
            <div class="card-icon-wrap">📅</div>
            <p class="card-name">Attendance</p>
            <p class="card-desc">Monitor attendance records</p>
            <span class="card-arrow">→</span>
        </a>
    </div>
</div>

</body>
</html>