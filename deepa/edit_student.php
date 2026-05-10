<?php
// edit_student.php - Edit Student
// Developer: Deepa Thapa | SRS-84
// Project: Edu Team - Student Record System

session_start();

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../isha/login.php');
    exit();
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system');
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Student — Edu Team SRS</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f0a1e;min-height:100vh;color:white}
.bg-orb1{position:fixed;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,0.22) 0%,transparent 70%);top:-200px;left:-150px;pointer-events:none}
.bg-orb2{position:fixed;width:450px;height:450px;border-radius:50%;background:radial-gradient(circle,rgba(59,130,246,0.1) 0%,transparent 70%);bottom:-120px;right:-100px;pointer-events:none}
.navbar{position:sticky;top:0;z-index:100;padding:0 40px;height:62px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.07);background:rgba(15,10,30,0.88);backdrop-filter:blur(14px)}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}
.brand-logo{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white}
.brand-text{font-size:14px;font-weight:700;color:white}
.nav-links{display:flex;align-items:center;gap:24px}
.nav-links a{font-size:13px;font-weight:500;color:rgba(255,255,255,0.45);text-decoration:none}
.nav-links a:hover{color:white}
.content{position:relative;z-index:1;max-width:680px;margin:0 auto;padding:36px 32px 60px}
.breadcrumb{margin-bottom:24px}
.breadcrumb a{font-size:13px;color:rgba(255,255,255,0.5);text-decoration:none}
.breadcrumb a:hover{color:#a855f7}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:14px}
.page-title{font-size:24px;font-weight:800;letter-spacing:-0.5px;margin-bottom:4px}
.page-sub{font-size:13px;color:rgba(255,255,255,0.3)}
.student-chip{display:flex;align-items:center;gap:10px;background:rgba(124,58,237,0.15);border:1px solid rgba(168,85,247,0.25);border-radius:12px;padding:10px 16px}
.chip-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white}
.chip-name{font-size:13px;font-weight:600}
.chip-id{font-size:12px;color:rgba(255,255,255,0.35)}
.form-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:28px}
.section-label{font-size:11px;font-weight:600;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.06)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.form-group{display:flex;flex-direction:column;gap:7px}
.form-group.full{grid-column:1/-1}
label{font-size:13px;color:rgba(255,255,255,0.6);font-weight:500}
label .req{color:#f87171;margin-left:2px}
input,select{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:11px 14px;color:white;font-size:13px;font-family:inherit;outline:none;width:100%}
input::placeholder{color:rgba(255,255,255,0.2)}
input:focus,select:focus{border-color:rgba(168,85,247,0.6);box-shadow:0 0 0 3px rgba(124,58,237,0.12)}
input.err,select.err{border-color:rgba(239,68,68,0.6)}
select option{background:#1a0d35}
.field-error{font-size:12px;color:#f87171}
.form-actions{display:flex;gap:12px;margin-top:28px;flex-wrap:wrap;align-items:center}
.btn-save{background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;border:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer}
.btn-save:hover{opacity:0.88}
.btn-cancel{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);padding:12px 24px;border-radius:12px;font-size:14px;font-weight:500;text-decoration:none}
.btn-cancel:hover{background:rgba(255,255,255,0.09);color:white}
.btn-del{background:transparent;border:1px solid rgba(239,68,68,0.35);color:#f87171;padding:12px 20px;border-radius:12px;font-size:14px;cursor:pointer;font-family:inherit;margin-left:auto}
.btn-del:hover{background:rgba(239,68,68,0.12)}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}.form-group.full{grid-column:1}.page-header{flex-direction:column}}
</style>
</head>
<body>
<div class="bg-orb1"></div>
<div class="bg-orb2"></div>
<nav class="navbar">
<a class="brand" href="dashboard.php"><div class="brand-logo">E</div><span class="brand-text">Edu Team – Student Record System</span></a>
<div class="nav-links"><a href="dashboard.php">Dashboard</a><a href="../isha/logout.php">Logout</a></div>
</nav>
<div class="content">
<div class="breadcrumb"><a href="student_list.php">← Back to Students</a></div>
<div class="page-header">
<div><h1 class="page-title">Edit Student</h1><p class="page-sub">Developer: Deepa Thapa | SRS-84</p></div>
<div class="student-chip">
<div class="chip-avatar"><?php echo strtoupper(substr($student['full_name'],0,1)); ?></div>
<div><div class="chip-name"><?php echo htmlspecialchars($student['full_name']); ?></div><div class="chip-id">ID: <?php echo $id; ?></div></div>
</div>
</div>
<div class="form-card">
<div class="section-label">Update Student Details</div>
<form method="POST" action="edit_student.php?id=<?php echo $id; ?>">
<div class="form-grid">
<div class="form-group full">
<label>Full Name <span class="req">*</span></label>
<input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" class="<?php echo isset($errors['full_name'])?'err':''; ?>">
<?php if(isset($errors['full_name'])): ?><span class="field-error"><?php echo $errors['full_name']; ?></span><?php endif; ?>
</div>
<div class="form-group full">
<label>Email Address <span class="req">*</span></label>
<input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" class="<?php echo isset($errors['email'])?'err':''; ?>">
<?php if(isset($errors['email'])): ?><span class="field-error"><?php echo $errors['email']; ?></span><?php endif; ?>
</div>
<div class="form-group">
<label>Course <span class="req">*</span></label>
<select name="course_id" class="<?php echo isset($errors['course_id'])?'err':''; ?>">
<option value="0">Select a course</option>
<?php while($c=mysqli_fetch_assoc($courses)): ?>
<option value="<?php echo $c['course_id']; ?>" <?php echo ($student['course_id']==$c['course_id'])?'selected':''; ?>><?php echo htmlspecialchars($c['course_name']); ?></option>
<?php endwhile; ?>
</select>
<?php if(isset($errors['course_id'])): ?><span class="field-error"><?php echo $errors['course_id']; ?></span><?php endif; ?>
</div>
<div class="form-group">
<label>Enrolled Date <span class="req">*</span></label>
<input type="date" name="enrolled_date" value="<?php echo htmlspecialchars($student['enrolled_date']); ?>" max="<?php echo date('Y-m-d'); ?>" class="<?php echo isset($errors['enrolled_date'])?'err':''; ?>">
<?php if(isset($errors['enrolled_date'])): ?><span class="field-error"><?php echo $errors['enrolled_date']; ?></span><?php endif; ?>
</div>
</div>
<div class="form-actions">
<button type="submit" class="btn-save">✓ Save Changes</button>
<a href="student_list.php" class="btn-cancel">Cancel</a>
<button type="button" class="btn-del" onclick="if(confirm('Delete <?php echo htmlspecialchars($student['full_name'],ENT_QUOTES); ?>?')) window.location='delete_student.php?id=<?php echo $id; ?>'">🗑 Delete</button>
</div>
</form>
</div>
</div>
</body>
</html>