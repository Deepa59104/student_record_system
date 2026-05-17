<?php
// Start the session to store login state
session_start();

// Check if the form was submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if credentials are correct
    if($username == 'satinder' && $password == 'sat123') {
        // Set session variable to mark user as logged in
        $_SESSION['logged_in'] = true;
        // Redirect to dashboard page
        header('Location: dashboard.php');
        exit();
    } else {
        // Show error message if credentials are wrong
        $error = "❌ Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance Login</title>
<style>
/* Reset default browser styles */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }

/* Full height background */
body {
    background: #f0eef6;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Top navigation bar */
.navbar {
    background: #4a2c6e;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.navbar .brand {
    color: white;
    font-size: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.navbar .brand span {
    background: white;
    color: #4a2c6e;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
}
.navbar .system-label { color: #ccc; font-size: 14px; }

/* Center the login form on the page */
.page-center {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

/* Circular avatar icon */
.avatar {
    background: #4a2c6e;
    color: white;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    margin-bottom: 20px;
}

/* Page heading styles */
.page-title { font-size: 28px; font-weight: bold; color: #2d2d2d; margin-bottom: 6px; }
.page-subtitle { color: #888; font-size: 14px; margin-bottom: 30px; }

/* White login card */
.card {
    background: white;
    border-radius: 12px;
    padding: 35px 40px;
    width: 100%;
    max-width: 430px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}

/* Form label styles */
label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #444;
    margin-bottom: 6px;
    margin-top: 18px;
}
label:first-of-type { margin-top: 0; }

/* Input field wrapper with icon */
.input-wrap { position: relative; }
.input-wrap .icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 15px;
}
.input-wrap input {
    width: 100%;
    padding: 11px 12px 11px 36px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    outline: none;
    transition: border 0.2s;
}
/* Highlight input on focus */
.input-wrap input:focus { border-color: #4a2c6e; }

/* Login submit button */
.btn-login {
    width: 100%;
    margin-top: 24px;
    padding: 12px;
    background: #4a2c6e;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-login:hover { background: #3a1f58; }

/* Error message box */
.error {
    background: #ffe5e5;
    color: #c0392b;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 16px;
    text-align: center;
}

/* Credentials hint box at the bottom */
.hint {
    margin-top: 20px;
    background: #f5f3fb;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 12px;
    color: #666;
    line-height: 1.8;
}
.hint strong { color: #4a2c6e; }
</style>
</head>
<body>

<!-- Top navigation bar -->
<nav class="navbar">
    <a class="brand" href="#">
        <span>🎓</span> EduTeam
    </a>
    <span class="system-label">Student Record System</span>
</nav>

<!-- Centered login form -->
<div class="page-center">
    <div class="avatar">🎓</div>
    <div class="page-title">Attendance Portal</div>
    <div class="page-subtitle">Sign in to access your attendance dashboard</div>

    <div class="card">
        <!-- Show error message if login failed -->
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

        <!-- Login form submits to same page via POST -->
        <form method="POST">
            <label>Username</label>
            <div class="input-wrap">
                <span class="icon">👤</span>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>

            <label>Password</label>
            <div class="input-wrap">
                <span class="icon">🔒</span>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn-login">➜ &nbsp;Sign In</button>
        </form>

        <!-- Show test credentials for demo -->
        <div class="hint">
            ℹ️ &nbsp;<strong>Credentials:</strong><br>
            Username: <strong>satinder</strong> &nbsp;/&nbsp; Password: <strong>sat123</strong>
        </div>
    </div>
</div>

</body>
</html>