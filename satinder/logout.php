<?php
// Start the session so we can access and destroy it
session_start();

// Destroy all session data to log the user out
session_destroy();

// Redirect back to the login page
header('Location: login.php');
exit();
?>