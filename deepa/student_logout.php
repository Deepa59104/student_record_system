<?php
/**
 * FILE:  student_logout.php
 * DEV:   Deepa Thapa | SRS-84
 * LAYER: Middle Layer
 * DESC:  Clears all sessions and redirects to dashboard.
 */
session_start();
session_destroy();
header('Location: student_login.php');
exit();