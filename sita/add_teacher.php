<?php
session_start();
if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../isha/login.php');
    exit();
}

include '../db.php';
include 'Teacher.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher = new Teacher($conn);
    $data = [
        'first_name'    => trim($_POST['first_name'] ?? ''),
        'last_name'     => trim($_POST['last_name'] ?? ''),
        'email'         => trim($_POST['email'] ?? ''),
        'password'      => trim($_POST['password'] ?? ''),
        'subject'       => trim($_POST['subject'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'qualification' => trim($_POST['qualification'] ?? ''),
        'course_id'     => intval($_POST['course_id'] ?? 0),
    ];

    if(empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password'])) {
        $error = 'Please fill all required fields.';
    } else {
        if($teacher->addTeacher($data)) {
            $success = 'Teacher added successfully!';
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

// Get active courses for dropdown
$courses = mysqli_query($conn, "SELECT * FROM course WHERE is_active=1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher — Edu Team SRS</title>
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
        .main { margin-left: 240px; padding: 32px; max-width: 700px; }
        .page-header { margin-bottom: 28px; }
        .page-title { font-size: 22px; font-weight: 800; }
        .page-sub { font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px; }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,0.4); font-size: 13px;
            text-decoration: none; margin-bottom: 16px;
        }
        .back-link:hover { color: white; }
        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 28px;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 18px; }
        .form-group.full { grid-column: 1 / -1; }
        label {
            display: block; font-size: 13px;
            color: rgba(255,255,255,0.6);
            font-weight: 500; margin-bottom: 7px;
        }
        label span { color: #f87171; }
        input, select {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 11px 14px;
            color: white; font-size: 14px;
            font-family: inherit; outline: none;
            transition: border-color 0.2s;
        }
        input::placeholder { color: rgba(255,255,255,0.2); }
        input:focus, select:focus { border-color: rgba(168,85,247,0.6); }
        select option { background: #1a1030; }
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
        .form-actions { display: flex; gap: 12px; margin-top: 8px; }
        .btn-save {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white; border: none;
            padding: 12px 28px; border-radius: 10px;
            font-size: 14px; font-weight: 600;
            font-family: inherit; cursor: pointer;
            box-shadow: 0 4px 15px rgba(124,58,237,0.4);
        }
        .btn-save:hover { opacity: 0.88; }
        .btn-cancel {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            padding: 12px 28px; border-radius: 10px;
            font-size: 14px; font-weight: 600;
            font-family: inherit; cursor: pointer;
            text-decoration: none; display: inline-flex;
            align-items: center;
        }
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
        <a href="teacher_list.php" class="back-link">← Back to Teacher List</a>

        <div class="page-header">
            <div class="page-title">Add New Teacher</div>
            <div class="page-sub">SRS-96 | Developer: Sita</div>
        </div>

        <?php if($success): ?>
            <div class="alert-success">✅ <?php echo $success; ?> <a href="teacher_list.php" style="color:#4ade80;">View List →</a></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="add_teacher.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span>*</span></label>
                        <input type="text" name="first_name" placeholder="e.g. John" required
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name <span>*</span></label>
                        <input type="text" name="last_name" placeholder="e.g. Smith" required
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address <span>*</span></label>
                        <input type="email" name="email" placeholder="teacher@edu.com" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Password <span>*</span></label>
                        <input type="password" name="password" placeholder="Set login password" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="e.g. 9800000000"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" placeholder="e.g. Mathematics"
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Qualification</label>
                        <input type="text" name="qualification" placeholder="e.g. M.Sc Computer Science"
                               value="<?php echo htmlspecialchars($_POST['qualification'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Assign Course</label>
                        <select name="course_id">
                            <option value="0">— Select Course —</option>
                            <?php while($course = mysqli_fetch_assoc($courses)): ?>
                                <option value="<?php echo $course['course_id']; ?>"
                                    <?php echo (($_POST['course_id'] ?? 0) == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Teacher</button>
                    <a href="teacher_list.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>

        <div style="margin-top:16px; font-size:12px; color:rgba(255,255,255,0.2);">
            Developer: Sita | SRS-96 | Edu Team
        </div>
    </div>
</body>
</html>