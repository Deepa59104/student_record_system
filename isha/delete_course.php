<?php
// delete_course.php - Delete Course Confirmation Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

// Start the session to access logged in teacher data
session_start();

// Redirect to login page if teacher is not logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

// Include shared database connection file
require_once '../db.php';

// Get the course ID from the URL and convert to integer for safety
$id = intval($_GET['id'] ?? 0);

// Redirect back to course list if no valid ID is provided
if ($id <= 0) {
    header("Location: course_list.php");
    exit();
}

// Fetch the course name and code to display in the confirmation page
$fetch = $conn->prepare("SELECT course_name, course_code FROM courses WHERE id = ?");
$fetch->bind_param("i", $id);
$fetch->execute();
$fetch->bind_result($course_name, $course_code);

// If course is not found redirect back to course list
if (!$fetch->fetch()) {
    $fetch->close();
    header("Location: course_list.php");
    exit();
}
$fetch->close();

// Initialise error variable
$error = '';

// Handle the confirmed DELETE when user clicks the delete button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {

    // Prepare DELETE SQL statement to remove course by ID
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Execute the DELETE and check if successful
    if ($stmt->execute()) {
        $stmt->close();

        // Store success message in session flash to show on course list page
        $_SESSION['flash_success'] = "Course \"$course_name\" ($course_code) deleted successfully.";

        // Redirect back to course list after successful delete
        header("Location: course_list.php");
        exit();
    } else {
        // Show database error if DELETE fails
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
        /* Reset default browser styles */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* CSS variables for consistent colours */
        :root {
            --bg:      #0d0f1a;   /* Dark background */
            --surface: #141626;   /* Card background */
            --surface2:#1a1d2e;   /* Inner box background */
            --border:  #252840;   /* Border colour */
            --accent:  #00c896;   /* Green accent */
            --text:    #e8eaf6;   /* Main text */
            --muted:   #7b82a8;   /* Muted grey text */
            --error:   #ff5f7e;   /* Red error/delete colour */
            --warn-bg: rgba(255,95,126,.12); /* Error background */
        }

        /* Main body - dark background full height */
        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }

        /* Navigation bar */
        nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; height: 56px;
            background: #0b0d19; border-bottom: 1px solid var(--border);
        }

        /* Brand name and logo */
        .nav-brand { display: flex; align-items: center; gap: .75rem; font-weight: 700; font-size: .95rem; }

        /* Purple square logo */
        .nav-logo  { width: 32px; height: 32px; border-radius: 8px; background: #7c3aed; display: grid; place-items: center; font-weight: 900; font-size: .85rem; }

        /* Navigation links */
        .nav-links a { color: var(--muted); text-decoration: none; margin-left: 1.5rem; font-size: .875rem; transition: color .2s; }
        .nav-links a:hover { color: var(--accent); }

        /* Main content area - centres the confirmation card */
        main {
            display: flex; align-items: center; justify-content: center;
            min-height: calc(100vh - 56px); padding: 2rem;
        }

        /* Confirmation card - red border to warn user */
        .confirm-card {
            background: var(--surface);
            border: 1px solid rgba(255,95,126,.3);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 480px; width: 100%;
            text-align: center;
            box-shadow: 0 0 40px rgba(255,95,126,.07);
        }

        /* Warning icon circle - red background */
        .warning-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(255,95,126,.12);
            border: 2px solid rgba(255,95,126,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1.5rem;
        }

        /* Card heading and description text */
        .confirm-card h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: .5rem; color: var(--error); }
        .confirm-card p  { color: var(--muted); font-size: .95rem; line-height: 1.6; margin-bottom: 1.75rem; }

        /* Course info box - shows course name, code and ID */
        .course-info {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 12px; padding: 1rem 1.25rem;
            margin-bottom: 2rem; text-align: left;
        }

        /* Each row in the course info box */
        .course-info .row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; }

        /* Divider between rows */
        .course-info .row:not(:last-child) { border-bottom: 1px solid var(--border); }

        /* Label text - small uppercase */
        .course-info .lbl { font-size: .78rem; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }

        /* Value text - bold */
        .course-info .val { font-size: .95rem; font-weight: 600; }

        /* Course code green badge */
        .code-badge {
            background: rgba(0,200,150,.12); color: var(--accent);
            border: 1px solid rgba(0,200,150,.25);
            border-radius: 6px; padding: .2rem .6rem; font-size: .82rem; font-weight: 700;
        }

        /* Error alert box */
        .alert-error {
            background: var(--warn-bg); border: 1px solid rgba(255,95,126,.35);
            color: var(--error); border-radius: 10px; padding: .85rem 1.1rem;
            margin-bottom: 1.5rem; font-size: .9rem;
        }

        /* Row containing delete and cancel buttons */
        .btn-row { display: flex; gap: 1rem; }

        /* Delete confirmation button - red */
        .btn-delete {
            flex: 1; padding: .85rem;
            background: var(--error); color: #fff;
            border: none; border-radius: 10px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s, transform .1s;
        }
        .btn-delete:hover  { background: #e54066; }

        /* Slight shrink effect on button click */
        .btn-delete:active { transform: scale(.98); }

        /* Cancel button - takes user back to course list */
        .btn-cancel {
            flex: 1; padding: .85rem;
            background: var(--surface2); border: 1px solid var(--border);
            color: var(--muted); font-size: 1rem; font-weight: 600;
            border-radius: 10px; cursor: pointer; text-decoration: none;
            display: grid; place-items: center;
            transition: border-color .2s, color .2s;
        }
        .btn-cancel:hover { border-color: var(--accent); color: var(--accent); }

        /* Footer text at bottom of card */
        footer { text-align: center; color: var(--muted); font-size: .75rem; margin-top: 1.5rem; }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav>
    <div class="nav-brand">
        <!-- Purple logo square -->
        <div class="nav-logo">E</div>
        Edu Team – Student Record System
    </div>
    <!-- Navigation links -->
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="course_list.php">Courses</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<!-- Main content - centred confirmation card -->
<main>
    <div>
        <div class="confirm-card">

            <!-- Warning icon -->
            <div class="warning-icon">🗑️</div>

            <!-- Heading and warning message -->
            <h1>Delete Course?</h1>
            <p>This action is <strong>permanent</strong> and cannot be undone. The course will be removed from all records.</p>

            <!-- Error alert - shown if database delete fails -->
            <?php if ($error): ?>
                <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Course info box - shows details of course being deleted -->
            <div class="course-info">
                <!-- Course name row -->
                <div class="row">
                    <span class="lbl">Course Name</span>
                    <span class="val"><?= htmlspecialchars($course_name) ?></span>
                </div>
                <!-- Course code row -->
                <div class="row">
                    <span class="lbl">Course Code</span>
                    <span class="code-badge"><?= htmlspecialchars($course_code) ?></span>
                </div>
                <!-- Course ID row -->
                <div class="row">
                    <span class="lbl">Course ID</span>
                    <span class="val">#<?= $id ?></span>
                </div>
            </div>

            <!-- Confirmation form - POST method sends confirm_delete -->
            <form method="POST" action="">
                <div class="btn-row">
                    <!-- Delete button - submits the form to delete the course -->
                    <button type="submit" name="confirm_delete" class="btn-delete">🗑️ Yes, Delete</button>

                    <!-- Cancel button - goes back to course list without deleting -->
                    <a href="course_list.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Footer with developer info -->
        <footer>Developer: Isha | SRS-86 | Edu Team</footer>
    </div>
</main>

</body>
</html>