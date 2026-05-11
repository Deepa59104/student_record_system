<?php
// login.php - Login Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

session_start();

// If already logged in go to dashboard
if(isset($_SESSION['teacher_id'])) {
    header('Location: ../deepa/dashboard.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = mysqli_connect('127.0.0.1', 'root', '', 'student_record_system');
    if(!$conn) die('Connection failed: ' . mysqli_connect_error());

    $email    = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if(empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $hashed = md5($password);
        $query  = "SELECT * FROM teacher WHERE email='$email' AND password='$hashed' AND is_active=1";
        $result = mysqli_query($conn, $query);

        if($result && mysqli_num_rows($result) > 0) {
            $teacher = mysqli_fetch_assoc($result);
            $_SESSION['teacher_id']   = $teacher['teacher_id'];
            $_SESSION['teacher_name'] = $teacher['first_name'] . ' ' . $teacher['last_name'];
            $_SESSION['teacher_email'] = $teacher['email'];
            header('Location: ../deepa/dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Edu Team SRS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f0a1e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            color: white;
        }
        .bg-orb1 { position: fixed; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(124,58,237,0.25) 0%, transparent 70%); top: -200px; left: -150px; pointer-events: none; }
        .bg-orb2 { position: fixed; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(168,85,247,0.15) 0%, transparent 70%); bottom: -150px; right: -100px; pointer-events: none; }
        .bg-orb3 { position: fixed; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; }

        .login-wrap {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
            padding: 32px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 36px;
        }
        .logo-icon {
            width: 60px; height: 60px; border-radius: 16px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 800; color: white;
            margin: 0 auto 16px;
            box-shadow: 0 8px 32px rgba(124,58,237,0.5);
        }
        .logo-title { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 6px; }
        .logo-sub { font-size: 13px; color: rgba(255,255,255,0.4); }

        .login-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 32px;
        }

        .card-title { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .card-sub { font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 28px; }

        .alert-error {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; color: rgba(255,255,255,0.6); font-weight: 500; margin-bottom: 7px; }
        input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 12px 14px;
            color: white;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input::placeholder { color: rgba(255,255,255,0.2); }
        input:focus { border-color: rgba(168,85,247,0.6); box-shadow: 0 0 0 3px rgba(124,58,237,0.12); }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            padding: 13px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(124,58,237,0.4);
            transition: opacity 0.2s;
            margin-top: 8px;
        }
        .btn-login:hover { opacity: 0.88; }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: rgba(255,255,255,0.3);
        }
        .login-footer span { color: #a855f7; }
    </style>
</head>
<body>
    <div class="bg-orb1"></div>
    <div class="bg-orb2"></div>
    <div class="bg-orb3"></div>

    <div class="login-wrap">
        <div class="logo-section">
            <div class="logo-icon">E</div>
            <div class="logo-title">Edu Team</div>
            <div class="logo-sub">Student Record System</div>
        </div>

        <div class="login-card">
            <h1 class="card-title">Welcome back 👋</h1>
            <p class="card-sub">Sign in to access your dashboard</p>

            <?php if($error): ?>
                <div class="alert-error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-login">Sign In →</button>
            </form>
        </div>

        <div class="login-footer">
            Developer: <span>Isha</span> | SRS-86 | Edu Team
        </div>
    </div>
</body>
</html>