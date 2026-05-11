<?php
// ============================================================
// FILE: student_dashboard.php
// MODULE: Grade & Result Tracking
// PROJECT: EduTeam - Student Record System
// DEVELOPER: Binu Karki
// LAYER: Presentation Layer
// DESCRIPTION: Student dashboard - uses Grade class (middle layer)
//              to fetch and display student's own grades and charts
// ============================================================

// MIDDLE LAYER: Start session
session_start();

// Security check: only students allowed
if (!isset($_SESSION['grade_user']) || $_SESSION['grade_role'] !== 'student') {
    header("Location: grade_login.php");
    exit();
}

// DATA LAYER: Include shared database connection
require_once '../../db.php';

// MIDDLE LAYER: Include and instantiate Grade class
require_once 'Grade.php';
$gradeObj = new Grade($conn);

// MIDDLE LAYER: Get student_id from session
$student_id = $_SESSION['grade_student_id'];

// ============================================================
// MIDDLE LAYER: Fetch data using Grade class methods
// ============================================================

// Get this student's grades only
$grades = $gradeObj->getStudentGrades($student_id);

// Get chart data for this student
$chart_rows = $gradeObj->getStudentChartData($student_id);

// ============================================================
// MIDDLE LAYER: Prepare chart arrays for JavaScript
// ============================================================
$chart_labels  = [];
$chart_mid     = [];
$chart_final   = [];
$chart_percent = [];

foreach ($chart_rows as $row) {
    $chart_labels[]  = $row['course_id'];
    $chart_mid[]     = $row['mid_term'];
    $chart_final[]   = $row['final_term'];
    $chart_percent[] = $row['percentage'];
}

// ============================================================
// MIDDLE LAYER: Calculate summary statistics
// ============================================================
$total_courses  = count($grades);
$total_passed   = 0;
$total_failed   = 0;
$avg_percentage = 0;

foreach ($grades as $g) {
    if ($g['is_passed'] == 1) {
        $total_passed++;
    } else {
        $total_failed++;
    }
    $avg_percentage += $g['percentage'];
}

// Avoid division by zero
$avg_percentage = $total_courses > 0
    ? round($avg_percentage / $total_courses, 2)
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | EduTeam Grades</title>

    <!-- PRESENTATION LAYER: Fonts, Icons, Chart.js -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ============================================================
           PRESENTATION LAYER: CSS - EduTeam Purple Theme
        ============================================================ */
        :root {
            --primary:    #4a235a;
            --hover:      #6b3580;
            --bg:         #f0f0f5;
            --success:    #1e8449;
            --error:      #c0392b;
            --text:       #2c2c2c;
            --light-text: #7a7a9d;
            --border:     #e0e0ee;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }

        /* NAVBAR */
        .navbar { background: var(--primary); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(74,35,90,0.3); position: sticky; top: 0; z-index: 100; }
        .navbar-brand { color: white; font-family: 'Playfair Display', serif; font-size: 1.4rem; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .navbar-right { display: flex; align-items: center; gap: 15px; }
        .navbar-user  { color: rgba(255,255,255,0.85); font-size: 0.9rem; }
        .btn-logout { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 7px 16px; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; cursor: pointer; text-decoration: none; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
        .btn-logout:hover { background: rgba(255,255,255,0.25); }

        /* CONTAINER */
        .container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }

        /* WELCOME BANNER */
        .welcome-banner { background: var(--primary); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; color: white; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 20px rgba(74,35,90,0.25); }
        .welcome-icon  { width: 60px; height: 60px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; }
        .welcome-text h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 4px; }
        .welcome-text p  { font-size: 0.88rem; color: rgba(255,255,255,0.75); }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card  { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 12px rgba(74,35,90,0.08); border-top: 4px solid var(--primary); }
        .stat-num   { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--primary); font-weight: 700; }
        .stat-num.green { color: var(--success); }
        .stat-num.red   { color: var(--error); }
        .stat-label { font-size: 0.8rem; color: var(--light-text); margin-top: 4px; }

        /* GRID */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }

        /* CARDS */
        .card      { background: white; border-radius: 14px; padding: 25px; box-shadow: 0 2px 12px rgba(74,35,90,0.08); }
        .card-full { background: white; border-radius: 14px; padding: 25px; box-shadow: 0 2px 12px rgba(74,35,90,0.08); margin-bottom: 25px; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        /* CHART */
        .chart-container { position: relative; height: 280px; }

        /* TABLE */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        thead { background: var(--primary); color: white; }
        thead th { padding: 12px 15px; text-align: left; font-weight: 500; font-size: 0.85rem; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.2s; }
        tbody tr:hover { background: #f7f4fa; }
        tbody td { padding: 12px 15px; }
        .badge      { padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 500; }
        .badge-pass { background: #eafaf1; color: #1e8449; }
        .badge-fail { background: #fdecea; color: var(--error); }
        .progress-bar-wrap { background: #f0f0f5; border-radius: 20px; height: 8px; width: 100px; display: inline-block; vertical-align: middle; margin-right: 8px; }
        .progress-bar-fill { height: 8px; border-radius: 20px; background: var(--primary); }
        .no-data { text-align: center; padding: 40px; color: var(--light-text); }

        /* FOOTER */
        footer { text-align: center; padding: 20px; color: var(--light-text); font-size: 0.8rem; margin-top: 10px; }
    </style>
</head>
<body>

<!-- PRESENTATION LAYER: Navbar -->
<nav class="navbar">
    <a href="#" class="navbar-brand">
        <i class="fas fa-graduation-cap"></i> EduTeam — Grades
    </a>
    <div class="navbar-right">
        <span class="navbar-user">
            <i class="fas fa-user-graduate"></i>
            <?= htmlspecialchars($_SESSION['grade_user']) ?>
        </span>
        <a href="grade_logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<div class="container">

    <!-- PRESENTATION LAYER: Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="welcome-text">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['grade_user']) ?>!</h2>
            <p>Student ID: <?= htmlspecialchars($student_id) ?> &nbsp;|&nbsp; Here are your grade results</p>
        </div>
    </div>

    <!-- PRESENTATION LAYER: Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num"><?= $total_courses ?></div>
            <div class="stat-label">Total Courses</div>
        </div>
        <div class="stat-card">
            <div class="stat-num green"><?= $total_passed ?></div>
            <div class="stat-label">Passed</div>
        </div>
        <div class="stat-card">
            <div class="stat-num red"><?= $total_failed ?></div>
            <div class="stat-label">Failed</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= $avg_percentage ?>%</div>
            <div class="stat-label">Avg Percentage</div>
        </div>
    </div>

    <?php if (!empty($grades)): ?>

        <!-- PRESENTATION LAYER: Charts -->
        <div class="grid-2">
            <div class="card">
                <div class="card-title"><i class="fas fa-chart-line"></i> Mid Term vs Final Term</div>
                <div class="chart-container">
                    <canvas id="studentChart"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-title"><i class="fas fa-chart-bar"></i> Percentage by Course</div>
                <div class="chart-container">
                    <canvas id="percentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- PRESENTATION LAYER: Grade Table -->
        <div class="card-full">
            <div class="card-title"><i class="fas fa-list-alt"></i> My Grade Details</div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Course</th>
                            <th>Mid Term</th>
                            <th>Final Term</th>
                            <th>Total</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $i => $g): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($g['course_id']) ?></strong></td>
                                <td><?= $g['mid_term'] ?> / 100</td>
                                <td><?= $g['final_term'] ?> / 100</td>
                                <td><?= $g['total_grade'] ?> / 200</td>
                                <td>
                                    <span class="progress-bar-wrap">
                                        <span class="progress-bar-fill" style="width: <?= min($g['percentage'], 100) ?>%"></span>
                                    </span>
                                    <?= $g['percentage'] ?>%
                                </td>
                                <td>
                                    <span class="badge <?= $g['is_passed'] ? 'badge-pass' : 'badge-fail' ?>">
                                        <?= $g['is_passed'] ? '✓ Pass' : '✗ Fail' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="card-full">
            <div class="no-data">
                <i class="fas fa-inbox" style="font-size:3rem; display:block; margin-bottom:12px;"></i>
                <p>No grades found for your account yet.</p>
                <p style="font-size:0.85rem; margin-top:5px;">Please contact your teacher.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<footer>&copy; 2026 EduTeam | Grade &amp; Result Tracking Module | Developed by Binu Karki</footer>

<!-- PRESENTATION LAYER: Chart.js Scripts -->
<script>
    // Line Chart: Mid Term vs Final Term
    const ctx1 = document.getElementById('studentChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [
                {
                    label: 'Mid Term',
                    data: <?= json_encode($chart_mid) ?>,
                    borderColor: '#4a235a',
                    backgroundColor: 'rgba(74, 35, 90, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4a235a',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Final Term',
                    data: <?= json_encode($chart_final) ?>,
                    borderColor: '#1e8449',
                    backgroundColor: 'rgba(30, 132, 73, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#1e8449',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, max: 100, ticks: { stepSize: 20 } } }
        }
    });

    // Bar Chart: Percentage per Course
    const ctx2 = document.getElementById('percentChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Percentage (%)',
                data: <?= json_encode($chart_percent) ?>,
                backgroundColor: <?= json_encode(array_map(function($p) {
                    return $p >= 50 ? '#1e8449' : '#c0392b';
                }, $chart_percent)) ?>,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, max: 100, ticks: { stepSize: 20 } } }
        }
    });
</script>

</body>
</html>
