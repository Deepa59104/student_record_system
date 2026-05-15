<?php
// Teacher.php - Teacher Middle Layer Class
// Developer: Sita | SRS-96
// Project: Edu Team - Student Record System

class Teacher {

    // Database connection property
    private $conn;

    // Constructor - receives database connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all teachers - with optional search filter
    public function getAllTeachers($search = '') {
        if($search) {
            // Sanitize search input to prevent SQL injection
            $search = mysqli_real_escape_string($this->conn, $search);
            $sql = "SELECT t.*, c.course_name 
                    FROM teacher t 
                    LEFT JOIN course c ON t.course_id = c.course_id
                    WHERE t.first_name LIKE '%$search%' 
                    OR t.last_name LIKE '%$search%' 
                    OR t.email LIKE '%$search%' 
                    OR t.subject LIKE '%$search%'
                    ORDER BY t.teacher_id DESC";
        } else {
            // Return all teachers with their course name
            $sql = "SELECT t.*, c.course_name 
                    FROM teacher t 
                    LEFT JOIN course c ON t.course_id = c.course_id
                    ORDER BY t.teacher_id DESC";
        }
        return mysqli_query($this->conn, $sql);
    }

    // Get single teacher by ID
    public function getTeacherById($id) {
        $id  = intval($id); // Sanitize ID
        $sql = "SELECT t.*, c.course_name 
                FROM teacher t 
                LEFT JOIN course c ON t.course_id = c.course_id
                WHERE t.teacher_id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    // Add new teacher to database
    public function addTeacher($data) {
        // Sanitize all string inputs
        $first_name    = mysqli_real_escape_string($this->conn, $data['first_name']);
        $last_name     = mysqli_real_escape_string($this->conn, $data['last_name']);
        $email         = mysqli_real_escape_string($this->conn, $data['email']);
        $subject       = mysqli_real_escape_string($this->conn, $data['subject']);
        $phone         = mysqli_real_escape_string($this->conn, $data['phone']);
        $qualification = mysqli_real_escape_string($this->conn, $data['qualification']);
        $course_id     = intval($data['course_id']); // Sanitize course ID
        $is_active     = isset($data['is_active']) ? intval($data['is_active']) : 1;

        // Hash password using MD5
        $password = md5($data['password']);

        // Insert teacher with all fields including qualification and course_id
        $sql = "INSERT INTO teacher 
                (first_name, last_name, email, password, subject, phone, qualification, course_id, is_active)
                VALUES 
                ('$first_name', '$last_name', '$email', '$password', '$subject', '$phone', '$qualification', $course_id, $is_active)";

        return mysqli_query($this->conn, $sql);
    }

    // Update existing teacher record
    public function updateTeacher($id, $data) {
        $id            = intval($id); // Sanitize ID
        $first_name    = mysqli_real_escape_string($this->conn, $data['first_name']);
        $last_name     = mysqli_real_escape_string($this->conn, $data['last_name']);
        $email         = mysqli_real_escape_string($this->conn, $data['email']);
        $subject       = mysqli_real_escape_string($this->conn, $data['subject']);
        $phone         = mysqli_real_escape_string($this->conn, $data['phone']);
        $qualification = mysqli_real_escape_string($this->conn, $data['qualification']);
        $course_id     = intval($data['course_id']);
        $is_active     = intval($data['is_active']);

        // Update all teacher fields
        $sql = "UPDATE teacher SET 
                first_name='$first_name', 
                last_name='$last_name', 
                email='$email', 
                subject='$subject', 
                phone='$phone',
                qualification='$qualification',
                course_id=$course_id,
                is_active=$is_active
                WHERE teacher_id=$id";

        return mysqli_query($this->conn, $sql);
    }

    // Update password separately (optional)
    public function updatePassword($id, $password) {
        $id       = intval($id);
        $password = md5($password); // Hash new password
        $sql      = "UPDATE teacher SET password='$password' WHERE teacher_id=$id";
        return mysqli_query($this->conn, $sql);
    }

    // Delete teacher by ID
    public function deleteTeacher($id) {
        $id  = intval($id); // Sanitize ID
        $sql = "DELETE FROM teacher WHERE teacher_id = $id";
        return mysqli_query($this->conn, $sql);
    }

    // Count total active teachers
    public function countTeachers() {
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM teacher WHERE is_active=1");
        $row    = mysqli_fetch_assoc($result);
        return $row['total'];
    }
}
?>