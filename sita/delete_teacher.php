<?php
session_start();
if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../isha/login.php');
    exit();
}

include '../db.php';
include 'Teacher.php';

$conn = mysqli_connect('localhost', 'root', '', 'student_record_system', 3306);
$teacher = new Teacher($conn);

$id = intval($_GET['id'] ?? 0);

if(!$id) {
    header('Location: teacher_list.php');
    exit();
}

// Don't allow deleting yourself
if($id == $_SESSION['teacher_id']) {
    header('Location: teacher_list.php?error=cannot_delete_self');
    exit();
}

if($teacher->deleteTeacher($id)) {
    header('Location: teacher_list.php?success=deleted');
} else {
    header('Location: teacher_list.php?error=delete_failed');
}
exit();
?>