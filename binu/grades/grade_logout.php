<?php
session_start();
session_destroy();
header("Location: grade_login.php");
exit();
?>