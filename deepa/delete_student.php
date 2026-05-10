<?php
// delete_student.php - Delete Student
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

$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name FROM student WHERE student_id = $id"));
if(!$student) { header('Location: student_list.php?error=Student not found'); exit(); }

if(mysqli_query($conn, "DELETE FROM student WHERE student_id = $id")) {
    header('Location: student_list.php?success=' . urlencode($student['full_name'] . ' deleted successfully'));
} else {
    header('Location: student_list.php?error=Could not delete student');
}
exit();
?>