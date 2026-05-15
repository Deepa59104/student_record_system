<?php
// logout.php - Logout Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

// Start the session so we can access and destroy it
session_start();

// Destroy all session data - logs the teacher out and clears all stored session variables
session_destroy();

// Redirect to login page after logout
header('Location: login.php');

// Stop the script from running any further code
exit();
?>