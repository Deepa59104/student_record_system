<?php
// add_student.php - Add New Student
// Developer: Deepa Thapa | SRS-84
// Project: Edu Team - Student Record System

session_start();

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../isha/login.php');
    exit();
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system');
if(!$conn) die('Connection failed: ' . mysqli_connect_error());

$errors = [];
$old    = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name']     = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $old['email']         = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $old['course_id']     = (int)($_POST['course_id'] ?? 0);
    $old['enrolled_date'] = trim(mysqli_real_escape_string($conn, $_POST['enrolled_date'] ?? ''));

    if(empty($old['full_name']))    $errors['full_name']     = 'Full name is required';
    if(empty($old['email']))        $errors['email']          = 'Email is required';
    elseif(!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address';
    if($old['course_id'] <= 0)      $errors['course_id']     = 'Please select a course';
    if(empty($old['enrolled_date'])) $errors['enrolled_date'] = 'Enrolled date is required';

    if(empty($errors['email'])) {
        $check = mysqli_query($conn, "SELECT student_id FROM student WHERE email='{$old['email']}'");
        if(mysqli_num_rows($check) > 0) $errors['email'] = 'Email already registered';
    }

    if(empty($errors)) {
        $q = "INSERT INTO student (full_name, email, teacher_id, course_id, enrolled_date)
              VALUES ('{$old['full_name']}', '{$old['email']}', {$_SESSION['teacher_id']}, {$old['course_id']}, '{$old['enrolled_date']}')";
        if(mysqli_query($conn, $q)) {
            header('Location: student_list.php?success=Student added successfully!');
            exit();
        } else {
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$courses = mysqli_query($conn, "SELECT course_id, course_name FROM course ORDER BY course_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Student — Edu Team SRS</title>
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
.page-title{font-size:24px;font-weight:800;letter-spacing:-0.5px;margin-bottom:4px}
.page-sub{font-size:13px;color:rgba(255,255,255,0.3);margin-bottom:28px}
.alert-error-box{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:13px}
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
input.is-error,select.is-error{border-color:rgba(239,68,68,0.6)}
select option{background:#1a0d35}
.field-error{font-size:12px;color:#f87171}
.form-actions{display:flex;gap:12px;margin-top:28px}
.btn-add{background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;border:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer}
.btn-add:hover{opacity:0.88}
.btn-cancel{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);padding:12px 24px;border-radius:12px;font-size:14px;font-weight:500;text-decoration:none}
.btn-cancel:hover{background:rgba(255,255,255,0.09);color:white}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}.form-group.full{grid-column:1}}
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
<h1 class="page-title">Add New Student</h1>
<p class="page-sub">Developer: Deepa Thapa | SRS-84</p>
<?php if(isset($errors['general'])): ?><div class="alert-error-box"><?php echo $errors['general']; ?></div><?php endif; ?>
<div class="form-card">
<div class="section-label">Student Details</div>
<form method="POST" action="add_student.php">
<div class="form-grid">
<div class="form-group full">
<label>Full Name <span class="req">*</span></label>
<input type="text" name="full_name" placeholder="e.g. John Smith" value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>" class="<?php echo isset($errors['full_name'])?'is-error':''; ?>">
<?php if(isset($errors['full_name'])): ?><span class="field-error"><?php echo $errors['full_name']; ?></span><?php endif; ?>
</div>
<div class="form-group full">
<label>Email Address <span class="req">*</span></label>
<input type="email" name="email" placeholder="student@email.com" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" class="<?php echo isset($errors['email'])?'is-error':''; ?>">
<?php if(isset($errors['email'])): ?><span class="field-error"><?php echo $errors['email']; ?></span><?php endif; ?>
</div>
<div class="form-group">
<label>Course <span class="req">*</span></label>
<select name="course_id" class="<?php echo isset($errors['course_id'])?'is-error':''; ?>">
<option value="">Select a course</option>
<?php while($c=mysqli_fetch_assoc($courses)): ?>
<option value="<?php echo $c['course_id']; ?>" <?php echo (isset($old['course_id'])&&$old['course_id']==$c['course_id'])?'selected':''; ?>><?php echo htmlspecialchars($c['course_name']); ?></option>
<?php endwhile; ?>
</select>
<?php if(isset($errors['course_id'])): ?><span class="field-error"><?php echo $errors['course_id']; ?></span><?php endif; ?>
</div>
<div class="form-group">
<label>Enrolled Date <span class="req">*</span></label>
<input type="date" name="enrolled_date" value="<?php echo htmlspecialchars($old['enrolled_date'] ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>" class="<?php echo isset($errors['enrolled_date'])?'is-error':''; ?>">
<?php if(isset($errors['enrolled_date'])): ?><span class="field-error"><?php echo $errors['enrolled_date']; ?></span><?php endif; ?>
</div>
</div>
<div class="form-actions">
<button type="submit" class="btn-add">+ Add Student</button>
<a href="student_list.php" class="btn-cancel">Cancel</a>
</div>
</form>
</div>
</div>
</body>
</html>