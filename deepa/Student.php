<?php
class Student {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    public function getAllStudents($search = '', $filter = '') {
        $query = "SELECT s.*, c.course_name FROM student s LEFT JOIN course c ON s.course_id = c.course_id WHERE 1=1";
        if($search != '') $query .= " AND s.full_name LIKE '%$search%'";
        if($filter != '') $query .= " AND s.course_id = '$filter'";
        $query .= " ORDER BY s.student_id ASC";
        return mysqli_query($this->conn, $query);
    }
    public function getStudent($id) {
        $result = mysqli_query($this->conn, "SELECT s.*, c.course_name FROM student s LEFT JOIN course c ON s.course_id = c.course_id WHERE s.student_id = '$id'");
        return mysqli_fetch_assoc($result);
    }
    public function addStudent($data) {
        return mysqli_query($this->conn, "INSERT INTO student (full_name, email, teacher_id, course_id, enrolled_date) VALUES ('{$data['full_name']}','{$data['email']}','{$data['teacher_id']}','{$data['course_id']}','{$data['enrolled_date']}')");
    }
    public function updateStudent($id, $data) {
        return mysqli_query($this->conn, "UPDATE student SET full_name='{$data['full_name']}', email='{$data['email']}', teacher_id='{$data['teacher_id']}', course_id='{$data['course_id']}', enrolled_date='{$data['enrolled_date']}' WHERE student_id='$id'");
    }
    public function deleteStudent($id) {
        return mysqli_query($this->conn, "DELETE FROM student WHERE student_id = '$id'");
    }
    public function countAllStudents() {
        $row = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT COUNT(*) as total FROM student"));
        return $row['total'];
    }
    public function getAllCourses() {
        return mysqli_query($this->conn, "SELECT * FROM course ORDER BY course_name ASC");
    }
}
?>