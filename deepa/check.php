<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system', 3306);
if(!$conn) die('Connection failed: ' . mysqli_connect_error());

echo "Connected: " . mysqli_get_host_info($conn) . "<br>";
echo "<br><b>All tables:</b><br>";
$t = mysqli_query($conn, "SHOW TABLES");
while($row = mysqli_fetch_array($t)){
    echo $row[0] . "<br>";
}

echo "<br><b>Course rows:</b><br>";
$r = mysqli_query($conn, "SELECT * FROM course");
echo "Total: " . mysqli_num_rows($r) . "<br>";
while($row = mysqli_fetch_assoc($r)){
    echo $row['course_id'] . " = " . $row['course_name'] . "<br>";
}

echo "<br><b>Students with courses:</b><br>";
$s = mysqli_query($conn, "SELECT s.student_id, s.full_name, s.course_id, c.course_name FROM student s LEFT JOIN course c ON s.course_id = c.course_id");
while($row = mysqli_fetch_assoc($s)){
    echo $row['student_id'] . " - " . $row['full_name'] . " = " . ($row['course_name'] ?? 'NULL') . "<br>";
}
?>