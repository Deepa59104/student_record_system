<?php
// edit_course.php - Edit Course Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system');
if (!$conn) die('DB Error: ' . mysqli_connect_error());

$teacher_row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT first_name, last_name FROM teacher WHERE teacher_id = " . (int)$_SESSION['teacher_id']
));
$teacher_name = $teacher_row
    ? $teacher_row['first_name'] . ' ' . $teacher_row['last_name']
    : ($_SESSION['teacher_name'] ?? 'Isha');
$_SESSION['teacher_name'] = $teacher_name;

$errors  = [];
$success = '';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: course_list.php"); exit(); }

// Fetch existing course data including duration_weeks and description
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT course_name, course_code, description, duration_weeks, is_active FROM course WHERE course_id = $id"
));
if (!$row) { header("Location: course_list.php"); exit(); }

$course_name    = $row['course_name'];
$course_code    = $row['course_code'];
$description    = $row['description'] ?? '';
$duration_weeks = $row['duration_weeks'] ?? '';
$status         = $row['is_active'] ? 'active' : 'inactive';

// Fetch enrolled student count for display using student.course_id
$enroll_row     = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as cnt FROM student WHERE course_id = $id"
));
$enrolled_count = (int)($enroll_row['cnt'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name    = trim($_POST['course_name'] ?? '');
    $course_code    = strtoupper(trim($_POST['course_code'] ?? ''));
    $description    = trim($_POST['description'] ?? '');
    $duration_weeks = trim($_POST['duration_weeks'] ?? '');
    $status         = $_POST['status'] ?? 'active';

    if (empty($course_name)) {
        $errors[] = 'Course name is required.';
    } elseif (strlen($course_name) > 100) {
        $errors[] = 'Course name must be 100 characters or fewer.';
    }

    if (empty($course_code)) {
        $errors[] = 'Course code is required.';
    } elseif (!preg_match('/^[A-Z0-9]{2,10}$/', $course_code)) {
        $errors[] = 'Course code must be 2–10 uppercase letters/digits (e.g. CS101).';
    }

    if ($duration_weeks !== '') {
        if (!is_numeric($duration_weeks) || (int)$duration_weeks < 1 || (int)$duration_weeks > 200) {
            $errors[] = 'Duration must be a number between 1 and 200 weeks.';
        } else {
            $duration_weeks = (int)$duration_weeks;
        }
    } else {
        $duration_weeks = null;
    }

    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status value.';
    }

    // Duplicate check excluding current course
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT course_id FROM course WHERE course_code = ? AND course_id != ?");
        mysqli_stmt_bind_param($check, "si", $course_code, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $errors[] = "Course code \"$course_code\" is already used by another course.";
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $is_active = ($status === 'active') ? 1 : 0;
        $stmt = mysqli_prepare($conn,
            "UPDATE course SET course_name=?, description=?, course_code=?, duration_weeks=?, is_active=? WHERE course_id=?");
        mysqli_stmt_bind_param($stmt, "sssiii", $course_name, $description, $course_code, $duration_weeks, $is_active, $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Course updated successfully!";
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course — Edu Team</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        :root{
            --purple:     #6C3FC5;
            --purple-nav: #3B2069;
            --purple-lt:  #7C4FD5;
            --green:      #059669;
            --red:        #ef4444;
            --blue:       #3b82f6;
            --text-dark:  #1A1033;
            --text-mid:   #5A5475;
            --text-light: #9A93B0;
            --border:     #E2D9F3;
            --white:      #ffffff;
            --bg:         #F0EBFF;
            --input-bg:   #F8F5FF;
        }

        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);min-height:100vh;color:var(--text-dark)}

        /* Navbar */
        nav{display:flex;align-items:center;justify-content:space-between;padding:0 2rem;height:58px;background:var(--purple-nav);box-shadow:0 2px 16px rgba(59,32,105,0.18)}
        .nav-brand{display:flex;align-items:center;gap:12px;text-decoration:none}
        .nav-logo{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#7C4FD5,#5B2EA6);display:grid;place-items:center;font-weight:900;font-size:.9rem;color:white}
        .nav-brand span{font-size:.9rem;font-weight:700;color:white}
        .nav-links a{color:rgba(255,255,255,0.55);text-decoration:none;margin-left:1.5rem;font-size:.875rem;font-weight:500;transition:color .2s}
        .nav-links a:hover,.nav-links a.active{color:white;font-weight:700}

        main{max-width:680px;margin:3rem auto;padding:0 1.5rem}

        /* Welcome banner */
        .welcome-banner{display:flex;align-items:center;gap:1rem;background:linear-gradient(135deg,rgba(108,63,197,0.08),rgba(91,46,166,0.04));border:1px solid rgba(108,63,197,0.15);border-radius:16px;padding:1.1rem 1.5rem;margin-bottom:2.2rem;box-shadow:0 2px 10px rgba(108,63,197,0.06)}
        .avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--purple),var(--purple-lt));display:grid;place-items:center;font-weight:800;font-size:1.1rem;color:white;flex-shrink:0}
        .welcome-banner h2{font-size:1rem;font-weight:700;color:var(--text-dark)}
        .welcome-banner h2 span{color:var(--purple)}
        .welcome-banner p{font-size:.78rem;color:var(--text-light);margin-top:2px}

        /* Page header */
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem}
        .page-header h1{font-size:1.75rem;font-weight:800;color:var(--text-dark)}
        .btn-back{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:10px;background:white;border:1.5px solid var(--border);color:var(--text-mid);font-size:.85rem;text-decoration:none;font-weight:500;transition:border-color .2s,color .2s}
        .btn-back:hover{border-color:var(--purple);color:var(--purple)}

        /* Info badges row */
        .badges-row{display:flex;align-items:center;gap:10px;margin-bottom:1.6rem;flex-wrap:wrap}
        .id-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(108,63,197,0.08);border:1px solid rgba(108,63,197,0.20);color:var(--purple);border-radius:8px;padding:.3rem .75rem;font-size:.8rem;font-weight:600}
        .enroll-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.22);color:var(--green);border-radius:8px;padding:.3rem .75rem;font-size:.8rem;font-weight:600}

        /* Alerts */
        .alert{border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.4rem;font-size:.9rem}
        .alert-error{background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.25);color:var(--red)}
        .alert-success{background:rgba(5,150,105,0.07);border:1px solid rgba(5,150,105,0.25);color:var(--green)}
        .alert ul{padding-left:1.2rem}
        .alert li{margin-top:.35rem}

        /* Card */
        .card{background:white;border:1px solid var(--border);border-radius:18px;padding:2rem;box-shadow:0 4px 20px rgba(108,63,197,0.07)}

        /* Form */
        .form-group{margin-bottom:1.4rem}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.4rem}
        label{display:block;font-size:.78rem;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem}
        input[type="text"],input[type="number"]{width:100%;background:var(--input-bg);border:1.5px solid var(--border);border-radius:10px;padding:.72rem 1rem;color:var(--text-dark);font-size:.92rem;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s}
        input[type="text"]:focus,input[type="number"]:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(108,63,197,0.10);background:white}
        input::placeholder{color:var(--text-light)}
        textarea{width:100%;background:var(--input-bg);border:1.5px solid var(--border);border-radius:10px;padding:.72rem 1rem;color:var(--text-dark);font-size:.92rem;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;resize:vertical;min-height:90px}
        textarea:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(108,63,197,0.10);background:white}
        textarea::placeholder{color:var(--text-light)}
        .hint{font-size:.75rem;color:var(--text-light);margin-top:.4rem}

        /* Duration input with unit label */
        .input-group{position:relative}
        .input-group input{padding-right:70px}
        .input-unit{position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:.78rem;font-weight:600;color:var(--text-light);pointer-events:none;background:var(--input-bg);padding:2px 6px;border-radius:4px}

        /* Enrolled info box - read-only display */
        .enrolled-info{background:rgba(5,150,105,0.06);border:1.5px solid rgba(5,150,105,0.20);border-radius:10px;padding:.72rem 1rem;font-size:.92rem;color:var(--green);font-weight:600;display:flex;align-items:center;gap:.5rem}

        /* Status toggle */
        .status-row{display:flex;gap:.75rem}
        .status-btn{flex:1;padding:.65rem;border-radius:10px;border:1.5px solid var(--border);background:var(--input-bg);color:var(--text-light);font-size:.88rem;font-family:inherit;cursor:pointer;transition:all .2s;text-align:center;user-select:none;font-weight:500}
        .status-btn.active-sel{border-color:var(--green);background:rgba(5,150,105,0.08);color:var(--green);font-weight:700}
        .status-btn.inactive-sel{border-color:var(--red);background:rgba(239,68,68,0.07);color:var(--red);font-weight:700}

        /* Buttons */
        .btn-row{display:flex;gap:.75rem;margin-top:.5rem}
        .btn-submit{flex:1;padding:.85rem;background:linear-gradient(135deg,var(--purple),var(--purple-lt));color:white;border:none;border-radius:12px;font-size:1rem;font-weight:700;font-family:inherit;cursor:pointer;transition:opacity .2s,transform .1s;box-shadow:0 4px 14px rgba(108,63,197,0.22)}
        .btn-submit:hover{opacity:.9;transform:translateY(-1px)}
        .btn-submit:active{transform:scale(.98)}
        .btn-cancel{padding:.85rem 1.5rem;border-radius:12px;background:white;border:1.5px solid var(--border);color:var(--text-mid);font-size:.95rem;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none;display:grid;place-items:center;transition:border-color .2s,color .2s}
        .btn-cancel:hover{border-color:var(--red);color:var(--red)}
    </style>
</head>
<body>

<nav>
    <a class="nav-brand" href="../deepa/dashboard.php">
        <div class="nav-logo">E</div>
        <span>Edu Team – Student Record System</span>
    </a>
    <div class="nav-links">
        <a href="../deepa/dashboard.php">Dashboard</a>
        <a href="course_list.php" class="active">Courses</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main>
    <div class="welcome-banner">
        <div class="avatar"><?= strtoupper(substr($teacher_name, 0, 1)) ?></div>
        <div>
            <h2>👋 Welcome, <span><?= htmlspecialchars($teacher_name) ?>!</span></h2>
            <p>Edit Course — Developer: Isha | SRS-86</p>
        </div>
    </div>

    <div class="page-header">
        <h1>Edit Course</h1>
        <a href="course_list.php" class="btn-back">← Back to Courses</a>
    </div>

    <!-- Info badges: course ID + enrolled students count -->
    <div class="badges-row">
        <div class="id-badge">📘 Course ID: #<?= $id ?></div>
        <div class="enroll-badge">
            👥 <?= $enrolled_count ?> student<?= $enrolled_count !== 1 ? 's' : '' ?> enrolled
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="">

            <!-- Course Name -->
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" id="course_name" name="course_name"
                       placeholder="e.g. Computer Science"
                       value="<?= htmlspecialchars($course_name) ?>"
                       maxlength="100" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Course Description</label>
                <textarea id="description" name="description"
                          placeholder="e.g. This course covers the fundamentals of computer science..."
                          maxlength="500"><?= htmlspecialchars($description) ?></textarea>
                <p class="hint">Optional. Max 500 characters.</p>
            </div>

            <!-- Course Code + Duration in a row -->
            <div class="form-row">
                <div>
                    <label for="course_code">Course Code</label>
                    <input type="text" id="course_code" name="course_code"
                           placeholder="e.g. CS101"
                           value="<?= htmlspecialchars($course_code) ?>"
                           maxlength="10" required>
                    <p class="hint">2–10 uppercase letters/digits.</p>
                </div>
                <div>
                    <label for="duration_weeks">Duration (weeks)</label>
                    <div class="input-group">
                        <input type="number" id="duration_weeks" name="duration_weeks"
                               placeholder="e.g. 12"
                               value="<?= htmlspecialchars($duration_weeks ?? '') ?>"
                               min="1" max="200">
                        <span class="input-unit">wks</span>
                    </div>
                    <p class="hint">Leave blank if not set.</p>
                </div>
            </div>

            <!-- Enrolled Students - read-only info display -->
            <div class="form-group">
                <label>Enrolled Students</label>
                <div class="enrolled-info">
                    👥 <?= $enrolled_count ?> student<?= $enrolled_count !== 1 ? 's' : '' ?> currently enrolled in this course
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label>Status</label>
                <div class="status-row">
                    <div class="status-btn <?= $status === 'active' ? 'active-sel' : '' ?>"
                         onclick="setStatus('active', this)">✅ Active</div>
                    <div class="status-btn <?= $status === 'inactive' ? 'inactive-sel' : '' ?>"
                         onclick="setStatus('inactive', this)">🚫 Inactive</div>
                </div>
                <input type="hidden" name="status" id="statusInput" value="<?= htmlspecialchars($status) ?>">
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-submit">💾 Save Changes</button>
                <a href="course_list.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
function setStatus(val, el) {
    document.getElementById('statusInput').value = val;
    document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active-sel','inactive-sel'));
    el.classList.add(val === 'active' ? 'active-sel' : 'inactive-sel');
}
document.getElementById('course_code').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
</body>
</html>