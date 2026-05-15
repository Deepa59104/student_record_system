<?php
session_start();

if(!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit();
}

include '../db.php';
include 'Teacher.php';

$teacher = new Teacher($conn);
$id = $_GET['id'] ?? '';

if(!$id) {
    header("Location: teacher_list.php");
    exit();
}

if($id == $_SESSION['teacher_id']) {
    header("Location: teacher_list.php?error=cannot_delete_self");
    exit();
}

$result = $teacher->deleteTeacher($id);

if($result) {
    header("Location: teacher_list.php?success=deleted");
} else {
    header("Location: teacher_list.php?error=failed");
}
exit();
?>