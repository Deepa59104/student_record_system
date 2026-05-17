<?php
/**
 * FILE:  delete_student.php
 * DEV:   Deepa Thapa | SRS-84
 * LAYER: Middle Layer + Data Layer
 * DESC:  Deletes student by ID and redirects back.
 */
session_start();
if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_logged_in'])) {
    header('Location: student_login.php'); exit();
}
require_once '../db.php';
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    mysqli_query($conn, "DELETE FROM students WHERE student_id=$id");
}
header('Location: student_list.php?success=Student deleted successfully!');
exit();