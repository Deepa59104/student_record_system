<?php
// Start session and check if user is logged in
session_start();
if(!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Connect to the database
include '../db.php';

// Get the attendance ID from the URL
$id = $_GET['id'];

// Fetch the existing attendance record with student and teacher info
$record = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, s.student_name, s.course, t.teacher_name
     FROM attendance a
     JOIN students s ON a.student_id = s.student_id
     JOIN teachers t ON a.teacher_id = t.teacher_id
     WHERE a.attendance_id = '$id'"));

// If record not found redirect back to list
if(!$record) {
    header('Location: attendance_list.php');
    exit();
}

// Check if the edit form was submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get updated values from the form
    $student_name    = mysqli_real_escape_string($conn, $_POST['student_name']);
    $course          = mysqli_real_escape_string($conn, $_POST['course']);
    $teacher_id      = $_POST['teacher_id'];
    $attendance_date = $_POST['attendance_date'];
    $status          = $_POST['status'];

    // Update the student name and course in the students table
    mysqli_query($conn,
        "UPDATE students SET student_name='$student_name', course='$course'
         WHERE student_id='{$record['student_id']}'");

    // Update the attendance record in the attendance table
    $sql = "UPDATE attendance
            SET teacher_id='$teacher_id', attendance_date='$attendance_date', status='$status'
            WHERE attendance_id='$id'";

    if(mysqli_query($conn, $sql)) {
        $success = "✅ Updated successfully!";
        // Refresh the record to show updated values
        $record = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT a.*, s.student_name, s.course, t.teacher_name
             FROM attendance a
             JOIN students s ON a.student_id = s.student_id
             JOIN teachers t ON a.teacher_id = t.teacher_id
             WHERE a.attendance_id = '$id'"));
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
<title>Edit Attendance</title>
<style>
/* Reset default styles */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
body { background: #f4f6f9; padding: 30px; }

/* Form container */
.container {
    max-width: 500px; margin: auto; background: white;
    padding: 30px; border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
h2 { margin-bottom: 20px; color: #333; text-align: center; }
label { font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
select, input {
    width: 100%; padding: 10px; margin-bottom: 15px;
    border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
}

/* Update button */
.btn-save {
    background: #007bff; color: white; padding: 10px 20px;
    border: none; border-radius: 5px; cursor: pointer;
    font-size: 15px; width: 100%;
}
.btn-save:hover { background: #0056b3; }

/* Cancel link */
.btn-cancel { display: block; text-align: center; margin-top: 10px; color: #dc3545; text-decoration: none; }

/* Success and error messages */
.success { color: green; text-align: center; margin-bottom: 10px; font-weight: bold; }
.error   { color: red;   text-align: center; margin-bottom: 10px; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
    <h2>✏️ Edit Attendance</h2>

    <!-- Show success or error message -->
    <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if(isset($error))   echo "<p class='error'>$error</p>"; ?>

    <!-- Edit form pre-filled with existing data -->
    <form method="POST">

        <!-- Student name pre-filled and editable -->
        <label>Student Name:</label>
        <input type="text" name="student_name"
               value="<?= $record['student_name'] ?>" required>

        <!-- Course pre-filled and editable -->
        <label>Enrolled Course:</label>
        <input type="text" name="course"
               value="<?= $record['course'] == 'N/A' ? '' : $record['course'] ?>"
               placeholder="e.g. Computer Science..." required>

        <!-- Teacher dropdown with current teacher pre-selected -->
        <label>Teacher:</label>
        <select name="teacher_id" required>
            <option value="">-- Select Teacher --</option>
            <?php while($t = mysqli_fetch_assoc($teachers)): ?>
                <option value="<?= $t['teacher_id'] ?>"
                    <?= $t['teacher_id'] == $record['teacher_id'] ? 'selected' : '' ?>>
                    <?= $t['teacher_name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Date pre-filled with existing date -->
        <label>Date:</label>
        <input type="date" name="attendance_date"
               value="<?= $record['attendance_date'] ?>" required>

        <!-- Status dropdown with current status pre-selected -->
        <label>Status:</label>
        <select name="status" required>
            <?php foreach(['Present', 'Absent', 'Late'] as $opt): ?>
                <option value="<?= $opt ?>"
                    <?= $opt == $record['status'] ? 'selected' : '' ?>>
                    <?= $opt ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Submit button -->
        <button type="submit" class="btn-save">💾 Update Attendance</button>

        <!-- Cancel link goes back to attendance list -->
        <a href="attendance_list.php" class="btn-cancel">Cancel</a>
    </form>
</div>
</body>
</html>