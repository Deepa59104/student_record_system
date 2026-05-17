<?php
// course_list.php - Course List Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

session_start();

if(!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system');
if(!$conn) die('DB Error: ' . mysqli_connect_error());

$teacher_row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT first_name, last_name FROM teacher WHERE teacher_id = " . (int)$_SESSION['teacher_id']
));
$teacher_name = $teacher_row
    ? $teacher_row['first_name'] . ' ' . $teacher_row['last_name']
    : ($_SESSION['teacher_name'] ?? 'Isha');
$_SESSION['teacher_name'] = $teacher_name;

$search  = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter  = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';
$per_page = 8;
$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset   = ($page - 1) * $per_page;

$total_all = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM course"))['total'];

$cq = "SELECT COUNT(*) as total FROM course WHERE 1=1";
if($search) $cq .= " AND (course_name LIKE '%$search%' OR course_code LIKE '%$search%')";
if($filter === 'active')   $cq .= " AND is_active = 1";
if($filter === 'inactive') $cq .= " AND is_active = 0";

$total_filtered = (int)mysqli_fetch_assoc(mysqli_query($conn, $cq))['total'];
$total_pages    = max(1, ceil($total_filtered / $per_page));

// Main query - counts students linked to each course via student.course_id
// duration_weeks column must exist in the course table:
// ALTER TABLE course ADD COLUMN duration_weeks INT NULL;
$query = "SELECT c.*,
    COALESCE(c.duration_weeks, 0) AS duration_weeks,
    COUNT(s.student_id) AS enrolled_students
    FROM course c
    LEFT JOIN student s ON s.course_id = c.course_id
    WHERE 1=1";
if($search) $query .= " AND (c.course_name LIKE '%$search%' OR c.course_code LIKE '%$search%')";
if($filter === 'active')   $query .= " AND c.is_active = 1";
if($filter === 'inactive') $query .= " AND c.is_active = 0";
$query .= " GROUP BY c.course_id ORDER BY c.course_id ASC LIMIT $per_page OFFSET $offset";

$result = mysqli_query($conn, $query);

function pageUrl($p,$s,$f){
    return 'course_list.php?'.http_build_query(['page'=>$p,'search'=>$s,'filter'=>$f]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Courses — Edu Team SRS</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

/* Light theme matching the dashboard */
:root {
    --purple:      #6C3FC5;
    --purple-nav:  #3B2069;
    --purple-light:#7C4FD5;
    --purple-bg:   #F3EEFF;
    --purple-pale: #EDE6FF;
    --green:       #059669;
    --green-light: #10b981;
    --red:         #ef4444;
    --blue:        #3b82f6;
    --text-dark:   #1A1033;
    --text-mid:    #5A5475;
    --text-light:  #9A93B0;
    --border:      #E2D9F3;
    --white:       #ffffff;
    --bg:          #F0EBFF;
    --card:        #ffffff;
    --input-bg:    #F8F5FF;
    --stat-bg:     #F8F5FF;
}

body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);min-height:100vh;color:var(--text-dark)}

/* ── Navbar ── */
.navbar{position:sticky;top:0;z-index:100;padding:0 40px;height:58px;display:flex;align-items:center;justify-content:space-between;background:var(--purple-nav);box-shadow:0 2px 16px rgba(59,32,105,0.18)}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}
.brand-logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7C4FD5,#5B2EA6);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:white}
.brand-text{font-size:14px;font-weight:700;color:white;letter-spacing:0.01em}
.nav-links{display:flex;align-items:center;gap:28px}
.nav-links a{font-size:13px;font-weight:500;color:rgba(255,255,255,0.55);text-decoration:none;transition:color 0.2s}
.nav-links a:hover{color:white}
.nav-links a.active{color:white;font-weight:700}

/* ── Content ── */
.content{max-width:1160px;margin:0 auto;padding:36px 32px 60px}

/* ── Welcome Banner ── */
.welcome-banner{display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,rgba(108,63,197,0.08),rgba(91,46,166,0.05));border:1px solid rgba(108,63,197,0.15);border-radius:16px;padding:18px 24px;margin-bottom:28px;box-shadow:0 2px 12px rgba(108,63,197,0.06)}
.w-avatar{width:46px;height:46px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--purple),var(--purple-light));display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white}
.w-title{font-size:17px;font-weight:700;color:var(--text-dark);margin-bottom:2px}
.w-title span{color:var(--purple)}
.w-sub{font-size:12px;color:var(--text-light)}

/* ── Alerts ── */
.alert{padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:18px}
.alert-success{background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.25);color:var(--green)}
.alert-error{background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:var(--red)}

/* ── Page header ── */
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.page-title{font-size:26px;font-weight:800;letter-spacing:-0.5px;color:var(--text-dark);margin-bottom:3px}
.page-sub{font-size:12px;color:var(--text-light)}
.add-btn{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--purple),var(--purple-light));color:white;padding:11px 22px;border-radius:12px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 4px 16px rgba(108,63,197,0.25);white-space:nowrap;transition:opacity 0.2s,transform 0.2s}
.add-btn:hover{opacity:0.88;transform:translateY(-1px)}

/* ── Stats ── */
.stats-row{display:flex;gap:14px;margin-bottom:20px}
.stat-box{flex:1;padding:16px 20px;background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 2px 8px rgba(108,63,197,0.05)}
.stat-label{font-size:12px;color:var(--text-light);margin-bottom:6px;font-weight:500}
.stat-val{font-size:24px;font-weight:800;color:var(--purple)}

/* ── Search bar ── */
.search-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.search-wrap{flex:1;min-width:200px;position:relative}
.search-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-light);pointer-events:none}
.search-input{width:100%;padding:11px 14px 11px 38px;background:var(--card);border:1.5px solid var(--border);border-radius:12px;font-size:13px;color:var(--text-dark);font-family:inherit;outline:none;transition:border-color 0.2s,box-shadow 0.2s}
.search-input::placeholder{color:var(--text-light)}
.search-input:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(108,63,197,0.10)}
.filter-select{padding:11px 32px 11px 14px;min-width:155px;background:var(--card);border:1.5px solid var(--border);border-radius:12px;font-size:13px;color:var(--text-dark);font-family:inherit;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239A93B0' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;transition:border-color 0.2s}
.filter-select:focus{border-color:var(--purple)}
.btn-search{padding:11px 22px;background:linear-gradient(135deg,var(--purple),var(--purple-light));color:white;border:none;border-radius:12px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:opacity 0.2s}
.btn-search:hover{opacity:0.88}
.btn-clear{padding:11px 18px;background:var(--card);border:1.5px solid var(--border);color:var(--text-mid);border-radius:12px;font-size:13px;text-decoration:none;font-weight:500;transition:border-color 0.2s}
.btn-clear:hover{border-color:var(--purple);color:var(--purple)}

/* ── Table card ── */
.table-card{background:var(--card);border:1px solid var(--border);border-radius:18px;overflow:hidden;box-shadow:0 4px 20px rgba(108,63,197,0.07)}
.table-top{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid var(--border)}
.table-top h3{font-size:14px;font-weight:700;color:var(--text-dark)}
.table-top span{font-size:12px;color:var(--text-light)}

table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:var(--purple-pale)}
th{text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:var(--purple);text-transform:uppercase;letter-spacing:0.7px}
td{padding:13px 16px;border-top:1px solid var(--border);color:var(--text-mid);vertical-align:middle}
tbody tr:hover td{background:rgba(108,63,197,0.03)}

.course-name{font-weight:700;color:var(--text-dark);display:block;margin-bottom:3px}
.desc-text{font-size:11.5px;color:var(--text-light);line-height:1.4;margin-top:2px;max-width:280px}
.code-badge{display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;background:rgba(108,63,197,0.10);color:var(--purple);font-family:monospace}

/* Duration badge */
.duration-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(59,130,246,0.10);color:var(--blue)}

/* Enrolled students badge */
.enrolled-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(5,150,105,0.10);color:var(--green)}
.enrolled-badge.zero{background:rgba(156,163,175,0.12);color:#9ca3af}

.status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.status-active{background:rgba(5,150,105,0.10);color:var(--green)}
.status-inactive{background:rgba(239,68,68,0.10);color:var(--red)}

.action-wrap{display:flex;gap:7px}
.btn-edit{background:rgba(59,130,246,0.10);color:var(--blue);padding:5px 14px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600;transition:background 0.2s}
.btn-edit:hover{background:rgba(59,130,246,0.22)}
.btn-delete{background:rgba(239,68,68,0.10);color:var(--red);padding:5px 14px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600;transition:background 0.2s}
.btn-delete:hover{background:rgba(239,68,68,0.22)}

.empty-state{text-align:center;padding:56px 20px;color:var(--text-light);font-size:14px}

/* ── Pagination ── */
.table-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-top:1px solid var(--border)}
.footer-info{font-size:12px;color:var(--text-light)}
.pagination{display:flex;align-items:center;gap:6px}
.page-btn{min-width:32px;height:32px;padding:0 10px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;background:var(--card);border:1.5px solid var(--border);color:var(--text-mid);transition:all 0.2s}
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
        <a href="course_list.php" class="active">Courses</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="content">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="w-avatar"><?php echo strtoupper(substr($teacher_name,0,1)); ?></div>
        <div>
            <div class="w-title">👋 Welcome, <span><?php echo htmlspecialchars($teacher_name); ?>!</span></div>
            <div class="w-sub">Course Management — Developer: Isha | SRS-86</div>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Courses</h1>
            <p class="page-sub">Developer: Isha | SRS-86</p>
        </div>
        <a href="add_course.php" class="add-btn">+ Add New Course</a>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Total Courses</div>
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

    <!-- Search & Filter -->
    <form method="GET" action="course_list.php" class="search-bar">
        <div class="search-wrap">
            <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" class="search-input"
                   placeholder="Search by course name or code..."
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <select name="filter" class="filter-select">
            <option value="">All Courses</option>
            <option value="active"   <?php echo $filter==='active'  ?'selected':''; ?>>Active</option>
            <option value="inactive" <?php echo $filter==='inactive'?'selected':''; ?>>Inactive</option>
        </select>
        <button type="submit" class="btn-search">Search</button>
        <a href="course_list.php" class="btn-clear">Clear</a>
    </form>

    <!-- Table -->
    <div class="table-card">
        <div class="table-top">
            <h3>All Courses</h3>
            <span>Showing <?php echo $total_filtered>0?$offset+1:0; ?>–<?php echo min($offset+$per_page,$total_filtered); ?> of <?php echo $total_filtered; ?> courses</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:46px">ID</th>
                    <th>Course Name & Description</th>
                    <th>Code</th>
                    <th>Duration</th>
                    <th>Enrolled Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0):
                    $row_num = $offset + 1;
                    while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row_num++; ?></td>
                    <td>
                        <span class="course-name"><?php echo htmlspecialchars($row['course_name']); ?></span>
                        <?php if(!empty($row['description'])): ?>
                            <div class="desc-text"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 80, '...')); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="code-badge"><?php echo htmlspecialchars($row['course_code'] ?? 'N/A'); ?></span></td>

                    <!-- Duration weeks column -->
                    <td>
                        <?php if(!empty($row['duration_weeks']) && $row['duration_weeks'] > 0): ?>
                            <span class="duration-badge">
                                🕐 <?php echo (int)$row['duration_weeks']; ?> week<?php echo $row['duration_weeks']!=1?'s':''; ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#ccc;font-size:12px">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Enrolled students count -->
                    <td>
                        <?php $enrolled = (int)$row['enrolled_students']; ?>
                        <span class="enrolled-badge <?php echo $enrolled===0?'zero':''; ?>">
                            👥 <?php echo $enrolled; ?> student<?php echo $enrolled!==1?'s':''; ?>
                        </span>
                    </td>

                    <td>
                        <?php if($row['is_active']): ?>
                            <span class="status-badge status-active">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-wrap">
                            <a href="edit_course.php?id=<?php echo $row['course_id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete_course.php?id=<?php echo $row['course_id']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Delete <?php echo htmlspecialchars($row['course_name'],ENT_QUOTES); ?>?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7"><div class="empty-state">📚 No courses found</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <span class="footer-info">Total: <?php echo $total_all; ?> course<?php echo $total_all!=1?'s':''; ?></span>
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