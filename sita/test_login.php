<?php
$conn = mysqli_connect('localhost', 'root', '', 'student_record_system', 3306);
if(!$conn) die('Connection failed: ' . mysqli_connect_error());

mysqli_query($conn, "ALTER TABLE teacher ADD COLUMN subject VARCHAR(100)");
mysqli_query($conn, "ALTER TABLE teacher ADD COLUMN phone VARCHAR(20)");
mysqli_query($conn, "ALTER TABLE teacher ADD COLUMN qualification VARCHAR(100)");
mysqli_query($conn, "ALTER TABLE teacher ADD COLUMN course_id INT");

echo "Done! ✅<br>";

// Confirm columns
$result = mysqli_query($conn, "DESCRIBE teacher");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " — " . $row['Type'] . "<br>";
}
?>