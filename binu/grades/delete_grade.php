<?php
// delete_grade.php - Delete Grade
// Developer: Binu
// Module: Grade Management
// Project: Edu Team - Student Record System

session_start();
include '../../db.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../../isha/login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: grade_list.php');
    exit();
}

$id = $_GET['id'];

$conn->query("DELETE FROM grade WHERE grade_id = '$id'");

header('Location: grade_list.php?success=Grade deleted successfully!');
exit();
?>