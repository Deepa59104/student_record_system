<?php
// Start session and check if user is logged in
session_start();
if(!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Connect to the database
include '../db.php';

// ✅ NEW: Handle Approve / Reject button clicks
if(isset($_GET['approve_id'])) {
    $aid    = intval($_GET['approve_id']);
    $action = $_GET['action'] === 'Approved' ? 'Approved' : 'Rejected';
    mysqli_query($conn, "UPDATE attendance SET approval_status='$action' WHERE attendance_id=$aid");
    // Redirect back to same page (with any existing filters kept)
    $qs = http_build_query(array_diff_key($_GET, ['approve_id'=>1,'action'=>1]));
    header("Location: attendance_list.php" . ($qs ? "?$qs" : ''));
    exit();
}

// Get search and filter values from the URL
$search        = isset($_GET['search'])        ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_date   = isset($_GET['filter_date'])   ? $_GET['filter_date']   : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Build the SQL query with optional filters
$sql = "SELECT a.*, s.student_name, s.course
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        WHERE s.student_name LIKE '%$search%'";

if($filter_date)   $sql .= " AND a.attendance_date = '$filter_date'";
if($filter_status) $sql .= " AND a.status = '$filter_status'";

$sql .= " ORDER BY a.attendance_date DESC";

$result = mysqli_query($conn, $sql);
$rows = [];
while($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance List</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
body { background: #f0eef6; }

.navbar {
    background: #4a2c6e; padding: 15px 30px;
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 30px;
}
.navbar .brand {
    color: white; font-size: 20px; font-weight: bold;
    display: flex; align-items: center; gap: 10px; text-decoration: none;
}
.navbar .brand span {
    background: white; color: #4a2c6e; border-radius: 50%;
    width: 32px; height: 32px; display: flex; align-items: center;
    justify-content: center; font-size: 16px;
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
    border-radius: 5px; text-decoration: none;
}
.navbar-links .btn-logout:hover { background: #c82333; }

.page { padding: 0 30px 30px; }
.page-title { font-size: 22px; font-weight: bold; color: #333; margin-bottom: 5px; }
.page-sub { color: #888; font-size: 13px; margin-bottom: 25px; }

.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
input[type=text], input[type=date], select {
    padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
}

.btn-add { background: #4a2c6e; color: white; padding: 9px 18px; border-radius: 5px; text-decoration: none; font-weight: bold; }
.btn-add:hover { background: #3a1f58; }

table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
thead { background: #4a2c6e; color: white; }
th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
tr:hover { background: #f5f3fb; }

/* Attendance status badges */
.present { background: #e6f4ea; color: #28a745; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }
.absent  { background: #fde8e8; color: #dc3545; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }
.late    { background: #fff3e0; color: #f57c00; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }

/* ✅ NEW: Approval status badges */
.badge-approved { background: #e0f7ef; color: #0a7c50; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }
.badge-rejected { background: #fde8e8; color: #dc3545; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }
.badge-pending  { background: #f0eef8; color: #6a3fa0; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }

/* Action buttons */
.btn-edit    { background: #007bff; color: white; padding: 5px 11px; border-radius: 4px; text-decoration: none; font-size: 13px; }
.btn-delete  { background: #dc3545; color: white; padding: 5px 11px; border-radius: 4px; text-decoration: none; font-size: 13px; margin-left: 4px; }
/* ✅ NEW: Approve / Reject buttons */
.btn-approve { background: #28a745; color: white; padding: 5px 11px; border-radius: 4px; text-decoration: none; font-size: 13px; margin-left: 4px; }
.btn-reject  { background: #fd7e14; color: white; padding: 5px 11px; border-radius: 4px; text-decoration: none; font-size: 13px; margin-left: 4px; }
.btn-edit:hover    { background: #0056b3; }
.btn-delete:hover  { background: #c82333; }
.btn-approve:hover { background: #1e7e34; }
.btn-reject:hover  { background: #e0660e; }

.btn-search { padding: 8px 16px; background: #4a2c6e; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold; }
.btn-search:hover { background: #3a1f58; }
.btn-clear { padding: 8px 14px; background: #f0eef6; color: #4a2c6e; border: 1px solid #4a2c6e; border-radius: 5px; font-size: 14px; text-decoration: none; }
.results-info { font-size: 13px; color: #888; margin-bottom: 12px; }
.results-info span { color: #4a2c6e; font-weight: bold; }
.no-results { text-align: center; padding: 40px; color: #aaa; font-size: 15px; }
</style>
</head>
<body>

<nav class="navbar">
    <a class="brand" href="dashboard.php"><span>🎓</span> EduTeam</a>
    <div class="navbar-links">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="attendance_list.php" class="active">📋 Attendance</a>
        <a href="add_attendance.php">➕ Add Attendance</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="page">
    <div class="page-title">📋 Attendance Management</div>
    <div class="page-sub">Developer: Satinder Singh Tanwar | SRS-87</div>

    <form method="GET">
    <div class="top-bar">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <input type="text" name="search"
                   placeholder="🔍 Search by student name..."
                   value="<?= htmlspecialchars($search) ?>">
            <input type="date" name="filter_date"
                   value="<?= htmlspecialchars($filter_date) ?>">
            <select name="filter_status">
                <option value="">-- All Status --</option>
                <option value="Present" <?= $filter_status=='Present' ? 'selected' : '' ?>>✅ Present</option>
                <option value="Absent"  <?= $filter_status=='Absent'  ? 'selected' : '' ?>>❌ Absent</option>
                <option value="Late"    <?= $filter_status=='Late'    ? 'selected' : '' ?>>🕐 Late</option>
            </select>
            <button type="submit" class="btn-search">Search</button>
            <a href="attendance_list.php" class="btn-clear">Clear</a>
        </div>
        <a href="add_attendance.php" class="btn-add">➕ Add Attendance</a>
    </div>
    </form>

    <div class="results-info">
        Showing <span><?= count($rows) ?></span> record(s)
        <?php if($search)        echo " for <span>\"$search\"</span>"; ?>
        <?php if($filter_status) echo " &nbsp;·&nbsp; Status: <span>$filter_status</span>"; ?>
        <?php if($filter_date)   echo " &nbsp;·&nbsp; Date: <span>$filter_date</span>"; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Enrolled Course</th>
                <th>Date</th>
                <th>Status</th>
                <th>✅ Approval Status</th>  <!-- ✅ NEW COLUMN -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(count($rows) == 0): ?>
            <tr><td colspan="7" class="no-results">😕 No records found matching your search.</td></tr>
        <?php else: ?>
            <?php foreach($rows as $row): ?>
            <?php
                $statusClass   = strtolower($row['status']);
                // ✅ NEW: Read approval_status from DB (default Pending if column not set)
                $approval      = isset($row['approval_status']) ? $row['approval_status'] : 'Pending';
                $approvalClass = 'badge-' . strtolower($approval);
                // Keep current search filters in approve/reject URLs
                $filterStr = http_build_query(['search'=>$search,'filter_date'=>$filter_date,'filter_status'=>$filter_status]);
            ?>
            <tr>
                <td><?= $row['student_id'] ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><?= $row['attendance_date'] ?></td>
                <td><span class="<?= $statusClass ?>"><?= $row['status'] ?></span></td>

                <!-- ✅ NEW: Approval status badge -->
                <td><span class="<?= $approvalClass ?>"><?= $approval ?></span></td>

                <td>
                    <a href="edit_attendance.php?id=<?= $row['attendance_id'] ?>" class="btn-edit">Edit</a>
                    <a href="delete_attendance.php?id=<?= $row['attendance_id'] ?>"
                       class="btn-delete"
                       onclick="return confirm('Delete this record?')">Delete</a>

                    <!-- ✅ NEW: Approve button -->
                    <a href="attendance_list.php?approve_id=<?= $row['attendance_id'] ?>&action=Approved&<?= $filterStr ?>"
                       class="btn-approve"
                       onclick="return confirm('Approve this record?')">Approve</a>

                    <!-- ✅ NEW: Reject button -->
                    <a href="attendance_list.php?approve_id=<?= $row['attendance_id'] ?>&action=Rejected&<?= $filterStr ?>"
                       class="btn-reject"
                       onclick="return confirm('Reject this record?')">Reject</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>