<?php
// Start session and check if user is logged in
session_start();
if(!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit(); }
include '../db.php';

// ✅ NEW: Get list of all students for the sidebar
$students_result = mysqli_query($conn, "SELECT * FROM students ORDER BY student_name ASC");
$all_students = [];
while($s = mysqli_fetch_assoc($students_result)) {
    $all_students[] = $s;
}

// ✅ NEW: Which student are we viewing? Default to first student if none selected
$selected_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
if($selected_id === 0 && count($all_students) > 0) {
    $selected_id = $all_students[0]['student_id'];
}

// ✅ NEW: Get the selected student's profile info
$sel_result  = mysqli_query($conn, "SELECT * FROM students WHERE student_id=$selected_id");
$sel_student = mysqli_fetch_assoc($sel_result);

// ✅ NEW: Get stats only for the selected student
$total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE student_id=$selected_id"))['c'];
$present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE student_id=$selected_id AND status='Present'"))['c'];
$absent  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE student_id=$selected_id AND status='Absent'"))['c'];
$late    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE student_id=$selected_id AND status='Late'"))['c'];
$absence_pct = $total > 0 ? number_format(($absent / $total) * 100, 2) : 0;

// ✅ NEW: Weekly chart data only for the selected student
$weeks_result = mysqli_query($conn, "
    SELECT YEAR(attendance_date) as yr, WEEK(attendance_date) as wk,
           SUM(status='Present') as present,
           SUM(status='Late')    as late,
           SUM(status='Absent')  as absent
    FROM attendance
    WHERE student_id = $selected_id
    GROUP BY yr, wk
    ORDER BY yr, wk
");
$week_labels = $week_present = $week_late = $week_absent = [];
while($w = mysqli_fetch_assoc($weeks_result)) {
    $week_labels[]  = 'Week ' . $w['wk'];
    $week_present[] = (int)$w['present'];
    $week_late[]    = (int)$w['late'];
    $week_absent[]  = (int)$w['absent'];
}

// Build initials from student name for avatar
$initials = '';
if($sel_student) {
    foreach(explode(' ', trim($sel_student['student_name'])) as $word)
        $initials .= strtoupper(substr($word, 0, 1));
    $initials = substr($initials, 0, 2);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard – EduTeam</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
body { background: #f0eef6; min-height: 100vh; }

/* ── Navbar ── */
.navbar {
    background: #4a2c6e; padding: 15px 30px;
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; top: 0; z-index: 100;
}
.navbar .brand {
    color: white; font-size: 20px; font-weight: bold;
    display: flex; align-items: center; gap: 10px; text-decoration: none;
}
.navbar .brand span {
    background: white; color: #4a2c6e; border-radius: 50%;
    width: 32px; height: 32px; display: flex; align-items: center;
    justify-content: center; font-size: 16px; font-weight: bold;
}
.navbar-links { display: flex; gap: 15px; align-items: center; }
.navbar-links a {
    color: #ddd; text-decoration: none; font-size: 14px;
    padding: 7px 14px; border-radius: 5px; transition: background 0.2s;
}
.navbar-links a:hover { background: rgba(255,255,255,0.15); color: white; }
.navbar-links a.active { background: rgba(255,255,255,0.2); color: white; }
.navbar-links .btn-logout {
    background: #dc3545; color: white; padding: 7px 14px;
    border-radius: 5px; text-decoration: none; font-size: 14px;
}
.navbar-links .btn-logout:hover { background: #c82333; }

/* ── Page layout: sidebar + content ── */
.page-layout {
    display: flex;
    min-height: calc(100vh - 58px);
}

/* ── ✅ NEW: Student list sidebar ── */
.sidebar {
    width: 240px;
    min-width: 200px;
    background: white;
    border-right: 2px solid #ece8f8;
    overflow-y: auto;
    padding: 16px 0;
}
.sidebar-heading {
    font-size: 11px;
    font-weight: 800;
    color: #9c86c8;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0 16px;
    margin-bottom: 10px;
}
.student-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s;
    border-left: 3px solid transparent;
    color: #333;
}
.student-item:hover { background: #f5f2ff; }
.student-item.active {
    background: #eeeaff;
    border-left-color: #4a2c6e;
    color: #4a2c6e;
}
.stu-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: #4a2c6e; color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: bold; flex-shrink: 0;
}
.student-item.active .stu-avatar { background: #6c42aa; }
.stu-name   { font-size: 14px; font-weight: 600; }
.stu-course { font-size: 11px; color: #999; margin-top: 1px; }

/* ── Main content area ── */
.content { flex: 1; padding: 28px 30px; overflow-y: auto; }

.page-title { font-size: 22px; font-weight: bold; color: #333; margin-bottom: 4px; }
.page-sub   { color: #888; font-size: 13px; margin-bottom: 22px; }

/* ── Top row: profile + stat cards ── */
.top-row {
    display: grid;
    grid-template-columns: 260px 1fr 1fr;
    gap: 20px;
    margin-bottom: 22px;
}
.profile-card {
    background: white; border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden;
}
.profile-header {
    background: #4a2c6e; color: white;
    padding: 13px 18px; font-size: 15px; font-weight: bold;
}
.profile-body { padding: 18px; text-align: center; }
.profile-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: #4a2c6e; color: white; font-size: 26px; font-weight: bold;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.profile-body .name { font-weight: bold; font-size: 16px; color: #333; margin-bottom: 10px; }
.profile-info { text-align: left; font-size: 13px; color: #666; line-height: 2; }
.profile-info strong { color: #444; }

.stat-card {
    background: white; border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    padding: 25px; display: flex; flex-direction: column;
    justify-content: center; align-items: center; text-align: center;
}
.stat-card .stat-title  { font-size: 14px; color: #888; margin-bottom: 10px; }
.stat-card .stat-number { font-size: 48px; font-weight: bold; color: #4a2c6e; }
.stat-card .stat-sub    { font-size: 13px; color: #aaa; margin-top: 6px; }
.stat-card.red .stat-number { color: #dc3545; }

/* ── Charts ── */
.charts-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
}
.card {
    background: white; border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 22px;
}
.card-title { font-size: 15px; font-weight: bold; color: #333; margin-bottom: 18px; }

/* No-data message */
.no-data {
    text-align: center; padding: 40px;
    color: #bbb; font-size: 15px;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a class="brand" href="dashboard.php"><span>🎓</span> EduTeam</a>
    <div class="navbar-links">
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="attendance_list.php">📋 Attendance</a>
        <a href="add_attendance.php">➕ Add Attendance</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="page-layout">

    <!-- ✅ NEW: Left sidebar — list of all students -->
    <aside class="sidebar">
        <div class="sidebar-heading">Students</div>

        <?php if(count($all_students) === 0): ?>
            <p style="padding:16px; color:#aaa; font-size:13px;">No students found.</p>
        <?php endif; ?>

        <?php foreach($all_students as $s):
            // Build 2-letter initials
            $si = '';
            foreach(explode(' ', trim($s['student_name'])) as $w)
                $si .= strtoupper(substr($w, 0, 1));
            $si = substr($si, 0, 2);
            $isActive = ($s['student_id'] == $selected_id);
        ?>
        <a href="dashboard.php?student_id=<?= $s['student_id'] ?>"
           class="student-item <?= $isActive ? 'active' : '' ?>">
            <div class="stu-avatar"><?= $si ?></div>
            <div>
                <div class="stu-name"><?= htmlspecialchars($s['student_name']) ?></div>
                <div class="stu-course"><?= htmlspecialchars($s['course']) ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </aside>

    <!-- Main dashboard content for the selected student -->
    <main class="content">

        <?php if(!$sel_student): ?>
            <div class="no-data">😕 No student selected or found.</div>
        <?php else: ?>

        <div class="page-title">📊 Attendance Dashboard</div>
        <div class="page-sub">Developer: Satinder Singh Tanwar | SRS-87</div>

        <div class="top-row">

            <!-- Profile card — shows selected student's info -->
            <div class="profile-card">
                <div class="profile-header">👤 Student Record System</div>
                <div class="profile-body">
                    <div class="profile-avatar"><?= $initials ?></div>
                    <div class="name"><?= htmlspecialchars($sel_student['student_name']) ?></div>
                    <div class="profile-info">
                        <?php if(!empty($sel_student['address'])): ?>
                        <strong>📍 Address:</strong><br>
                        <?= htmlspecialchars($sel_student['address']) ?><br><br>
                        <?php endif; ?>
                        <?php if(!empty($sel_student['email'])): ?>
                        <strong>📧 Mail:</strong><br>
                        <?= htmlspecialchars($sel_student['email']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Absence % for selected student -->
            <div class="stat-card red">
                <div class="stat-title">📉 Absence in the time span</div>
                <div class="stat-number"><?= $absence_pct ?>%</div>
                <div class="stat-sub">All registered data — No time limits</div>
            </div>

            <!-- Total records for selected student -->
            <div class="stat-card">
                <div class="stat-title">📋 Total Records</div>
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-sub">
                    ✅ Present: <?= $present ?> &nbsp;|&nbsp;
                    ❌ Absent: <?= $absent ?> &nbsp;|&nbsp;
                    🕐 Late: <?= $late ?>
                </div>
            </div>

        </div><!-- /top-row -->

        <!-- Charts for selected student -->
        <?php if($total == 0): ?>
            <div class="card">
                <div class="no-data">😕 No attendance records found for <?= htmlspecialchars($sel_student['student_name']) ?>.</div>
            </div>
        <?php else: ?>
        <div class="charts-row">
            <div class="card">
                <div class="card-title">🥧 Overview of absence types</div>
                <canvas id="pieChart" height="250"></canvas>
            </div>
            <div class="card">
                <div class="card-title">📊 Absence per week</div>
                <canvas id="barChart" height="120"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; // end if sel_student ?>

    </main>
</div><!-- /page-layout -->

<?php if($sel_student && $total > 0): ?>
<script>
// Pie chart — absence types for selected student
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ['Present', 'Late', 'Absent'],
        datasets: [{
            data: [<?= $present ?>, <?= $late ?>, <?= $absent ?>],
            backgroundColor: ['#8bc34a', '#ffc107', '#c0392b'],
            borderWidth: 2
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Bar chart — per week breakdown for selected student
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($week_labels) ?>,
        datasets: [
            { label: 'Present', data: <?= json_encode($week_present) ?>, backgroundColor: '#8bc34a' },
            { label: 'Late',    data: <?= json_encode($week_late) ?>,    backgroundColor: '#ffc107' },
            { label: 'Absent',  data: <?= json_encode($week_absent) ?>,  backgroundColor: '#c0392b' }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
<?php endif; ?>

</body>
</html>