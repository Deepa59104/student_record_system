<?php
session_start();
if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../isha/login.php');
    exit();
}

include '../db.php';
include 'Teacher.php';

$conn = mysqli_connect('localhost', 'root', '', 'student_record_system', 3306);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard — Edu Team SRS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f0a1e;
            min-height: 100vh;
            color: white;
        }
        .sidebar {
            position: fixed; left: 0; top: 0;
            width: 240px; height: 100vh;
            background: rgba(255,255,255,0.03);
            border-right: 1px solid rgba(255,255,255,0.07);
            padding: 24px 16px;
            display: flex; flex-direction: column; gap: 4px;
        }
        .sidebar-logo {
            display: flex; align-items: center; gap: 10px;
            padding: 0 8px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 8px;
        }
        .logo-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 16px;
        }
        .logo-text { font-weight: 700; font-size: 15px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            color: rgba(255,255,255,0.5); font-size: 14px;
            text-decoration: none; font-weight: 500;
            transition: all 0.2s;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background: rgba(124,58,237,0.2); color: #a855f7; }
        .main { margin-left: 240px; padding: 32px; }
        .page-header {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .welcome { font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 6px; }
        .page-title { font-size: 22px; font-weight: 800; }
        .page-sub { font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px; }
        .btn-add {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white; border: none;
            padding: 10px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 600;
            font-family: inherit; cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(124,58,237,0.4);
        }
        .btn-add:hover { opacity: 0.88; }
        .search-bar { display: flex; gap: 12px; margin-bottom: 20px; }
        .search-input {
            flex: 1;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px 16px;
            color: white; font-size: 14px;
            font-family: inherit; outline: none;
        }
        .search-input::placeholder { color: rgba(255,255,255,0.3); }
        .search-input:focus { border-color: rgba(168,85,247,0.6); }
        .btn-search {
            background: rgba(124,58,237,0.3);
            border: 1px solid rgba(124,58,237,0.4);
            color: white; padding: 10px 20px;
            border-radius: 10px; font-size: 14px;
            font-family: inherit; cursor: pointer;
            text-decoration: none;
        }
        .stat-card {
            background: rgba(124,58,237,0.15);
            border: 1px solid rgba(124,58,237,0.3);
            border-radius: 12px; padding: 16px 20px;
            margin-bottom: 20px; display: inline-block;
        }
        .stat-number { font-size: 28px; font-weight: 800; color: #a855f7; }
        .stat-label { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 2px; }
        .table-wrap {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(255,255,255,0.04); }
        th {
            padding: 14px 20px; text-align: left;
            font-size: 12px; font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        td {
            padding: 14px 20px; font-size: 14px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        tr:hover td { background: rgba(255,255,255,0.02); }
        .badge-active {
            background: rgba(34,197,94,0.15); color: #4ade80;
            border: 1px solid rgba(34,197,94,0.3);
            padding: 3px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .badge-inactive {
            background: rgba(239,68,68,0.15); color: #f87171;
            border: 1px solid rgba(239,68,68,0.3);
            padding: 3px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .btn-edit {
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3);
            color: #60a5fa; padding: 6px 14px;
            border-radius: 8px; font-size: 13px;
            text-decoration: none; font-weight: 500;
            margin-right: 6px;
        }
        .btn-delete {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171; padding: 6px 14px;
            border-radius: 8px; font-size: 13px;
            text-decoration: none; font-weight: 500;
        }
        .alert-success {
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.3);
            color: #4ade80; padding: 12px 16px;
            border-radius: 12px; font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171; padding: 12px 16px;
            border-radius: 12px; font-size: 13px;
            margin-bottom: 20px;
        }
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: rgba(255,255,255,0.3);
        }
        .empty-state div { font-size: 40px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">E</div>
            <div class="logo-text">Edu Team</div>
        </div>
        <a href="../deepa/dashboard.php" class="nav-item">🏠 Dashboard</a>
        <a href="teacher_list.php" class="nav-item active">👩‍🏫 Teachers</a>
        <a href="../deepa/student_list.php" class="nav-item">👨‍🎓 Students</a>
        <a href="../isha/logout.php" class="nav-item" style="margin-top:auto;">🚪 Logout</a>
    </div>

    <div class="main">

        <?php if($msg_success == 'deleted'): ?>
            <div class="alert-success">✅ Teacher deleted successfully!</div>
        <?php endif; ?>
        <?php if($msg_error): ?>
            <div class="alert-error">❌ <?php echo $msg_error == 'cannot_delete_self' ? 'You cannot delete your own account!' : 'Failed to delete teacher.'; ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <div class="welcome">Welcome back, Sita Subedi 👋</div>
                <div class="page-title">Teacher Dashboard</div>
                <div class="page-sub">SRS-92 | Developer: Sita</div>
            </div>
            <a href="add_teacher.php" class="btn-add">+ Add Teacher</a>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Active Teachers</div>
        </div>

        <form method="GET" class="search-bar">
            <input type="text" name="search" class="search-input"
                   placeholder="Search by name, email or subject..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Search</button>
            <?php if($search): ?>
                <a href="teacher_list.php" class="btn-search">Clear</a>
            <?php endif; ?>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($teachers) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($teachers)): ?>
                        <tr>
                            <td><?php echo $row['teacher_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? '—'); ?></td>
                            <td>
                                <?php if($row['is_active']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
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
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <div>👩‍🏫</div>
                                No teachers found.
                            </div>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px; font-size:12px; color:rgba(255,255,255,0.2);">
            Developer: Sita | SRS-92 | Edu Team
        </div>
    </div>
</body>
</html>