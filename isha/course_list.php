<?php
// course_list.php - Course List Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

session_start();

// Redirect to login if not logged in
if(!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system');
if(!$conn) die('DB Error: ' . mysqli_connect_error());

// FIX: Fetch teacher name directly from DB using session teacher_id
// This ensures the correct name always shows regardless of session data
$teacher_row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT first_name, last_name FROM teacher WHERE teacher_id = " . (int)$_SESSION['teacher_id']
));
$teacher_name = $teacher_row
    ? $teacher_row['first_name'] . ' ' . $teacher_row['last_name']
    : ($_SESSION['teacher_name'] ?? 'Isha');

// Also update the session to keep it in sync
$_SESSION['teacher_name'] = $teacher_name;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';

$per_page = 8;
$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset   = ($page - 1) * $per_page;

// Count all
$total_all = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM course"))['total'];

// Count filtered
$cq = "SELECT COUNT(*) as total FROM course WHERE 1=1";
if($search) $cq .= " AND (course_name LIKE '%$search%' OR course_code LIKE '%$search%')";
if($filter === 'active')   $cq .= " AND is_active = 1";
if($filter === 'inactive') $cq .= " AND is_active = 0";
$total_filtered = (int)mysqli_fetch_assoc(mysqli_query($conn, $cq))['total'];
$total_pages    = max(1, ceil($total_filtered / $per_page));

// Main query
$query = "SELECT * FROM course WHERE 1=1";
if($search) $query .= " AND (course_name LIKE '%$search%' OR course_code LIKE '%$search%')";
if($filter === 'active')   $query .= " AND is_active = 1";
if($filter === 'inactive') $query .= " AND is_active = 0";
$query .= " ORDER BY course_id ASC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($conn, $query);

function pageUrl($p,$s,$f){return 'course_list.php?'.http_build_query(['page'=>$p,'search'=>$s,'filter'=>$f]);}
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
body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f0a1e;min-height:100vh;color:white}
.bg-orb1{position:fixed;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.2) 0%,transparent 70%);top:-200px;left:-150px;pointer-events:none}
.bg-orb2{position:fixed;width:450px;height:450px;border-radius:50%;background:radial-gradient(circle,rgba(59,130,246,0.1) 0%,transparent 70%);bottom:-120px;right:-100px;pointer-events:none}
.navbar{position:sticky;top:0;z-index:100;padding:0 40px;height:62px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.07);background:rgba(15,10,30,0.88);backdrop-filter:blur(14px)}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}
.brand-logo{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white}
.brand-text{font-size:14px;font-weight:700;color:white}
.nav-links{display:flex;align-items:center;gap:24px}
.nav-links a{font-size:13px;font-weight:500;color:rgba(255,255,255,0.45);text-decoration:none;transition:color 0.2s}
.nav-links a:hover{color:white}
.nav-links a.active{color:#10b981}
.content{position:relative;z-index:1;max-width:1140px;margin:0 auto;padding:36px 32px 60px}
.welcome-banner{display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.08));border:1px solid rgba(16,185,129,0.25);border-radius:16px;padding:20px 26px;margin-bottom:32px}
.w-avatar{width:48px;height:48px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:white}
.w-title{font-size:18px;font-weight:700;margin-bottom:3px}
.w-title span{color:#10b981}
.w-sub{font-size:13px;color:rgba(255,255,255,0.38)}
.alert{padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:20px}
.alert-success{background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#34d399}
.alert-error{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#f87171}
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px}
.page-title{font-size:26px;font-weight:800;letter-spacing:-0.5px;margin-bottom:4px}
.page-sub{font-size:13px;color:rgba(255,255,255,0.3)}
.add-btn{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#059669,#10b981);color:white;padding:11px 22px;border-radius:12px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 4px 18px rgba(16,185,129,0.3);white-space:nowrap}
.add-btn:hover{opacity:0.88}
.stats-row{display:flex;gap:14px;margin-bottom:22px}
.stat-box{flex:1;padding:16px 20px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:14px}
.stat-label{font-size:12px;color:rgba(255,255,255,0.3);margin-bottom:5px}
.stat-val{font-size:22px;font-weight:700;color:#10b981}
.search-bar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;align-items:center}
.search-wrap{flex:1;min-width:200px;position:relative}
.search-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.25);pointer-events:none}
.search-input{width:100%;padding:11px 14px 11px 38px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.09);border-radius:12px;font-size:13px;color:white;font-family:inherit;outline:none}
.search-input::placeholder{color:rgba(255,255,255,0.2)}
.search-input:focus{border-color:rgba(16,185,129,0.55)}
.filter-select{padding:11px 32px 11px 14px;min-width:165px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.09);border-radius:12px;font-size:13px;color:white;font-family:inherit;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23888' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center}
.filter-select option{background:#1a0d35}
.btn-search{padding:11px 22px;background:linear-gradient(135deg,#059669,#10b981);color:white;border:none;border-radius:12px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer}
.btn-clear{padding:11px 18px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.09);color:rgba(255,255,255,0.5);border-radius:12px;font-size:13px;text-decoration:none;font-weight:500}
.table-card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:18px;overflow:hidden}
.table-top{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid rgba(255,255,255,0.05)}
.table-top h3{font-size:14px;font-weight:600;color:rgba(255,255,255,0.7)}
.table-top span{font-size:12px;color:rgba(255,255,255,0.3)}
table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:rgba(255,255,255,0.04)}
th{text-align:left;padding:11px 18px;font-size:11px;font-weight:600;color:rgba(255,255,255,0.28);text-transform:uppercase;letter-spacing:0.7px}
td{padding:13px 18px;border-top:1px solid rgba(255,255,255,0.04);color:rgba(255,255,255,0.75);vertical-align:middle}
tbody tr:hover td{background:rgba(16,185,129,0.05)}
.course-name{font-weight:600;color:white}
.code-badge{display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(16,185,129,0.15);color:#34d399;font-family:monospace}
.status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:500}
.status-active{background:rgba(16,185,129,0.15);color:#34d399}
.status-inactive{background:rgba(239,68,68,0.15);color:#f87171}
.action-wrap{display:flex;gap:7px}
.btn-edit{background:rgba(59,130,246,0.16);color:#60a5fa;padding:5px 15px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600}
.btn-edit:hover{background:rgba(59,130,246,0.3)}
.btn-delete{background:rgba(239,68,68,0.16);color:#f87171;padding:5px 15px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600}
.btn-delete:hover{background:rgba(239,68,68,0.3)}
.empty-state{text-align:center;padding:56px 20px;color:rgba(255,255,255,0.2);font-size:14px}
.table-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-top:1px solid rgba(255,255,255,0.05)}
.footer-info{font-size:12px;color:rgba(255,255,255,0.28)}
.pagination{display:flex;align-items:center;gap:6px}
.page-btn{min-width:32px;height:32px;padding:0 10px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.5)}
.page-btn:hover{background:rgba(255,255,255,0.1);color:white}
.page-btn.active{background:linear-gradient(135deg,#059669,#10b981);border-color:transparent;color:white}
.page-btn.disabled{opacity:0.3;pointer-events:none}
</style>
</head>
<body>
<div class="bg-orb1"></div>
<div class="bg-orb2"></div>
<nav class="navbar">
<a class="brand" href="../deepa/dashboard.php"><div class="brand-logo">E</div><span class="brand-text">Edu Team – Student Record System</span></a>
<div class="nav-links">
<a href="../deepa/dashboard.php">Dashboard</a>
<a href="course_list.php" class="active">Courses</a>
<a href="logout.php">Logout</a>
</div>
</nav>
<div class="content">
<div class="welcome-banner">
<div class="w-avatar"><?php echo strtoupper(substr($teacher_name,0,1)); ?></div>
<div>
<div class="w-title">👋 Welcome, <span><?php echo htmlspecialchars($teacher_name); ?>!</span></div>
<div class="w-sub">Course Management — Developer: Isha | SRS-86</div>
</div>
</div>
<?php if(isset($_GET['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div><?php endif; ?>
<?php if(isset($_GET['error'])): ?><div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
<div class="page-header">
<div><h1 class="page-title">Courses</h1><p class="page-sub">Developer: Isha | SRS-86</p></div>
<a href="add_course.php" class="add-btn">+ Add New Course</a>
</div>
<div class="stats-row">
<div class="stat-box"><div class="stat-label">Total Courses</div><div class="stat-val"><?php echo $total_all; ?></div></div>
<div class="stat-box"><div class="stat-label">Showing</div><div class="stat-val"><?php echo $total_filtered; ?></div></div>
<div class="stat-box"><div class="stat-label">Current Page</div><div class="stat-val"><?php echo $page; ?> / <?php echo $total_pages; ?></div></div>
</div>
<form method="GET" action="course_list.php" class="search-bar">
<div class="search-wrap">
<svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
<input type="text" name="search" class="search-input" placeholder="Search by course name or code..." value="<?php echo htmlspecialchars($search); ?>">
</div>
<select name="filter" class="filter-select">
<option value="">All Courses</option>
<option value="active" <?php echo $filter==='active'?'selected':''; ?>>Active</option>
<option value="inactive" <?php echo $filter==='inactive'?'selected':''; ?>>Inactive</option>
</select>
<button type="submit" class="btn-search">Search</button>
<a href="course_list.php" class="btn-clear">Clear</a>
</form>
<div class="table-card">
<div class="table-top">
<h3>All Courses</h3>
<span>Showing <?php echo $total_filtered>0?$offset+1:0; ?>–<?php echo min($offset+$per_page,$total_filtered); ?> of <?php echo $total_filtered; ?> courses</span>
</div>
<table>
<thead>
<tr>
<th style="width:46px">ID</th>
<th>Course Name</th>
<th>Course Code</th>
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
<td><span class="course-name"><?php echo htmlspecialchars($row['course_name']); ?></span></td>
<td><span class="code-badge"><?php echo htmlspecialchars($row['course_code'] ?? 'N/A'); ?></span></td>
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
<a href="delete_course.php?id=<?php echo $row['course_id']; ?>" class="btn-delete" onclick="return confirm('Delete <?php echo htmlspecialchars($row['course_name'],ENT_QUOTES); ?>?')">Delete</a>
</div>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="5"><div class="empty-state">No courses found</div></td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="table-footer">
<span class="footer-info">Total: <?php echo $total_all; ?> course<?php echo $total_all!=1?'s':''; ?></span>
<?php if($total_pages>1): ?>
<div class="pagination">
<a href="<?php echo pageUrl($page-1,$search,$filter); ?>" class="page-btn <?php echo $page<=1?'disabled':''; ?>">← Prev</a>
<?php for($p=1;$p<=$total_pages;$p++) echo '<a href="'.pageUrl($p,$search,$filter).'" class="page-btn '.($p==$page?'active':'').'">'.$p.'</a>'; ?>
<a href="<?php echo pageUrl($page+1,$search,$filter); ?>" class="page-btn <?php echo $page>=$total_pages?'disabled':''; ?>">Next →</a>
</div>
<?php endif; ?>
</div>
</div>
</div>
</body>
</html>