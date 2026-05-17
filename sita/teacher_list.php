<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if(!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit();
}

include '../db.php';
include 'Teacher.php';

$teacher = new Teacher($conn);

$search = $_GET['search'] ?? '';
$teachers = $teacher->getAllTeachers($search);
$total = $teacher->countTeachers();

$msg_success = $_GET['success'] ?? '';
$msg_error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{ margin:0; padding:0; box-sizing:border-box; }

body{
    font-family:'Plus Jakarta Sans',sans-serif;
    background:#f5f3ff;
    color:#1e1b4b;
    min-height:100vh;
}

.sidebar{
    position:fixed;
    left:0; top:0;
    width:220px;
    height:100vh;
    background:#ffffff;
    border-right:1px solid #ede9fe;
    padding:32px 16px;
    display:flex;
    flex-direction:column;
    gap:6px;
}

.nav-item{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 14px;
    border-radius:10px;
    color:#6b7280;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    transition:all 0.2s;
}
.nav-item:hover{ background:#f3effe; color:#7c3aed; }
.nav-item.active{ background:#ede9fe; color:#7c3aed; }

.logout{
    margin-top:auto;
    color:#6b7280;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    padding:12px 14px;
    border-radius:10px;
    display:block;
}
.logout:hover{ background:#f3effe; color:#7c3aed; }

.main{ margin-left:220px; padding:36px 40px; }

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:28px;
}

.page-title{ font-size:32px; font-weight:800; color:#1e1b4b; margin-bottom:4px; }
.welcome{ font-size:14px; color:#9b8bb8; }

.btn-add{
    background:#7c3aed;
    color:white;
    padding:12px 22px;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    font-size:14px;
    white-space:nowrap;
}
.btn-add:hover{ background:#6d28d9; }

.stat{
    background:#ffffff;
    border:1px solid #ddd6fe;
    padding:20px 28px;
    border-radius:16px;
    display:inline-block;
    margin-bottom:28px;
    box-shadow:0 2px 8px rgba(124,58,237,0.06);
}
.stat h2{ font-size:32px; font-weight:800; color:#7c3aed; margin-bottom:2px; }
.stat p{ font-size:14px; color:#9b8bb8; }

.role-title{ font-size:16px; font-weight:800; color:#1e1b4b; margin-bottom:14px; }

.role-actions{
    display:flex;
    gap:12px;
    margin-bottom:28px;
    flex-wrap:wrap;
}

.role-btn{
    background:#ffffff;
    border:1px solid #ddd6fe;
    padding:12px 20px;
    border-radius:10px;
    font-size:14px;
    font-weight:600;
    color:#1e1b4b;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:8px;
    transition:all 0.2s;
}
.role-btn:hover{ background:#ede9fe; border-color:#c4b5fd; color:#7c3aed; }

.filter-bar{
    display:flex;
    gap:0;
    margin-bottom:20px;
    border:1px solid #ddd6fe;
    border-radius:12px;
    overflow:hidden;
    background:#ffffff;
}

.filter-input{
    flex:1;
    padding:14px 18px;
    border:none;
    font-size:14px;
    color:#1e1b4b;
    font-family:inherit;
    outline:none;
    background:transparent;
}
.filter-input::placeholder{ color:#c4b5fd; }

.btn-filter{
    background:#7c3aed;
    color:white;
    border:none;
    padding:14px 28px;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    font-family:inherit;
}
.btn-filter:hover{ background:#6d28d9; }

.table-wrap{
    background:#ffffff;
    border:1px solid #ede9fe;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 2px 8px rgba(124,58,237,0.04);
}

table{ width:100%; border-collapse:collapse; }

th{
    padding:14px 18px;
    text-align:left;
    font-size:13px;
    font-weight:800;
    color:#1e1b4b;
    background:#f5f3ff;
    border-bottom:1px solid #ede9fe;
}

td{
    padding:14px 18px;
    font-size:14px;
    color:#374151;
    border-top:1px solid #f3f0ff;
}

tr:hover td{ background:#faf8ff; }

.badge{ padding:5px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.active-badge{ background:#dcfce7; color:#166534; }
.inactive-badge{ background:#fee2e2; color:#991b1b; }

.btn-edit{
    background:#2563eb;
    color:white;
    padding:6px 16px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    margin-right:6px;
}

.btn-delete{
    background:#dc2626;
    color:white;
    padding:6px 16px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:12px 16px;
    border-radius:10px;
    margin-bottom:20px;
    font-size:14px;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px 16px;
    border-radius:10px;
    margin-bottom:20px;
    font-size:14px;
}
</style>
</head>
<body>

<div class="sidebar">
    <a href="../deepa/dashboard.php" class="nav-item">🏠 Main Dashboard</a>
    <a href="teacher_list.php" class="nav-item active">👩‍🏫 Teacher Dashboard</a>
    <a href="view_students.php" class="nav-item">👨‍🎓 Students</a>
    <a href="teacher_logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">

    <?php if($msg_success == 'added'): ?>
        <div class="alert-success">✅ Teacher added successfully.</div>
    <?php endif; ?>

    <?php if($msg_success == 'updated'): ?>
        <div class="alert-success">✅ Teacher updated successfully.</div>
    <?php endif; ?>

    <?php if($msg_success == 'deleted'): ?>
        <div class="alert-success">✅ Teacher deleted successfully.</div>
    <?php endif; ?>

    <?php if($msg_error): ?>
        <div class="alert-error">❌ Action failed.</div>
    <?php endif; ?>

    <div class="topbar">
        <div>
            <div class="page-title">Teacher Dashboard</div>
            <div class="welcome">Welcome <?php echo htmlspecialchars($_SESSION['teacher_name']); ?> 👋</div>
        </div>
        <a href="add_teacher.php" class="btn-add">+ Add Teacher</a>
    </div>

    <div class="stat">
        <h2><?php echo $total; ?></h2>
        <p>Active Teachers</p>
    </div>

    <div class="role-title">Teacher Role Actions</div>
    <div class="role-actions">
        <a href="view_students.php" class="role-btn">👨‍🎓 View Students</a>
        <a href="../isha/course_list.php" class="role-btn">📋 View Assigned Program</a>
        <a href="../binu/grades/grade_list.php" class="role-btn">📝 View / Manage Grades</a>
        <a href="../satinder/attendance_list.php" class="role-btn">📅 View Attendance</a>
    </div>

    <form method="GET">
        <div class="filter-bar">
            <input
                type="text"
                name="search"
                class="filter-input"
                placeholder="Find / Filter teacher by name, email, subject or assigned program, Teacher Status, phone..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-filter">Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Assigned Program</th>
                <th>Phone</th>
                <th>Teacher Status</th>
                <th>Actions</th>
            </tr>

            <?php if($teachers && mysqli_num_rows($teachers) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($teachers)): ?>
                <tr>
                    <td><?php echo $row['teacher_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['course_name'] ?? 'Not Assigned'); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? '—'); ?></td>
                    <td>
                        <?php if($row['is_active'] == 1): ?>
                            <span class="badge active-badge">Active</span>
                        <?php else: ?>
                            <span class="badge inactive-badge">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_teacher.php?id=<?php echo $row['teacher_id']; ?>" class="btn-edit">Edit</a>
                        <a href="delete_teacher.php?id=<?php echo $row['teacher_id']; ?>"
                           class="btn-delete"
                           onclick="return confirm('Delete this teacher?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#9b8bb8;">
                        No teachers found.
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</div>
</body>
</html>