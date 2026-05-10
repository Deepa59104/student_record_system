<?php
$host = 'localhost';
$dbname = 'student_record_system';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if(!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>