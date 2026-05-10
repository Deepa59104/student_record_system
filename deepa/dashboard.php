<?php
// dashboard.php - Main Dashboard
// Developer: Deepa Thapa | SRS-84
// Project: Edu Team - Student Record System

session_start();

// Redirect to login if not logged in
if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../isha/login.php');
    exit();
}

$teacher_name = $_SESSION['teacher_name'] ?? 'Teacher';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edu Team - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0a1e; min-height: 100vh; position: relative; overflow-x: hidden; }
        .bg-orb1 { position: fixed; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(124,58,237,0.25) 0%, transparent 70%); top: -200px; left: -150px; pointer-events: none; }
        .bg-orb2 { position: fixed; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(168,85,247,0.15) 0%, transparent 70%); bottom: -150px; right: -100px; pointer-events: none; }
        .bg-orb3 { position: fixed; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; }
        .navbar { position: sticky; top: 0; z-index: 100; padding: 0 40px; height: 62px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(15,10,30,0.85); backdrop-filter: blur(14px); }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand-logo { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #a855f7); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; color: white; box-shadow: 0 4px 20px rgba(124,58,237,0.5); }
        .brand-text { color: white; font-size: 15px; font-weight: 700; letter-spacing: -0.3px; }
        .nav-links { display: flex; align-items: center; gap: 28px; }
        .nav-links a { color: rgba(255,255,255,0.45); font-size: 13px; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: white; }
        .nav-links a.active { color: #a855f7; }
        .content { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; padding: 60px 32px; }
        .page-header { margin-bottom: 52px; }
        .page-title { font-size: 38px; font-weight: 800; color: white; letter-spacing: -1px; margin-bottom: 12px; line-height: 1.2; }
        .page-title span { color: #a855f7; }
        .page-sub { font-size: 15px; color: rgba(255,255,255,0.35); }
        .modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .module-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 32px 24px; text-align: center; text-decoration: none; display: block; transition: all 0.3s ease; backdrop-filter: blur(10px); }
        .module-card:hover { background: rgba(124,58,237,0.15); border-color: rgba(168,85,247,0.5); transform: translateY(-6px); box-shadow: 0 20px 40px rgba(124,58,237,0.2); }
        .card-icon { width: 68px; height: 68px; border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 30px; transition: transform 0.3s; }
        .module-card:hover .card-icon { transform: scale(1.1); }
        .icon-t { background: rgba(124,58,237,0.2); }
        .icon-s { background: rgba(59,130,246,0.2); }
        .icon-c { background: rgba(16,185,129,0.2); }
        .icon-g { background: rgba(245,158,11,0.2); }
        .icon-a { background: rgba(239,68,68,0.2); }
        .card-name { font-size: 17px; font-weight: 600; color: white; margin-bottom: 8px; }
        .card-desc { font-size: 13px; color: rgba(255,255,255,0.3); margin-bottom: 16px; }
        .card-arrow { font-size: 18px; color: rgba(168,85,247,0.5); transition: all 0.3s; }
        .module-card:hover .card-arrow { transform: translateX(6px); color: #a855f7; }
        .bottom-row { display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .bottom-row .module-card { width: calc(33.333% - 10px); }
    </style>
</head>
<body>
    <div class="bg-orb1"></div>
    <div class="bg-orb2"></div>
    <div class="bg-orb3"></div>
    <nav class="navbar">
        <a class="brand" href="dashboard.php">
            <div class="brand-logo">E</div>
            <span class="brand-text">Edu Team – Student Record System</span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="../isha/logout.php">Logout</a>
        </div>
    </nav>
    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Welcome to <span>Edu Team</span></h1>
            <p class="page-sub">Select a module below to manage your records</p>
        </div>
        <div class="modules-grid">
            <a href="../sita/teacher_list.php" class="module-card">
                <div class="card-icon icon-t">👨‍🏫</div>
                <p class="card-name">Teachers</p>
                <p class="card-desc">Manage teacher records</p>
                <div class="card-arrow">→</div>
            </a>
            <a href="student_list.php" class="module-card">
                <div class="card-icon icon-s">👨‍🎓</div>
                <p class="card-name">Students</p>
                <p class="card-desc">Manage student profiles</p>
                <div class="card-arrow">→</div>
            </a>
            <a href="../isha/course_list.php" class="module-card">
                <div class="card-icon icon-c">📚</div>
                <p class="card-name">Courses</p>
                <p class="card-desc">Manage course records</p>
                <div class="card-arrow">→</div>
            </a>
        </div>
        <div class="bottom-row">
            <a href="../binu/grade_list.php" class="module-card">
                <div class="card-icon icon-g">📝</div>
                <p class="card-name">Grades</p>
                <p class="card-desc">Track student results</p>
                <div class="card-arrow">→</div>
            </a>
            <a href="../satinder/attendance_list.php" class="module-card">
                <div class="card-icon icon-a">📅</div>
                <p class="card-name">Attendance</p>
                <p class="card-desc">Monitor attendance records</p>
                <div class="card-arrow">→</div>
            </a>
        </div>
    </div>
</body>
</html>