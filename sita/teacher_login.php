<?php
session_start();

$error = "";

if(isset($_POST['login'])) {

    $email = trim($_POST['teacher_email'] ?? '');
    $password = trim($_POST['teacher_password'] ?? '');

    if($email == "sita@email.com" && $password == "sita123") {

        $_SESSION['teacher_id'] = 1;
        $_SESSION['teacher_name'] = "Sita";

        header("Location: teacher_list.php");
        exit();

    } else {
        $error = "Wrong Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Login</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#f5f3ff;
    font-family:'Plus Jakarta Sans',sans-serif;
}

.box{
    width:430px;
    background:#ffffff;
    padding:42px;
    border-radius:24px;
    border:1px solid #ddd6fe;
    box-shadow:0 8px 24px rgba(0,0,0,0.08);
}

.logo{
    text-align:center;
    color:#7c3aed;
    font-weight:800;
    margin-bottom:10px;
    font-size:18px;
}

h1{
    text-align:center;
    color:#1e1b4b;
    margin-bottom:12px;
    font-size:42px;
    font-weight:800;
}

.sub{
    text-align:center;
    color:#6b7280;
    margin-bottom:26px;
    font-size:14px;
}

input{
    width:100%;
    padding:15px;
    margin-bottom:18px;
    border:1px solid #ddd6fe;
    border-radius:12px;
    font-size:16px;
    color:#111827;
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    color:white;
    font-size:18px;
    font-weight:800;
    cursor:pointer;
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:10px;
    text-align:center;
    margin-bottom:18px;
}
</style>
</head>

<body>

<div class="box">

<div class="logo">🎓 Edu Team</div>

<h1>Teacher Login</h1>

<div class="sub">Login using your teacher account</div>

<?php if($error != ""): ?>
<div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" autocomplete="off">

<input type="text" name="teacher_email" placeholder="Enter Teacher Email" required>

<input type="password" name="teacher_password" placeholder="Enter Teacher Password" required>

<button type="submit" name="login">Login</button>

</form>

</div>

</body>
</html>