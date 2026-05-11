<?php
// logout.php - Logout
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

session_start();
session_destroy();
header('Location: login.php');
exit();
?>