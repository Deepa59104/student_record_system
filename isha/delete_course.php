<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: course_list.php");
    exit();
}

// Fetch course to show name in confirmation
$fetch = $conn->prepare("SELECT course_name, course_code FROM courses WHERE id = ?");
$fetch->bind_param("i", $id);
$fetch->execute();
$fetch->bind_result($course_name, $course_code);
if (!$fetch->fetch()) {
    $fetch->close();
    header("Location: course_list.php");
    exit();
}
$fetch->close();

$error = '';

// Handle confirmed DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        // Redirect with success message via session flash
        $_SESSION['flash_success'] = "Course \"$course_name\" ($course_code) deleted successfully.";
        header("Location: course_list.php");
        exit();
    } else {
        $error = 'Database error: ' . $conn->error;
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Course — Edu Team</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #0d0f1a;
            --surface: #141626;
            --surface2:#1a1d2e;
            --border:  #252840;
            --accent:  #00c896;
            --text:    #e8eaf6;
            --muted:   #7b82a8;
            --error:   #ff5f7e;
            --warn-bg: rgba(255,95,126,.12);
        }

        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }

        nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; height: 56px;
            background: #0b0d19; border-bottom: 1px solid var(--border);
        }
        .nav-brand { display: flex; align-items: center; gap: .75rem; font-weight: 700; font-size: .95rem; }
        .nav-logo  { width: 32px; height: 32px; border-radius: 8px; background: #7c3aed; display: grid; place-items: center; font-weight: 900; font-size: .85rem; }
        .nav-links a { color: var(--muted); text-decoration: none; margin-left: 1.5rem; font-size: .875rem; transition: color .2s; }
        .nav-links a:hover { color: var(--accent); }

        main {
            display: flex; align-items: center; justify-content: center;
            min-height: calc(100vh - 56px); padding: 2rem;
        }

        .confirm-card {
            background: var(--surface);
            border: 1px solid rgba(255,95,126,.3);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 480px; width: 100%;
            text-align: center;
            box-shadow: 0 0 40px rgba(255,95,126,.07);
        }

        .warning-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(255,95,126,.12);
            border: 2px solid rgba(255,95,126,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1.5rem;
        }

        .confirm-card h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: .5rem; color: var(--error); }
        .confirm-card p  { color: var(--muted); font-size: .95rem; line-height: 1.6; margin-bottom: 1.75rem; }

        .course-info {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 12px; padding: 1rem 1.25rem;
            margin-bottom: 2rem; text-align: left;
        }
        .course-info .row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; }
        .course-info .row:not(:last-child) { border-bottom: 1px solid var(--border); }
        .course-info .lbl { font-size: .78rem; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
        .course-info .val { font-size: .95rem; font-weight: 600; }
        .code-badge {
            background: rgba(0,200,150,.12); color: var(--accent);
            border: 1px solid rgba(0,200,150,.25);
            border-radius: 6px; padding: .2rem .6rem; font-size: .82rem; font-weight: 700;
        }

        .alert-error {
            background: var(--warn-bg); border: 1px solid rgba(255,95,126,.35);
            color: var(--error); border-radius: 10px; padding: .85rem 1.1rem;
            margin-bottom: 1.5rem; font-size: .9rem;
        }

        .btn-row { display: flex; gap: 1rem; }
        .btn-delete {
            flex: 1; padding: .85rem;
            background: var(--error); color: #fff;
            border: none; border-radius: 10px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s, transform .1s;
        }
        .btn-delete:hover  { background: #e54066; }
        .btn-delete:active { transform: scale(.98); }
        .btn-cancel {
            flex: 1; padding: .85rem;
            background: var(--surface2); border: 1px solid var(--border);
            color: var(--muted); font-size: 1rem; font-weight: 600;
            border-radius: 10px; cursor: pointer; text-decoration: none;
            display: grid; place-items: center;
            transition: border-color .2s, color .2s;
        }
        .btn-cancel:hover { border-color: var(--accent); color: var(--accent); }

        footer { text-align: center; color: var(--muted); font-size: .75rem; margin-top: 1.5rem; }
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
        <a href="course_list.php">Courses</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main>
    <div>
        <div class="confirm-card">
            <div class="warning-icon">🗑️</div>
            <h1>Delete Course?</h1>
            <p>This action is <strong>permanent</strong> and cannot be undone. The course will be removed from all records.</p>

            <?php if ($error): ?>
                <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="course-info">
                <div class="row">
                    <span class="lbl">Course Name</span>
                    <span class="val"><?= htmlspecialchars($course_name) ?></span>
                </div>
                <div class="row">
                    <span class="lbl">Course Code</span>
                    <span class="code-badge"><?= htmlspecialchars($course_code) ?></span>
                </div>
                <div class="row">
                    <span class="lbl">Course ID</span>
                    <span class="val">#<?= $id ?></span>
                </div>
            </div>

            <form method="POST" action="">
                <div class="btn-row">
                    <button type="submit" name="confirm_delete" class="btn-delete">🗑️ Yes, Delete</button>
                    <a href="course_list.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>

        <footer>Developer: Isha | SRS-86 | Edu Team</footer>
    </div>
</main>

</body>
</html>