<?php
// Start session and check if user is logged in
session_start();
if(!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Connect to the database
include '../db.php';

// Check if an attendance ID was passed in the URL
if(isset($_GET['id'])) {
    // Get the attendance ID from the URL
    $id = $_GET['id'];

    // Delete the attendance record from the database
    mysqli_query($conn, "DELETE FROM attendance WHERE attendance_id='$id'");
}

// Redirect back to the attendance list after deletion
header('Location: attendance_list.php');
exit();
?>