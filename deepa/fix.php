<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system', 3306);

// Check columns
$r = mysqli_query($conn, "DESCRIBE course");
while($row = mysqli_fetch_assoc($r)){
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

// Insert without extra columns
mysqli_query($conn, "DELETE FROM course");
mysqli_query($conn, "INSERT INTO course (course_id, course_name) VALUES (1,'Computer Science')");
mysqli_query($conn, "INSERT INTO course (course_id, course_name) VALUES (2,'Information Technology')");
mysqli_query($conn, "INSERT INTO course (course_id, course_name) VALUES (3,'Software Engineering')");
mysqli_query($conn, "INSERT INTO course (course_id, course_name) VALUES (4,'Data Science')");
mysqli_query($conn, "INSERT INTO course (course_id, course_name) VALUES (5,'Cybersecurity')");

$r = mysqli_query($conn, "SELECT * FROM course");
echo "<br>Courses: " . mysqli_num_rows($r) . "<br>";
while($row = mysqli_fetch_assoc($r)){
    echo $row['course_id'] . " = " . $row['course_name'] . "<br>";
}
echo "<br><a href='student_list.php'>Go to Students</a>";
?>