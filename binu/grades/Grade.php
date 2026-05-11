<?php
// ============================================================
// FILE: Grade.php
// MODULE: Grade & Result Tracking
// PROJECT: EduTeam - Student Record System
// DEVELOPER: Binu Karki
// LAYER: Middle Layer (Business Logic Class)
// DESCRIPTION: Grade class containing all database operations
//              for the Grade & Result Tracking module
//              Used by teacher_dashboard.php and student_dashboard.php
// ============================================================

class Grade {

    // Private database connection variable
    // Only accessible within this class
    private $conn;

    // -----------------------------------------------
    // CONSTRUCTOR
    // Receives $conn from the presentation layer
    // and stores it for use in all methods
    // -----------------------------------------------
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ============================================================
    // METHOD: login()
    // Checks username and password against grade_users table
    // Returns user array if found, false if not found
    // Called by: grade_login.php
    // ============================================================
    public function login($username, $password) {

        // Prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($this->conn,
            "SELECT * FROM grade_users WHERE username = ? AND password = ?"
        );

        // Bind string parameters
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Return user data if found, false if not
        if (mysqli_num_rows($result) === 1) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

    // ============================================================
    // METHOD: getAllGrades()
    // Fetches all grades with optional search and filter
    // Returns array of grade records
    // Called by: teacher_dashboard.php
    // ============================================================
    public function getAllGrades($search = '', $filter_course = '') {

        // Base query
        $query  = "SELECT * FROM grade WHERE 1=1";
        $params = [];
        $types  = '';

        // Add search condition if provided
        if (!empty($search)) {
            $query   .= " AND student_id LIKE ?";
            $params[] = "%$search%";
            $types   .= 's';
        }

        // Add course filter if selected
        if (!empty($filter_course)) {
            $query   .= " AND course_id = ?";
            $params[] = $filter_course;
            $types   .= 's';
        }

        // Order by newest first
        $query .= " ORDER BY grade_id DESC";

        // Execute prepared statement
        $stmt = mysqli_prepare($this->conn, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // ============================================================
    // METHOD: getGradeById()
    // Fetches a single grade record by grade_id
    // Used to pre-fill the edit form
    // Called by: teacher_dashboard.php
    // ============================================================
    public function getGradeById($grade_id) {

        $stmt = mysqli_prepare($this->conn,
            "SELECT * FROM grade WHERE grade_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $grade_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // ============================================================
    // METHOD: getStudentGrades()
    // Fetches all grades for a specific student
    // Filters by student_id so student sees only their own grades
    // Called by: student_dashboard.php
    // ============================================================
    public function getStudentGrades($student_id) {

        $stmt = mysqli_prepare($this->conn,
            "SELECT * FROM grade WHERE student_id = ? ORDER BY grade_id DESC"
        );
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // ============================================================
    // METHOD: addGrade()
    // Inserts a new grade record into the database
    // Calculates total_grade, percentage, is_passed automatically
    // Called by: teacher_dashboard.php
    // ============================================================
    public function addGrade($student_id, $course_id, $mid_term, $final_term) {

        // Calculate values
        $total_grade = $mid_term + $final_term;
        $percentage  = ($total_grade / 200) * 100;
        $is_passed   = ($percentage >= 50) ? 1 : 0;

        // Insert prepared statement
        $stmt = mysqli_prepare($this->conn,
            "INSERT INTO grade (student_id, course_id, mid_term, final_term, total_grade, percentage, is_passed)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        // Bind parameters: i=int, s=string, d=double
        mysqli_stmt_bind_param($stmt, "isddddi",
            $student_id, $course_id, $mid_term, $final_term, $total_grade, $percentage, $is_passed
        );

        return mysqli_stmt_execute($stmt);
    }

    // ============================================================
    // METHOD: updateGrade()
    // Updates an existing grade record in the database
    // Recalculates total_grade, percentage, is_passed
    // Called by: teacher_dashboard.php
    // ============================================================
    public function updateGrade($grade_id, $student_id, $course_id, $mid_term, $final_term) {

        // Recalculate values
        $total_grade = $mid_term + $final_term;
        $percentage  = ($total_grade / 200) * 100;
        $is_passed   = ($percentage >= 50) ? 1 : 0;

        // Update prepared statement
        $stmt = mysqli_prepare($this->conn,
            "UPDATE grade SET student_id=?, course_id=?, mid_term=?, final_term=?,
             total_grade=?, percentage=?, is_passed=? WHERE grade_id=?"
        );

        mysqli_stmt_bind_param($stmt, "isddddii",
            $student_id, $course_id, $mid_term, $final_term, $total_grade, $percentage, $is_passed, $grade_id
        );

        return mysqli_stmt_execute($stmt);
    }

    // ============================================================
    // METHOD: deleteGrade()
    // Deletes a grade record by grade_id
    // Called by: teacher_dashboard.php
    // ============================================================
    public function deleteGrade($grade_id) {

        $stmt = mysqli_prepare($this->conn,
            "DELETE FROM grade WHERE grade_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $grade_id);
        return mysqli_stmt_execute($stmt);
    }

    // ============================================================
    // METHOD: getChartData()
    // Returns aggregate pass/fail counts for teacher chart
    // Used to populate Chart.js bar graph in teacher dashboard
    // Called by: teacher_dashboard.php
    // ============================================================
    public function getChartData() {

        $result = mysqli_query($this->conn,
            "SELECT
                SUM(CASE WHEN mid_term >= 50 THEN 1 ELSE 0 END)   AS mid_passed,
                SUM(CASE WHEN mid_term < 50 THEN 1 ELSE 0 END)    AS mid_failed,
                SUM(CASE WHEN final_term >= 50 THEN 1 ELSE 0 END) AS final_passed,
                SUM(CASE WHEN final_term < 50 THEN 1 ELSE 0 END)  AS final_failed,
                SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END)    AS overall_passed,
                SUM(CASE WHEN is_passed = 0 THEN 1 ELSE 0 END)    AS overall_failed
             FROM grade"
        );
        return mysqli_fetch_assoc($result);
    }

    // ============================================================
    // METHOD: getStudentChartData()
    // Returns chart data for a specific student only
    // Used to populate Chart.js charts in student dashboard
    // Called by: student_dashboard.php
    // ============================================================
    public function getStudentChartData($student_id) {

        $stmt = mysqli_prepare($this->conn,
            "SELECT course_id, mid_term, final_term, percentage, is_passed
             FROM grade WHERE student_id = ? ORDER BY course_id"
        );
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // ============================================================
    // METHOD: getAllCourses()
    // Returns distinct list of courses from grade table
    // Used to populate filter dropdown in teacher dashboard
    // Called by: teacher_dashboard.php
    // ============================================================
    public function getAllCourses() {

        $result = mysqli_query($this->conn,
            "SELECT DISTINCT course_id FROM grade ORDER BY course_id"
        );
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // ============================================================
    // METHOD: validateInput()
    // Validates grade input fields
    // Returns error message string or empty string if valid
    // Called by: teacher_dashboard.php
    // ============================================================
    public function validateInput($student_id, $course_id, $mid_term, $final_term) {

        // Check student ID is a positive integer
        if ($student_id <= 0) {
            return "Student ID must be a positive number.";
        }

        // Check course ID is not empty
        if (empty($course_id)) {
            return "Course ID cannot be empty.";
        }

        // Check mid term is between 0 and 100
        if ($mid_term < 0 || $mid_term > 100) {
            return "Mid Term must be between 0 and 100.";
        }

        // Check final term is between 0 and 100
        if ($final_term < 0 || $final_term > 100) {
            return "Final Term must be between 0 and 100.";
        }

        // All valid - return empty string
        return "";
    }
}
?>
