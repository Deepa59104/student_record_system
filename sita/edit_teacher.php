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
$error = '';
$id = $_GET['id'] ?? '';

if(!$id) {
    header("Location: teacher_list.php");
    exit();
}

$data = $teacher->getTeacherById($id);
if(!$data) {
    header("Location: teacher_list.php");
    exit();
}

$courses = $teacher->getAllCourses();

if(isset($_POST['update'])) {
    $updateData = [
        'first_name'    => trim($_POST['first_name'] ?? ''),
        'last_name'     => trim($_POST['last_name'] ?? ''),
        'email'         => trim($_POST['email'] ?? ''),
        'subject'       => trim($_POST['subject'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'qualification' => trim($_POST['qualification'] ?? ''),
        'course_id'     => $_POST['course_id'] ?? null,
        'is_active'     => $_POST['is_active'] ?? 1,
    ];

    $result = $teacher->updateTeacher($id, $updateData);
    if($result) {
        header("Location: teacher_list.php?success=updated");
        exit();
    } else {
        $error = "Failed to update teacher.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Teacher</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{ margin:0; padding:0; box-sizing:border-box; }
body{ font-family:'Plus Jakarta Sans',sans-serif; background:#f5f3ff; color:#1e1b4b; min-height:100vh; }

.sidebar{ position:fixed; left:0; top:0; width:220px; height:100vh; background:#ffffff; border-right:1px solid #ede9fe; padding:32px 16px; display:flex; flex-direction:column; gap:6px; }
.sidebar-title{ font-size:16px; font-weight:800; color:#1e1b4b; padding:0 14px 24px; }
.nav-item{ display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:10px; color:#6b7280; text-decoration:none; font-size:14px; font-weight:600; transition:all 0.2s; }
.nav-item:hover{ background:#f3effe; color:#7c3aed; }
.nav-item.active{ background:#ede9fe; color:#7c3aed; }
.logout{ margin-top:auto; color:#6b7280; text-decoration:none; font-size:14px; font-weight:600; padding:12px 14px; border-radius:10px; display:block; transition:all 0.2s; }
.logout:hover{ background:#f3effe; color:#7c3aed; }

.main{ margin-left:220px; padding:36px 40px; }
.back-link{ display:inline-flex; align-items:center; gap:6px; color:#7c3aed; text-decoration:none; font-size:13px; font-weight:600; margin-bottom:12px; }
.back-link:hover{ text-decoration:underline; }
.page-title{ font-size:28px; font-weight:800; color:#1e1b4b; margin-bottom:6px; }
.sub{ color:#9b8bb8; font-size:14px; margin-bottom:28px; }

.form-card{ background:#ffffff; border:1px solid #ddd6fe; border-radius:18px; padding:36px; max-width:740px; box-shadow:0 2px 10px rgba(0,0,0,.04); }
.form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.form-group{ display:flex; flex-direction:column; gap:6px; }
.form-group.full{ grid-column:1/-1; }

label{ font-size:13px; font-weight:700; color:#1e1b4b; }
.required{ color:#ef4444; }

input, select{
    padding:13px; border:1.5px solid #ddd6fe; border-radius:10px;
    font-size:14px; color:#111827; font-family:inherit;
    background:#fff; transition:border-color 0.2s; width:100%;
}
input:focus, select:focus{ outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,.08); }

.error-msg{ color:#ef4444; font-size:12px; margin-top:3px; }

.btn-row{ display:flex; gap:12px; margin-top:26px; }
.btn-save{ background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff; border:none; padding:13px 32px; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit; transition:opacity 0.2s; }
.btn-save:hover{ opacity:0.88; }
.btn-cancel{ background:#f3f4f6; color:#374151; padding:13px 24px; border-radius:10px; text-decoration:none; font-weight:700; font-size:15px; border:1.5px solid #e5e7eb; transition:background 0.2s; }
.btn-cancel:hover{ background:#e5e7eb; }

.alert-error{ background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px; border:1px solid #fecaca; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-title">🏫 Edu Team</div>
    <a href="../deepa/dashboard.php" class="nav-item">🏠 Main Dashboard</a>
    <a href="teacher_list.php" class="nav-item active">👩‍🏫 Teacher Dashboard</a>
    <a href="view_students.php" class="nav-item">👨‍🎓 Students</a>
    <a href="teacher_logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <a href="teacher_list.php" class="back-link">← Back to Teacher List</a>
    <div class="page-title">Edit Teacher</div>
    <div class="sub">Update the teacher details below.</div>

    <?php if($error): ?>
        <div class="alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-grid">

                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" id="first_name"
                        value="<?php echo htmlspecialchars($data['first_name']); ?>" required>
                    <div class="error-msg" id="first_name_err"></div>
                </div>

                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" id="last_name"
                        value="<?php echo htmlspecialchars($data['last_name']); ?>" required>
                    <div class="error-msg" id="last_name_err"></div>
                </div>

                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email"
                        value="<?php echo htmlspecialchars($data['email']); ?>" required>
                    <div class="error-msg" id="email_err"></div>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject"
                        value="<?php echo htmlspecialchars($data['subject'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="phone"
                        value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>">
                    <div class="error-msg" id="phone_err"></div>
                </div>

                <div class="form-group">
                    <label>Qualification</label>
                    <input type="text" name="qualification"
                        value="<?php echo htmlspecialchars($data['qualification'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id">
                        <option value="">Not Assigned</option>
                        <?php
                        if($courses):
                            while($c = mysqli_fetch_assoc($courses)):
                        ?>
                        <option value="<?php echo $c['course_id']; ?>"
                            <?php echo (isset($data['course_id']) && $data['course_id'] == $c['course_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['course_name']); ?>
                        </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active">
                        <option value="1" <?php echo ($data['is_active'] == 1) ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($data['is_active'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

            </div>

            <div class="btn-row">
                <button type="submit" name="update" class="btn-save">Update Teacher</button>
                <a href="teacher_list.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateForm() {
    let valid = true;

    const firstName = document.getElementById('first_name').value.trim();
    if(!firstName) {
        document.getElementById('first_name_err').textContent = 'First name is required.';
        valid = false;
    } else {
        document.getElementById('first_name_err').textContent = '';
    }

    const lastName = document.getElementById('last_name').value.trim();
    if(!lastName) {
        document.getElementById('last_name_err').textContent = 'Last name is required.';
        valid = false;
    } else {
        document.getElementById('last_name_err').textContent = '';
    }

    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRegex.test(email)) {
        document.getElementById('email_err').textContent = 'Please enter a valid email address.';
        valid = false;
    } else {
        document.getElementById('email_err').textContent = '';
    }

    const phone = document.getElementById('phone').value.trim();
    if(phone && !/^\d+$/.test(phone)) {
        document.getElementById('phone_err').textContent = 'Phone number must contain numbers only.';
        valid = false;
    } else {
        document.getElementById('phone_err').textContent = '';
    }

    return valid;
}
</script>
</body>
</html>