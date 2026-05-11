<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../../isha/login.php');
    exit();
}

$result = $conn->query("SELECT * FROM grade ORDER BY grade_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edu Team - Grade Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f5; }

        .navbar {
            background: #4a235a;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-left { color: white; font-size: 16px; font-weight: 500; }
        .navbar-right { display: flex; align-items: center; gap: 20px; }
        .navbar-right a { color: #ddd; font-size: 13px; text-decoration: none; }
        .navbar-right a:hover { color: white; }

        .content { padding: 24px; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .page-title { font-size: 20px; font-weight: 500; color: #333; margin-bottom: 4px; }
        .page-subtitle { font-size: 13px; color: #999; }

        .add-btn {
            background: #4a235a;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .card {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead { background: #f9f9f9; }
        th { text-align: left; padding: 10px 16px; color: #999; font-weight: 500; }
        td { padding: 12px 16px; border-bottom: 1px solid #f5f5f5; }

        .badge-pass {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 11px;
        }
        .badge-fail {
            background: #fce4ec;
            color: #c62828;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 11px;
        }

        .edit-btn {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 4px;
            text-decoration: none;
        }
        .delete-btn {
            background: #fce4ec;
            color: #c62828;
            padding: 4px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
        }

        .card-footer { padding: 12px 16px; border-top: 1px solid #eee; }
        .card-footer p { font-size: 12px; color: #999; }

        .developer { text-align: center; margin-top: 24px; font-size: 12px; color: #bbb; }
    </style>
</head>
<body>

<div class="navbar">
    <span class="navbar-left">Edu Team - Student Record System</span>
    <div class="navbar-right">
        <a href="../../sita/dashboard.php">Dashboard</a>
        <a href="../../sita/teacher_list.php">Teachers</a>
        <a href="../../deepa/student_list.php">Students</a>
        <a href="../../isha/course_list.php">Courses</a>
        <a href="grade_list.php">Grades</a>
        <a href="../../satinder/attendance_list.php">Attendance</a>
        <a href="../../isha/logout.php">Logout</a>
    </div>
</div>

<div class="content">
    <div class="header">
        <div>
            <p class="page-title">Grade Management</p>
            <p class="page-subtitle">Developer: Binu | EduTeam</p>
        </div>
        <a href="add_grade.html" class="add-btn">+ Add New Grade</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Course ID</th>
                    <th>Mid Term</th>
                    <th>Final Term</th>
                    <th>Total</th>
                    <th>Percentage</th>
                    <th>Result</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>'.$row['grade_id'].'</td>';
                        echo '<td>'.$row['student_id'].'</td>';
                        echo '<td>'.$row['course_id'].'</td>';
                        echo '<td>'.$row['mid_term'].'</td>';
                        echo '<td>'.$row['final_term'].'</td>';
                        echo '<td>'.$row['total_grade'].'</td>';
                        echo '<td>'.$row['percentage'].'%</td>';
                        echo '<td>';
                        echo $row['is_passed'] == 1
                            ? '<span class="badge-pass">Pass</span>'
                            : '<span class="badge-fail">Fail</span>';
                        echo '</td>';
                        echo '<td>';
                        echo '<a href="edit_grade.html?id='.$row['grade_id'].'" class="edit-btn">Edit</a>';
                        echo '<a href="delete.php?id='.$row['grade_id'].'" class="delete-btn" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9" style="text-align:center;padding:20px;color:#999;">No grades found</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <div class="card-footer">
            <p>Total: <?php echo $result ? $result->num_rows : 0; ?> grades</p>
        </div>
    </div>

    <p class="developer">Developer: Binu | EduTeam</p>
</div>

</body>
</html>