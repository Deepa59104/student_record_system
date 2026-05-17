<?php
// Start session and check if user is logged in
session_start();
if(!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Connect to the database
include '../db.php';

// Check if the form was submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form values and sanitize student name
    $student_name    = mysqli_real_escape_string($conn, $_POST['student_name']);
    $course          = mysqli_real_escape_string($conn, $_POST['course']);
    $teacher_id      = $_POST['teacher_id'];
    $attendance_date = $_POST['attendance_date'];
    $status          = $_POST['status'];

    // Check if student already exists in the database
    $s = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT student_id FROM students WHERE student_name='$student_name'"));

    if(!$s) {
        // Student not found — create them automatically with the given course
        mysqli_query($conn,
            "INSERT INTO students (student_name, course, email)
             VALUES ('$student_name', '$course', '')");
        // Get the new student's ID
        $student_id = mysqli_insert_id($conn);
    } else {
        // Student exists — use their existing ID
        $student_id = $s['student_id'];
        // Update course if it was previously set to N/A
        mysqli_query($conn,
            "UPDATE students SET course='$course'
             WHERE student_id='$student_id' AND course='N/A'");
    }

    // Insert the attendance record into the database
    $sql = "INSERT INTO attendance (student_id, teacher_id, attendance_date, status)
            VALUES ('$student_id', '$teacher_id', '$attendance_date', '$status')";

    if(mysqli_query($conn, $sql)) {
        $success = "✅ Attendance added successfully!";
    } else {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}

// Fetch all teachers for the dropdown
$teachers = mysqli_query($conn, "SELECT * FROM teachers ORDER BY teacher_name");
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Attendance</title>
<style>
/* Reset default styles */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
body { background: #f4f6f9; padding: 30px; }

/* Form container */
.container {
    max-width: 500px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
h2 { margin-bottom: 20px; color: #333; text-align: center; }
label { font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
select, input {
    width: 100%; padding: 10px; margin-bottom: 15px;
    border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
}

/* Save button */
.btn-save {
    background: #28a745; color: white; padding: 10px 20px;
    border: none; border-radius: 5px; cursor: pointer;
    font-size: 15px; width: 100%;
}
.btn-save:hover { background: #218838; }

/* Cancel link */
.btn-cancel { display: block; text-align: center; margin-top: 10px; color: #dc3545; text-decoration: none; }

/* Success and error messages */
.success { color: green; text-align: center; margin-bottom: 10px; font-weight: bold; }
.error   { color: red;   text-align: center; margin-bottom: 10px; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
    <h2>➕ Add Attendance</h2>

    <!-- Show success or error message -->
    <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if(isset($error))   echo "<p class='error'>$error</p>"; ?>

    <!-- Attendance form -->
    <form method="POST">

        <!-- Student name text input -->
        <label>Student Name:</label>
        <input type="text" name="student_name" placeholder="Type student name..." required>

        <!-- Course input -->
        <label>Enrolled Course:</label>
        <input type="text" name="course" placeholder="e.g. Computer Science..." required>

        <!-- Teacher dropdown loaded from database -->
        <label>Teacher:</label>
        <select name="teacher_id" required>
            <option value="">-- Select Teacher --</option>
            <?php while($t = mysqli_fetch_assoc($teachers)): ?>
                <option value="<?= $t['teacher_id'] ?>"><?= $t['teacher_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Date picker -->
        <label>Date:</label>
        <input type="date" name="attendance_date" value="<?= date('Y-m-d') ?>" required>

        <!-- Status dropdown -->
        <label>Status:</label>
        <select name="status" required>
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
            <option value="Late">Late</option>
        </select>

        <!-- Submit button -->
        <button type="submit" class="btn-save">💾 Save Attendance</button>

        <!-- Cancel link goes back to attendance list -->
        <a href="attendance_list.php" class="btn-cancel">Cancel</a>
    </form>
</div>
</body>
</html>