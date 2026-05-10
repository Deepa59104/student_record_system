<?php
session_start();
$_SESSION['teacher_id']   = 1;
$_SESSION['teacher_name'] = 'Deepa Thapa';

$conn = mysqli_connect('localhost', 'root', '', 'student_record_system');
if(!$conn) die('Connection failed: ' . mysqli_connect_error());

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0) { header('Location: student_list.php?error=Invalid student'); exit(); }

$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM student WHERE student_id = $id"));
if(!$student) { header('Location: student_list.php?error=Student not found'); exit(); }

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name     = trim(mysqli_real_escape_string($conn, $_POST['full_name']));
    $email         = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $course_id     = (int)$_POST['course_id'];
    $enrolled_date = trim(mysqli_real_escape_string($conn, $_POST['enrolled_date']));

    if(empty($full_name))     $errors['full_name']     = 'Full name is required';
    if(empty($email))         $errors['email']          = 'Email is required';
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email';
    if($course_id <= 0)       $errors['course_id']     = 'Please select a course';
    if(empty($enrolled_date)) $errors['enrolled_date'] = 'Date is required';

    if(empty($errors)) {
        mysqli_query($conn, "UPDATE student SET full_name='$full_name', email='$email', course_id=$course_id, enrolled_date='$enrolled_date' WHERE student_id=$id");
        header('Location: student_list.php?success=Student updated successfully!');
        exit();
    }

    $student['full_name']     = $_POST['full_name'];
    $student['email']         = $_POST['email'];
    $student['course_id']     = $_POST['course_id'];
    $student['enrolled_date'] = $_POST['enrolled_date'];
}

$courses = mysqli_query($conn, "SELECT * FROM course");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f0a1e;min-height:100vh;color:white}
        .navbar{padding:0 40px;height:62px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.07);background:rgba(15,10,30,0.88)}
        .brand{display:flex;align-items:center;gap:12px;text-decoration:none}
        .brand-logo{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white}
        .brand-text{font-size:14px;font-weight:700;color:white}
        .nav-links{display:flex;gap:24px}
        .nav-links a{font-size:13px;color:rgba(255,255,255,0.45);text-decoration:none}
        .nav-links a:hover{color:white}
        .content{max-width:680px;margin:0 auto;padding:36px 32px}
        .back a{font-size:13px;color:rgba(255,255,255,0.5);text-decoration:none}
        .back{margin-bottom:24px}
        h1{font-size:24px;font-weight:800;margin-bottom:4px}
        .sub{font-size:13px;color:rgba(255,255,255,0.3);margin-bottom:28px}
        .card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:28px}
        .form-group{margin-bottom:18px}
        label{display:block;font-size:13px;color:rgba(255,255,255,0.6);margin-bottom:7px}
        input,select{width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:11px 14px;color:white;font-size:13px;font-family:inherit;outline:none}
        input:focus,select:focus{border-color:rgba(168,85,247,0.6)}
        select option{background:#1a0d35}
        .err-msg{font-size:12px;color:#f87171;margin-top:4px}
        .actions{display:flex;gap:12px;margin-top:24px;align-items:center}
        .btn-save{background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;border:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer}
        .btn-cancel{color:rgba(255,255,255,0.6);padding:12px 24px;border-radius:12px;font-size:14px;text-decoration:none;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)}
        .btn-del{margin-left:auto;background:transparent;border:1px solid rgba(239,68,68,0.35);color:#f87171;padding:12px 20px;border-radius:12px;font-size:14px;cursor:pointer;font-family:inherit}
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="brand" href="dashboard.php">
            <div class="brand-logo">E</div>
            <span class="brand-text">Edu Team – Student Record System</span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="#">Logout</a>
        </div>
    </nav>
    <div class="content">
        <div class="back"><a href="student_list.php">← Back to Students</a></div>
        <h1>Edit Student</h1>
        <div class="sub">Developer: Deepa Thapa | SRS-84</div>
        <div class="card">
            <form method="POST" action="edit_student.php?id=<?php echo $id; ?>">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>">
                    <?php if(isset($errors['full_name'])): ?><div class="err-msg"><?php echo $errors['full_name']; ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">
                    <?php if(isset($errors['email'])): ?><div class="err-msg"><?php echo $errors['email']; ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <select name="course_id">
                        <option value="0">Select a course</option>
                        <?php while($c = mysqli_fetch_assoc($courses)): ?>
                            <option value="<?php echo $c['course_id']; ?>" <?php echo ($student['course_id'] == $c['course_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if(isset($errors['course_id'])): ?><div class="err-msg"><?php echo $errors['course_id']; ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Enrolled Date *</label>
                    <input type="date" name="enrolled_date" value="<?php echo htmlspecialchars($student['enrolled_date']); ?>" max="<?php echo date('Y-m-d'); ?>">
                    <?php if(isset($errors['enrolled_date'])): ?><div class="err-msg"><?php echo $errors['enrolled_date']; ?></div><?php endif; ?>
                </div>
                <div class="actions">
                    <button type="submit" class="btn-save">✓ Save Changes</button>
                    <a href="student_list.php" class="btn-cancel">Cancel</a>
                    <button type="button" class="btn-del" onclick="if(confirm('Delete this student?')) window.location='delete_student.php?id=<?php echo $id; ?>'">🗑 Delete</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>