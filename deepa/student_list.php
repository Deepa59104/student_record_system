<?php
/**
 * ============================================================
 * FILE:        student_list.php
 * MODULE:      Student Profile Management
 * DEVELOPER:   Deepa Thapa | SRS-84
 * PROJECT:     Edu Team – Student Record System
 * LAYER:       Presentation + Middle + Data Layer
 * DESCRIPTION: Teacher → sees all students, full access
 *              Student → sees ONLY their own record + grades
 * ============================================================
 */

// MIDDLE LAYER: Start session
session_start();

// MIDDLE LAYER: Access control
if (!isset($_SESSION['student_teacher_id']) && !isset($_SESSION['student_logged_in'])) {
    header('Location: student_login.php');
    exit();
}

// MIDDLE LAYER: Who is logged in?
$is_teacher = isset($_SESSION['student_teacher_id']);
$is_student = isset($_SESSION['student_logged_in']);

// MIDDLE LAYER: Get user name
$user_name = $_SESSION['student_teacher_name'] ?? $_SESSION['student_name'] ?? 'User';
$initial   = strtoupper(substr($user_name, 0, 1));

// DATA LAYER: Database connection
require_once '../db.php';

// MIDDLE LAYER: Search and filter (teacher only)
$search        = trim($_POST['search'] ?? $_GET['search'] ?? '');
$course_filter = trim($_POST['course_filter'] ?? $_GET['course_filter'] ?? '');

// DATA LAYER: Build WHERE clause
$where = "WHERE 1=1";

// MIDDLE LAYER: If student — only show their own record
if ($is_student) {
    $where .= " AND s.student_id = " . (int)$_SESSION['student_id'];
}

// Teacher search/filter
if ($is_teacher) {
    if (!empty($search)) {
        $where .= " AND s.full_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
    }
    if (!empty($course_filter)) {
        $where .= " AND s.course = '" . mysqli_real_escape_string($conn, $course_filter) . "'";
    }
}

// DATA LAYER: Fetch students with teacher name JOIN
$sql = "SELECT s.student_id, s.full_name, s.email,
               s.course, s.teacher_id, s.enrolled_date,
               CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
        FROM   students s
        LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
        $where
        ORDER BY s.student_id ASC";

$result   = mysqli_query($conn, $sql);
$students = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

// DATA LAYER: Total count (teacher sees all, student sees 1)
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM students");
$total        = mysqli_fetch_assoc($total_result)['cnt'];
$showing      = count($students);

// MIDDLE LAYER: Course list for filter dropdown (teacher only)
$course_result = mysqli_query($conn, "SELECT DISTINCT course FROM students ORDER BY course ASC");
$course_list   = $course_result ? mysqli_fetch_all($course_result, MYSQLI_ASSOC) : [];

// PRESENTATION LAYER: Avatar colours
$avatar_colors = ['#7c3aed','#3b82f6','#10b981','#f59e0b','#ef4444','#ec4899'];

// Get student grades if student logged in
$student_grades = [];
if ($is_student && !empty($students)) {
    $sid = (int)$_SESSION['student_id'];
    $gr  = mysqli_query($conn, "SELECT * FROM grades WHERE student_id = $sid ORDER BY subject ASC");
    $student_grades = $gr ? mysqli_fetch_all($gr, MYSQLI_ASSOC) : [];
}

function gradeColor($score) {
    if ($score >= 85) return ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#6ee7b7'];
    if ($score >= 70) return ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd'];
    if ($score >= 55) return ['bg'=>'#fef3c7','color'=>'#92400e','border'=>'#fcd34d'];
    return ['bg'=>'#fff1f2','color'=>'#9f1239','border'=>'#fda4af'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students – Edu Team SRS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f3effe; min-height: 100vh; }

        /* ── Navbar ── */
        .navbar { position: sticky; top: 0; z-index: 100; padding: 0 40px; height: 62px; display: flex; align-items: center; justify-content: space-between; background: #3b1f6e; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand-logo { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .brand-text { color: #fff; font-size: 15px; font-weight: 700; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: rgba(255,255,255,0.65); font-size: 13px; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: #fff; }
        .nav-links a.danger { color: #f87171; }
        .role-badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .role-teacher { background: rgba(255,255,255,0.15); color: #fff; }
        .role-student { background: rgba(16,185,129,0.3); color: #6ee7b7; }

        /* ── Content ── */
        .content { max-width: 1200px; margin: 0 auto; padding: 36px 32px 60px; }

        /* ── Notices ── */
        .success-msg { background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 10px; padding: 12px 18px; font-size: 13px; color: #065f46; margin-bottom: 18px; }

        /* ── Student own profile card ── */
        .own-profile {
            background: #fff; border: 1.5px solid #3b1f6e;
            border-radius: 16px; padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(59,31,110,0.12);
        }
        .own-profile-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
        .own-avatar { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; color: #fff; flex-shrink: 0; }
        .own-name  { font-size: 22px; font-weight: 800; color: #2d1657; margin-bottom: 4px; }
        .own-sub   { font-size: 13px; color: #9b8bb8; }

        .own-details { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .detail-box { background: #f3effe; border-radius: 10px; padding: 14px 16px; }
        .detail-lbl { font-size: 11px; color: #9b8bb8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .detail-val { font-size: 14px; font-weight: 700; color: #2d1657; }

        /* ── Grades section for student ── */
        .grades-section { margin-top: 8px; }
        .grades-title {
            font-size: 14px; font-weight: 700; color: #2d1657;
            margin-bottom: 14px; padding-bottom: 10px;
            border-bottom: 1px solid #f0eaf8;
            display: flex; align-items: center; gap: 8px;
        }
        .fk-tag { font-size: 11px; color: #9b8bb8; font-weight: 400; }

        .grades-table-wrap { overflow-x: auto; }
        .grades-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .grades-table thead tr { background: #faf8ff; }
        .grades-table th { text-align: left; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #9b8bb8; text-transform: uppercase; letter-spacing: .7px; border-bottom: 1px solid #f0eaf8; }
        .grades-table td { padding: 12px 14px; border-top: 1px solid #f7f3ff; color: #4a3a6b; vertical-align: middle; }
        .grades-table tbody tr:hover td { background: #faf8ff; }

        .grade-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 1px solid; }
        .score-wrap { display: flex; align-items: center; gap: 8px; }
        .score-bar-bg { flex: 1; height: 6px; background: #f0eaf8; border-radius: 3px; max-width: 80px; }
        .score-bar { height: 6px; border-radius: 3px; }
        .no-grades { text-align: center; padding: 24px; color: #c4b8e0; font-size: 13px; }

        /* ── Welcome banner (teacher) ── */
        .banner { background: #fff; border: 1.5px solid #e2d9f3; border-radius: 16px; padding: 20px 26px; display: flex; align-items: center; gap: 16px; margin-bottom: 24px; box-shadow: 0 2px 12px rgba(59,31,110,0.06); }
        .banner-av { width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0; background: #3b1f6e; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; color: #fff; }
        .banner-title { font-size: 17px; font-weight: 700; color: #2d1657; margin-bottom: 3px; }
        .banner-title span { color: #7c3aed; }
        .banner-sub { font-size: 12px; color: #9b8bb8; }

        /* ── Page header ── */
        .page-hdr { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .page-title { font-size: 26px; font-weight: 800; color: #2d1657; margin-bottom: 4px; }
        .page-sub   { font-size: 13px; color: #9b8bb8; }
        .add-btn { display: inline-flex; align-items: center; gap: 6px; background: #3b1f6e; color: white; padding: 11px 22px; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 18px rgba(59,31,110,0.25); white-space: nowrap; transition: background 0.2s; }
        .add-btn:hover { background: #4e2b8f; }

        /* ── Stats ── */
        .stats { display: flex; gap: 14px; margin-bottom: 22px; }
        .stat { flex: 1; padding: 16px 20px; background: #fff; border: 1.5px solid #e2d9f3; border-radius: 14px; box-shadow: 0 2px 8px rgba(59,31,110,0.05); }
        .stat-lbl { font-size: 12px; color: #9b8bb8; margin-bottom: 4px; }
        .stat-val { font-size: 22px; font-weight: 800; color: #3b1f6e; }

        /* ── Search ── */
        .search-row { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; align-items: center; }
        .search-wrap { flex: 1; min-width: 200px; position: relative; }
        .s-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #9b8bb8; }
        .s-input { width: 100%; padding: 11px 14px 11px 38px; background: #fff; border: 1.5px solid #e2d9f3; border-radius: 12px; font-size: 13px; color: #2d1657; font-family: inherit; outline: none; transition: border-color 0.2s; }
        .s-input:focus { border-color: #3b1f6e; }
        .s-input::placeholder { color: #c4b8e0; }
        .f-select { padding: 11px 32px 11px 14px; min-width: 180px; background: #fff; border: 1.5px solid #e2d9f3; border-radius: 12px; font-size: 13px; color: #2d1657; font-family: inherit; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239b8bb8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
        .btn-search { padding: 11px 22px; background: #3b1f6e; color: white; border: none; border-radius: 12px; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; transition: background 0.2s; }
        .btn-search:hover { background: #4e2b8f; }
        .btn-clear { padding: 11px 18px; background: #fff; border: 1.5px solid #e2d9f3; color: #9b8bb8; border-radius: 12px; font-size: 13px; text-decoration: none; font-weight: 500; }

        /* ── Teacher table ── */
        .tbl-card { background: #fff; border: 1.5px solid #e2d9f3; border-radius: 18px; overflow: hidden; box-shadow: 0 2px 12px rgba(59,31,110,0.06); }
        .tbl-top { display: flex; align-items: center; justify-content: space-between; padding: 16px 22px; border-bottom: 1px solid #f0eaf8; }
        .tbl-top h3 { font-size: 14px; font-weight: 700; color: #2d1657; }
        .tbl-top span { font-size: 12px; color: #9b8bb8; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: #faf8ff; }
        th { text-align: left; padding: 11px 18px; font-size: 11px; font-weight: 700; color: #9b8bb8; text-transform: uppercase; letter-spacing: .7px; border-bottom: 1px solid #f0eaf8; }
        td { padding: 12px 18px; border-top: 1px solid #f7f3ff; color: #4a3a6b; vertical-align: middle; }
        tbody tr:hover td { background: #faf8ff; }
        .name-cell { display: flex; align-items: center; gap: 10px; }
        .av { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
        .fname { font-weight: 700; color: #2d1657; }
        .email-td { color: #9b8bb8; }
        .date-td  { color: #9b8bb8; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #ede9f6; color: #4a235a; }
        .teacher-link { color: #7c3aed; text-decoration: none; font-size: 12px; font-weight: 600; }
        .teacher-link:hover { text-decoration: underline; }
        .acts { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn-edit { background: #eff6ff; color: #2563eb; padding: 5px 12px; border-radius: 8px; font-size: 12px; text-decoration: none; font-weight: 600; border: 1px solid #bfdbfe; }
        .btn-edit:hover { background: #dbeafe; }
        .btn-del { background: #fff1f2; color: #e11d48; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid #fecdd3; text-decoration: none; font-family: inherit; }
        .btn-del:hover { background: #ffe4e6; }
        .btn-grade { background: #fffbeb; color: #d97706; padding: 5px 12px; border-radius: 8px; font-size: 12px; text-decoration: none; font-weight: 600; border: 1px solid #fde68a; }
        .btn-grade:hover { background: #fef3c7; }
        .tbl-footer { display: flex; align-items: center; justify-content: space-between; padding: 14px 22px; border-top: 1px solid #f0eaf8; }
        .footer-info { font-size: 12px; color: #9b8bb8; }
        .empty { text-align: center; padding: 48px; color: #c4b8e0; }

        @media (max-width: 700px) {
            .stats { flex-direction: column; }
            .own-details { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a class="brand" href="dashboard.php">
        <div class="brand-logo">🎓</div>
        <span class="brand-text">EduTeam</span>
    </a>
    <div class="nav-links">
        <?php if ($is_teacher): ?>
            <span class="role-badge role-teacher">👨‍🏫 Teacher</span>
        <?php else: ?>
            <span class="role-badge role-student">🎓 Student</span>
        <?php endif; ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="student_logout.php" class="danger">Logout</a>
    </div>
</nav>

<div class="content">

    <?php if (isset($_GET['success'])): ?>
    <div class="success-msg">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if ($is_student && !empty($students)):
        $s = $students[0];
        $parts    = explode(' ', $s['full_name']);
        $initials = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
        $av_color = $avatar_colors[$s['student_id'] % count($avatar_colors)];
        $avg = count($student_grades) > 0 ? round(array_sum(array_column($student_grades,'score'))/count($student_grades),1) : 0;
    ?>
    <!-- ── STUDENT VIEW — own profile + grades ── -->
    <div class="own-profile">

        <!-- Profile header -->
        <div class="own-profile-header">
            <div class="own-avatar" style="background:<?= $av_color ?>">
                <?= $initials ?>
            </div>
            <div>
                <div class="own-name">👋 Welcome, <?= htmlspecialchars($s['full_name']) ?>!</div>
                <div class="own-sub">Student Profile Management — Developer: Deepa Thapa | SRS-84</div>
            </div>
        </div>

        <!-- Profile details -->
        <div class="own-details">
            <div class="detail-box">
                <div class="detail-lbl">Student ID</div>
                <div class="detail-val">#<?= $s['student_id'] ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-lbl">Email</div>
                <div class="detail-val"><?= htmlspecialchars($s['email']) ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-lbl">Course</div>
                <div class="detail-val"><?= htmlspecialchars($s['course']) ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-lbl">Teacher</div>
                <div class="detail-val"><?= htmlspecialchars($s['teacher_name'] ?? '—') ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-lbl">Enrolled Date</div>
                <div class="detail-val"><?= $s['enrolled_date'] ? date('d M Y', strtotime($s['enrolled_date'])) : '—' ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-lbl">Average Score</div>
                <div class="detail-val"><?= count($student_grades) > 0 ? $avg.'%' : '—' ?></div>
            </div>
        </div>

        <!-- Grades table -->
        <div class="grades-section">
            <div class="grades-title">
                📊 My Grades
                <span class="fk-tag">Linked via student_id = <?= $s['student_id'] ?> (FK → grades table)</span>
            </div>
            <div class="grades-table-wrap">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Semester</th>
                            <th>Score</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($student_grades)): ?>
                        <tr><td colspan="6" class="no-grades">No grades recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($student_grades as $i => $g):
                            $c = gradeColor($g['score']); ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><strong><?= htmlspecialchars($g['subject']) ?></strong></td>
                            <td><?= htmlspecialchars($g['semester']) ?></td>
                            <td>
                                <div class="score-wrap">
                                    <strong><?= $g['score'] ?>%</strong>
                                    <div class="score-bar-bg">
                                        <div class="score-bar" style="width:<?= $g['score'] ?>%;background:<?= $c['color'] ?>"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="grade-badge"
                                    style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;border-color:<?= $c['border'] ?>">
                                    <?= htmlspecialchars($g['grade_value']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($g['remarks'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /own-profile -->

    <?php elseif ($is_teacher): ?>
    <!-- ── TEACHER VIEW — full student list ── -->

    <!-- Welcome banner -->
    <div class="banner">
        <div class="banner-av"><?= $initial ?></div>
        <div>
            <div class="banner-title">👋 Welcome, <span><?= htmlspecialchars($user_name) ?>!</span></div>
            <div class="banner-sub">Student Profile Management — Developer: Deepa Thapa | SRS-84</div>
        </div>
    </div>

    <!-- Page header -->
    <div class="page-hdr">
        <div>
            <h1 class="page-title">Students</h1>
            <p class="page-sub">Developer: Deepa Thapa | SRS-84</p>
        </div>
        <a href="add_student.php" class="add-btn">+ Add New Student</a>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat"><div class="stat-lbl">Total Students</div><div class="stat-val"><?= $total ?></div></div>
        <div class="stat"><div class="stat-lbl">Showing</div><div class="stat-val"><?= $showing ?></div></div>
        <div class="stat"><div class="stat-lbl">Current Page</div><div class="stat-val">1 / 1</div></div>
    </div>

    <!-- Search -->
    <form method="POST" action="student_list.php">
        <div class="search-row">
            <div class="search-wrap">
                <svg class="s-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="s-input" name="search" placeholder="Search by student name..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select class="f-select" name="course_filter">
                <option value="">All Courses</option>
                <?php foreach ($course_list as $c): ?>
                <option value="<?= htmlspecialchars($c['course']) ?>"
                    <?= $course_filter === $c['course'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['course']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-search">Search</button>
            <a href="student_list.php" class="btn-clear">Clear</a>
        </div>
    </form>

    <!-- Table -->
    <div class="tbl-card">
        <div class="tbl-top">
            <h3>All Students</h3>
            <span>Showing <?= $showing ?> of <?= $total ?> students</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Enrolled Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr><td colspan="7" class="empty">No students found</td></tr>
            <?php else: ?>
                <?php foreach ($students as $i => $s):
                    $color    = $avatar_colors[$i % count($avatar_colors)];
                    $parts    = explode(' ', $s['full_name']);
                    $initials = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
                    $date_display = !empty($s['enrolled_date']) ? date('d M Y', strtotime($s['enrolled_date'])) : '—';
                    $teacher_name = $s['teacher_name'] ?? '—';
                ?>
                <tr>
                    <td><?= $s['student_id'] ?></td>
                    <td>
                        <div class="name-cell">
                            <div class="av" style="background:<?=$color?>22;color:<?=$color?>"><?= $initials ?></div>
                            <span class="fname"><?= htmlspecialchars($s['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="email-td"><?= htmlspecialchars($s['email']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($s['course']) ?></span></td>
                    <td>
                        <a href="../sita/teacher_list.php?teacher_id=<?= $s['teacher_id'] ?>" class="teacher-link">
                            <?= htmlspecialchars($teacher_name) ?>
                        </a>
                    </td>
                    <td class="date-td"><?= $date_display ?></td>
                    <td>
                        <div class="acts">
                            <a href="edit_student.php?id=<?= $s['student_id'] ?>" class="btn-edit">Edit</a>
                            <a href="delete_student.php?id=<?= $s['student_id'] ?>" class="btn-del"
                               onclick="return confirm('Delete <?= htmlspecialchars(addslashes($s['full_name'])) ?>? This cannot be undone.')">
                               Delete
                            </a>
                            <a href="../binu/grades/grade_list.php?student_id=<?= $s['student_id'] ?>" class="btn-grade">Grades</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="tbl-footer">
            <span class="footer-info">Total: <?= $total ?> students</span>
        </div>
    </div>

    <?php endif; ?>

</div>
</body>
</html>