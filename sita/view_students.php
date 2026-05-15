<?php
session_start();

if(!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php');
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'student_record_system');
if(!$conn) die('DB Error: ' . mysqli_connect_error());

$teacher_row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT first_name, last_name FROM teacher WHERE teacher_id = " . (int)$_SESSION['teacher_id']
));
$teacher_name = $teacher_row
    ? $teacher_row['first_name'] . ' ' . $teacher_row['last_name']
    : ($_SESSION['teacher_name'] ?? 'Sita');
$_SESSION['teacher_name'] = $teacher_name;

$search   = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter   = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';
$per_page = 8;
$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset   = ($page - 1) * $per_page;

$total_all = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM student"))['total'];

$cq = "SELECT COUNT(*) as total FROM student s LEFT JOIN course c ON s.course_id = c.course_id WHERE 1=1";
if($search) $cq .= " AND (s.full_name LIKE '%$search%' OR s.email LIKE '%$search%')";
if($filter) $cq .= " AND c.course_name = '$filter'";

$total_filtered = (int)mysqli_fetch_assoc(mysqli_query($conn, $cq))['total'];
$total_pages    = max(1, ceil($total_filtered / $per_page));

$query = "SELECT s.student_id, s.full_name, s.email,
    s.enrolled_date,
    c.course_name,
    CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
    FROM student s
    LEFT JOIN course c ON s.course_id = c.course_id
    LEFT JOIN teacher t ON s.teacher_id = t.teacher_id
    WHERE 1=1";
if($search) $query .= " AND (s.full_name LIKE '%$search%' OR s.email LIKE '%$search%')";
if($filter) $query .= " AND c.course_name = '$filter'";
$query .= " ORDER BY s.student_id ASC LIMIT $per_page OFFSET $offset";

$result = mysqli_query($conn, $query);

$course_result = mysqli_query($conn, "SELECT DISTINCT course_name FROM course ORDER BY course_name ASC");

$avatar_colors = ['#7c3aed','#3b82f6','#10b981','#f59e0b','#ef4444','#ec4899'];

function pageUrl($p,$s,$f){
    return 'view_students.php?'.http_build_query(['page'=>$p,'search'=>$s,'filter'=>$f]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students — Edu Team SRS</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root {
    --purple:      #6C3FC5;
    --purple-nav:  #3B2069;
    --purple-light:#7C4FD5;
    --purple-pale: #EDE6FF;
    --green:       #059669;
    --red:         #ef4444;
    --blue:        #3b82f6;
    --amber:       #d97706;
    --text-dark:   #1A1033;
    --text-mid:    #5A5475;
    --text-light:  #9A93B0;
    --border:      #E2D9F3;
    --bg:          #F0EBFF;
    --card:        #ffffff;
}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);min-height:100vh;color:var(--text-dark)}

.navbar{position:sticky;top:0;z-index:100;padding:0 40px;height:58px;display:flex;align-items:center;justify-content:space-between;background:var(--purple-nav);box-shadow:0 2px 16px rgba(59,32,105,0.18)}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}
.brand-logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7C4FD5,#5B2EA6);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:white}
.brand-text{font-size:14px;font-weight:700;color:white}
.nav-links{display:flex;align-items:center;gap:28px}
.nav-links a{font-size:13px;font-weight:500;color:rgba(255,255,255,0.55);text-decoration:none;transition:color 0.2s}
.nav-links a:hover{color:white}
.nav-links a.active{color:white;font-weight:700}

.content{max-width:1160px;margin:0 auto;padding:36px 32px 60px}

.welcome-banner{display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,rgba(108,63,197,0.08),rgba(91,46,166,0.05));border:1px solid rgba(108,63,197,0.15);border-radius:16px;padding:18px 24px;margin-bottom:28px;box-shadow:0 2px 12px rgba(108,63,197,0.06)}
.w-avatar{width:46px;height:46px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--purple),var(--purple-light));display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white}
.w-title{font-size:17px;font-weight:700;color:var(--text-dark);margin-bottom:2px}
.w-title span{color:var(--purple)}
.w-sub{font-size:12px;color:var(--text-light)}

.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.page-title{font-size:26px;font-weight:800;color:var(--text-dark);margin-bottom:3px}
.page-sub{font-size:12px;color:var(--text-light)}
.back-btn{display:inline-flex;align-items:center;gap:6px;background:var(--card);color:var(--purple);padding:11px 22px;border-radius:12px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid var(--border)}
.back-btn:hover{background:var(--purple-pale)}

.stats-row{display:flex;gap:14px;margin-bottom:20px}
.stat-box{flex:1;padding:16px 20px;background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 2px 8px rgba(108,63,197,0.05)}
.stat-label{font-size:12px;color:var(--text-light);margin-bottom:6px;font-weight:500}
.stat-val{font-size:24px;font-weight:800;color:var(--purple)}

.search-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.search-wrap{flex:1;min-width:200px;position:relative}
.search-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-light);pointer-events:none}
.search-input{width:100%;padding:11px 14px 11px 38px;background:var(--card);border:1.5px solid var(--border);border-radius:12px;font-size:13px;color:var(--text-dark);font-family:inherit;outline:none}
.search-input::placeholder{color:var(--text-light)}
.search-input:focus{border-color:var(--purple)}
.filter-select{padding:11px 32px 11px 14px;min-width:180px;background:var(--card);border:1.5px solid var(--border);border-radius:12px;font-size:13px;color:var(--text-dark);font-family:inherit;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239A93B0' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center}
.btn-search{padding:11px 22px;background:linear-gradient(135deg,var(--purple),var(--purple-light));color:white;border:none;border-radius:12px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer}
.btn-search:hover{opacity:0.88}
.btn-clear{padding:11px 18px;background:var(--card);border:1.5px solid var(--border);color:var(--text-mid);border-radius:12px;font-size:13px;text-decoration:none;font-weight:500}
.btn-clear:hover{border-color:var(--purple);color:var(--purple)}

.table-card{background:var(--card);border:1px solid var(--border);border-radius:18px;overflow:hidden;box-shadow:0 4px 20px rgba(108,63,197,0.07)}
.table-top{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid var(--border)}
.table-top h3{font-size:14px;font-weight:700;color:var(--text-dark)}
.table-top span{font-size:12px;color:var(--text-light)}

table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:var(--purple-pale)}
th{text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:var(--purple);text-transform:uppercase;letter-spacing:0.7px}
td{padding:13px 16px;border-top:1px solid var(--border);color:var(--text-mid);vertical-align:middle}
tbody tr:hover td{background:rgba(108,63,197,0.03)}

.name-cell{display:flex;align-items:center;gap:10px}
.av{width:34px;height:34px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}
.fname{font-weight:700;color:var(--text-dark)}
.course-badge{display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;background:rgba(108,63,197,0.10);color:var(--purple)}
.teacher-link{color:var(--purple);text-decoration:none;font-size:12px;font-weight:600}
.teacher-link:hover{text-decoration:underline}
.date-td{color:var(--text-light)}

.acts{display:flex;gap:6px}
.btn-edit{background:rgba(59,130,246,0.10);color:var(--blue);padding:5px 12px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600;border:1px solid rgba(59,130,246,0.2)}
.btn-edit:hover{background:rgba(59,130,246,0.22)}
.btn-del{background:rgba(239,68,68,0.10);color:var(--red);padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid rgba(239,68,68,0.2);text-decoration:none}
.btn-del:hover{background:rgba(239,68,68,0.22)}
.btn-grade{background:rgba(217,119,6,0.10);color:var(--amber);padding:5px 12px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600;border:1px solid rgba(217,119,6,0.2)}
.btn-grade:hover{background:rgba(217,119,6,0.22)}

.empty-state{text-align:center;padding:56px 20px;color:var(--text-light);font-size:14px}
.table-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-top:1px solid var(--border)}
.footer-info{font-size:12px;color:var(--text-light)}
.pagination{display:flex;align-items:center;gap:6px}
.page-btn{min-width:32px;height:32px;padding:0 10px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;background:var(--card);border:1.5px solid var(--border);color:var(--text-mid)}
.page-btn:hover{border-color:var(--purple);color:var(--purple)}
.page-btn.active{background:linear-gradient(135deg,var(--purple),var(--purple-light));border-color:transparent;color:white}
.page-btn.disabled{opacity:0.35;pointer-events:none}
</style>
</head>
<body>

<nav class="navbar">
    <a class="brand" href="../deepa/dashboard.php">
        <div class="brand-logo">E</div>
        <span class="brand-text">Edu Team – Student Record System</span>
    </a>
    <div class="nav-links">
        <a href="../deepa/dashboard.php">Dashboard</a>
        <a href="teacher_list.php">Teachers</a>
        <a href="view_students.php" class="active">Students</a>
        <a href="teacher_logout.php">Logout</a>
    </div>
</nav>

<div class="content">

    <div class="welcome-banner">
        <div class="w-avatar"><?php echo strtoupper(substr($teacher_name,0,1)); ?></div>
        <div>
            <div class="w-title">👋 Welcome, <span><?php echo htmlspecialchars($teacher_name); ?>!</span></div>
            <div class="w-sub">Student Management — Developer: Sita | SRS-92</div>
        </div>
    </div>

    <div class="page-header">
        <div>
            <h1 class="page-title">Students</h1>
            <p class="page-sub">Developer: Sita | SRS-92</p>
        </div>
        <a href="teacher_list.php" class="back-btn">← Back to Teachers</a>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Total Students</div>
            <div class="stat-val"><?php echo $total_all; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Showing</div>
            <div class="stat-val"><?php echo $total_filtered; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Current Page</div>
            <div class="stat-val"><?php echo $page; ?> / <?php echo $total_pages; ?></div>
        </div>
    </div>

    <form method="GET" action="view_students.php" class="search-bar">
        <div class="search-wrap">
            <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" class="search-input"
                   placeholder="Search by student name or email..."
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <select name="filter" class="filter-select">
            <option value="">All Courses</option>
            <?php if($course_result): while($c = mysqli_fetch_assoc($course_result)): ?>
            <option value="<?php echo htmlspecialchars($c['course_name']); ?>"
                <?php echo $filter === $c['course_name'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c['course_name']); ?>
            </option>
            <?php endwhile; endif; ?>
        </select>
        <button type="submit" class="btn-search">Search</button>
        <a href="view_students.php" class="btn-clear">Clear</a>
    </form>

    <div class="table-card">
        <div class="table-top">
            <h3>All Students</h3>
            <span>Showing <?php echo $total_filtered>0?$offset+1:0; ?>–<?php echo min($offset+$per_page,$total_filtered); ?> of <?php echo $total_filtered; ?> students</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:46px">ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Enrolled Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0):
                    $i = 0;
                    while($row = mysqli_fetch_assoc($result)):
                        $color = $avatar_colors[$i % count($avatar_colors)];
                        $parts = explode(' ', $row['full_name']);
                        $initials = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
                        $date_display = !empty($row['enrolled_date']) ? date('d M Y', strtotime($row['enrolled_date'])) : '—';
                        $i++;
                ?>
                <tr>
                    <td><?php echo $row['student_id']; ?></td>
                    <td>
                        <div class="name-cell">
                            <div class="av" style="background:<?php echo $color; ?>22;color:<?php echo $color; ?>"><?php echo $initials; ?></div>
                            <span class="fname"><?php echo htmlspecialchars($row['full_name']); ?></span>
                        </div>
                    </td>
                    <td style="color:var(--text-light)"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><span class="course-badge"><?php echo htmlspecialchars($row['course_name'] ?? '—'); ?></span></td>
                    <td><span class="teacher-link"><?php echo htmlspecialchars($row['teacher_name'] ?? '—'); ?></span></td>
                    <td class="date-td"><?php echo $date_display; ?></td>
                    <td>
                        <div class="acts">
                            <a href="../deepa/edit_student.php?id=<?php echo $row['student_id']; ?>" class="btn-edit">Edit</a>
                            <a href="../deepa/delete_student.php?id=<?php echo $row['student_id']; ?>"
                               class="btn-del"
                               onclick="return confirm('Delete <?php echo htmlspecialchars(addslashes($row['full_name'])); ?>?')">Delete</a>
                            <a href="../binu/grades/grade_list.php?student_id=<?php echo $row['student_id']; ?>" class="btn-grade">Grades</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7"><div class="empty-state">👨‍🎓 No students found</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <span class="footer-info">Total: <?php echo $total_all; ?> student<?php echo $total_all!=1?'s':''; ?></span>
            <?php if($total_pages>1): ?>
            <div class="pagination">
                <a href="<?php echo pageUrl($page-1,$search,$filter); ?>" class="page-btn <?php echo $page<=1?'disabled':''; ?>">← Prev</a>
                <?php for($p=1;$p<=$total_pages;$p++)
                    echo '<a href="'.pageUrl($p,$search,$filter).'" class="page-btn '.($p==$page?'active':'').'">'.$p.'</a>'; ?>
                <a href="<?php echo pageUrl($page+1,$search,$filter); ?>" class="page-btn <?php echo $page>=$total_pages?'disabled':''; ?>">Next →</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>