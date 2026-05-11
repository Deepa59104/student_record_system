<?php
class Teacher {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllTeachers($search = '') {
        if($search) {
            $search = mysqli_real_escape_string($this->conn, $search);
            $sql = "SELECT * FROM teacher WHERE 
                    first_name LIKE '%$search%' OR 
                    last_name LIKE '%$search%' OR 
                    email LIKE '%$search%' OR 
                    subject LIKE '%$search%'
                    ORDER BY teacher_id DESC";
        } else {
            $sql = "SELECT * FROM teacher ORDER BY teacher_id DESC";
        }
        return mysqli_query($this->conn, $sql);
    }

    public function getTeacherById($id) {
        $id = intval($id);
        $sql = "SELECT * FROM teacher WHERE teacher_id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function addTeacher($data) {
        $first_name = mysqli_real_escape_string($this->conn, $data['first_name']);
        $last_name  = mysqli_real_escape_string($this->conn, $data['last_name']);
        $email      = mysqli_real_escape_string($this->conn, $data['email']);
        $subject    = mysqli_real_escape_string($this->conn, $data['subject']);
        $phone      = mysqli_real_escape_string($this->conn, $data['phone']);
        $password   = md5($data['password']);

        $sql = "INSERT INTO teacher (first_name, last_name, email, password, subject, phone, is_active)
                VALUES ('$first_name', '$last_name', '$email', '$password', '$subject', '$phone', 1)";
        return mysqli_query($this->conn, $sql);
    }

    public function updateTeacher($id, $data) {
        $id         = intval($id);
        $first_name = mysqli_real_escape_string($this->conn, $data['first_name']);
        $last_name  = mysqli_real_escape_string($this->conn, $data['last_name']);
        $email      = mysqli_real_escape_string($this->conn, $data['email']);
        $subject    = mysqli_real_escape_string($this->conn, $data['subject']);
        $phone      = mysqli_real_escape_string($this->conn, $data['phone']);

        $sql = "UPDATE teacher SET 
                first_name='$first_name', last_name='$last_name', 
                email='$email', subject='$subject', phone='$phone'
                WHERE teacher_id=$id";
        return mysqli_query($this->conn, $sql);
    }

    public function deleteTeacher($id) {
        $id = intval($id);
        $sql = "DELETE FROM teacher WHERE teacher_id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function countTeachers() {
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM teacher WHERE is_active=1");
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
}
?>