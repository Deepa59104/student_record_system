<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php'; // your existing DB connection file

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name'] ?? '');
    $course_code = strtoupper(trim($_POST['course_code'] ?? ''));
    $status      = $_POST['status'] ?? 'active';

    // --- Validation ---
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

    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status value.';
    }

    // --- Check duplicate code ---
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
        $check->bind_param("s", $course_code);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors[] = "Course code \"$course_code\" already exists.";
        }
        $check->close();
    }

    // --- INSERT ---
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $course_name, $course_code, $status);
        if ($stmt->execute()) {
            $success = "Course \"$course_name\" added successfully!";
            $course_name = $course_code = '';
            $status = 'active';
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course — Edu Team</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0d0f1a;
            --surface:   #141626;
            --surface2:  #1a1d2e;
            --border:    #252840;
            --accent:    #00c896;
            --accent2:   #00a878;
            --text:      #e8eaf6;
            --muted:     #7b82a8;
            --error:     #ff5f7e;
            --warn-bg:   rgba(255,95,126,.12);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }

        /* ── NAV ── */
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 56px;
            background: #0b0d19;
            border-bottom: 1px solid var(--border);
        }
        .nav-brand { display: flex; align-items: center; gap: .75rem; font-weight: 700; font-size: .95rem; }
        .nav-logo  { width: 32px; height: 32px; border-radius: 8px; background: #7c3aed; display: grid; place-items: center; font-weight: 900; font-size: .85rem; }
        .nav-links a { color: var(--muted); text-decoration: none; margin-left: 1.5rem; font-size: .875rem; transition: color .2s; }
        .nav-links a:hover, .nav-links a.active { color: var(--accent); }

        /* ── MAIN ── */
        main { max-width: 680px; margin: 3rem auto; padding: 0 1.5rem; }

        /* Welcome banner */
        .welcome-banner {
            display: flex; align-items: center; gap: 1rem;
            background: linear-gradient(135deg, #0f2d25 0%, #0d1f2d 100%);
            border: 1px solid #1e4035;
            border-radius: 14px;
            padding: 1.1rem 1.5rem;
            margin-bottom: 2.5rem;
        }
        .avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--accent); display: grid; place-items: center; font-weight: 800; font-size: 1.1rem; color: #0d0f1a; flex-shrink: 0; }
        .welcome-banner h2 { font-size: 1rem; font-weight: 700; }
        .welcome-banner p  { font-size: .8rem; color: var(--muted); margin-top: 2px; }

        /* Page header */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 800; }
        .btn-back {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem 1rem; border-radius: 8px;
            background: var(--surface2); border: 1px solid var(--border);
            color: var(--muted); font-size: .85rem; text-decoration: none;
            transition: border-color .2s, color .2s;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }

        /* Alerts */
        .alert {
            border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: .9rem;
        }
        .alert-error {
            background: var(--warn-bg); border: 1px solid rgba(255,95,126,.35); color: var(--error);
        }
        .alert-success {
            background: rgba(0,200,150,.1); border: 1px solid rgba(0,200,150,.35); color: var(--accent);
        }
        .alert ul { padding-left: 1.2rem; }
        .alert li { margin-top: .35rem; }

        /* Form card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
        }

        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-size: .82rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .5rem; }

        input[type="text"],
        select {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .75rem 1rem;
            color: var(--text);
            font-size: .95rem;
            outline: none;
            transition: border-color .2s;
        }
        input[type="text"]:focus,
        select:focus { border-color: var(--accent); }
        input::placeholder { color: var(--muted); }

        select option { background: var(--surface2); }

        .hint { font-size: .78rem; color: var(--muted); margin-top: .4rem; }

        /* Status toggle row */
        .status-row { display: flex; gap: 1rem; }
        .status-btn {
            flex: 1; padding: .65rem; border-radius: 10px; border: 1px solid var(--border);
            background: var(--surface2); color: var(--muted); font-size: .9rem;
            cursor: pointer; transition: all .2s; text-align: center; user-select: none;
        }
        .status-btn.active-sel   { border-color: var(--accent); background: rgba(0,200,150,.12); color: var(--accent); font-weight: 600; }
        .status-btn.inactive-sel { border-color: var(--error);  background: rgba(255,95,126,.1); color: var(--error);  font-weight: 600; }
        input[name="status"] { display: none; }

        .btn-submit {
            width: 100%; padding: .85rem;
            background: var(--accent); color: #0d0f1a;
            border: none; border-radius: 10px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s, transform .1s;
            margin-top: .5rem;
        }
        .btn-submit:hover  { background: var(--accent2); }
        .btn-submit:active { transform: scale(.98); }
    </style>
</head>
<body>

<nav>
    <div class="nav-brand">
        <div class="nav-logo">E</div>
        Edu Team – Student Record System
    </div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="course_list.php" class="active">Courses</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main>
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="avatar"><?= strtoupper(substr($_SESSION['teacher_name'] ?? 'A', 0, 1)) ?></div>
        <div>
            <h2>👋 Welcome, <?= htmlspecialchars($_SESSION['teacher_name'] ?? 'Admin') ?>!</h2>
            <p>Add Course — Developer: Isha | SRS-86</p>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1>Add New Course</h1>
        <a href="course_list.php" class="btn-back">← Back to Courses</a>
    </div>

    <!-- Alerts -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="">

            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" id="course_name" name="course_name"
                       placeholder="e.g. Computer Science"
                       value="<?= htmlspecialchars($course_name ?? '') ?>"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="course_code">Course Code</label>
                <input type="text" id="course_code" name="course_code"
                       placeholder="e.g. CS101"
                       value="<?= htmlspecialchars($course_code ?? '') ?>"
                       maxlength="10" required>
                <p class="hint">2–10 uppercase letters and digits only. Will be auto-uppercased.</p>
            </div>

            <div class="form-group">
                <label>Status</label>
                <div class="status-row">
                    <div class="status-btn <?= ($status ?? 'active') === 'active' ? 'active-sel' : '' ?>"
                         onclick="setStatus('active', this)">✅ Active</div>
                    <div class="status-btn <?= ($status ?? '') === 'inactive' ? 'inactive-sel' : '' ?>"
                         onclick="setStatus('inactive', this)">🚫 Inactive</div>
                </div>
                <input type="hidden" name="status" id="statusInput"
                       value="<?= htmlspecialchars($status ?? 'active') ?>">
            </div>

            <button type="submit" class="btn-submit">+ Add Course</button>
        </form>
    </div>
</main>

<script>
function setStatus(val, el) {
    document.getElementById('statusInput').value = val;
    document.querySelectorAll('.status-btn').forEach(b => {
        b.classList.remove('active-sel', 'inactive-sel');
    });
    el.classList.add(val === 'active' ? 'active-sel' : 'inactive-sel');
}

// Auto-uppercase course code as user types
document.getElementById('course_code').addEventListener('input', function() {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>

</body>
</html>