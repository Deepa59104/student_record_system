<?php
// login.php - Login Page
// Developer: Isha | SRS-86
// Project: Edu Team - Student Record System

session_start();

if(isset($_SESSION['teacher_id'])) {
    header('Location: ../deepa/dashboard.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = mysqli_connect('localhost', 'root', '', 'student_record_system');
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
            $_SESSION['teacher_id']    = $teacher['teacher_id'];
            $_SESSION['teacher_name']  = $teacher['first_name'] . ' ' . $teacher['last_name'];
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
            background: #ede8ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            color: #2d1f6e;
        }

        #bgCanvas {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0; pointer-events: none;
        }

        .orb { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; }
        .orb1 { width:500px; height:500px; top:-180px; left:-180px; background:radial-gradient(circle,rgba(168,85,247,0.18) 0%,transparent 70%); }
        .orb2 { width:400px; height:400px; bottom:-150px; right:-150px; background:radial-gradient(circle,rgba(124,58,237,0.15) 0%,transparent 70%); }
        .orb3 { width:300px; height:300px; top:40%; left:-100px; background:radial-gradient(circle,rgba(196,167,255,0.20) 0%,transparent 70%); }
        .orb4 { width:280px; height:280px; top:20%; right:-80px; background:radial-gradient(circle,rgba(167,139,250,0.18) 0%,transparent 70%); }

        .login-wrap {
            position: relative; z-index: 2;
            width: 100%; max-width: 560px;
            padding: 32px;
        }

        /* ── Logo ── */
        .logo-section { text-align: center; margin-bottom: 20px; }

        .logo-icon {
            width: 72px; height: 72px; border-radius: 20px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: 800; color: white;
            margin: 0 auto 14px;
            box-shadow: 0 10px 36px rgba(124,58,237,0.35);
            animation: floatIcon 3s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }

        .logo-title { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #2d1f6e; margin-bottom: 5px; }
        .logo-sub   { font-size: 14px; color: #7c6fa8; }

        /* ── About Edu Team description box ── */
        .about-box {
            background: rgba(255,255,255,0.65);
            border: 1px solid rgba(124,58,237,0.15);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 16px;
            backdrop-filter: blur(8px);
            text-align: center;
        }

        .about-box .about-title {
            font-size: 14px;
            font-weight: 700;
            color: #7c3aed;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .about-box .about-text {
            font-size: 13px;
            color: #5a4a8a;
            line-height: 1.7;
            margin-bottom: 14px;
        }



        /* ── Login card ── */
        .login-card {
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(196,167,255,0.35);
            border-radius: 24px;
            padding: 46px;
            box-shadow: 0 4px 24px rgba(124,58,237,0.10), 0 1px 0 rgba(255,255,255,0.9) inset;
            backdrop-filter: blur(12px);
        }

        .card-title { font-size: 22px; font-weight: 700; margin-bottom: 6px; color: #2d1f6e; }
        .card-sub   { font-size: 14px; color: #7c6fa8; margin-bottom: 28px; }

        .alert-error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            color: #dc2626; padding: 12px 16px;
            border-radius: 12px; font-size: 13px;
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        .form-group { margin-bottom: 18px; }

        label { display: block; font-size: 13px; color: #4a3b8c; font-weight: 600; margin-bottom: 8px; }

        input {
            width: 100%;
            background: #f5f2ff;
            border: 1.5px solid #d4c9f5;
            border-radius: 12px;
            padding: 14px 16px;
            color: #2d1f6e; font-size: 15px;
            font-family: inherit; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        input::placeholder { color: #a99dd4; }
        input:focus {
            border-color: rgba(124,58,237,0.6);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.09);
            background: #fff;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white; border: none;
            padding: 15px; border-radius: 13px;
            font-size: 16px; font-weight: 700; font-family: inherit;
            cursor: pointer; letter-spacing: 0.02em;
            box-shadow: 0 6px 24px rgba(124,58,237,0.35);
            transition: opacity 0.2s, transform 0.15s;
            margin-top: 10px;
        }
        .btn-login:hover  { opacity: 0.88; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            text-align: center; margin-top: 22px;
            font-size: 12px; color: #9b8ec4;
        }
        .login-footer span { color: #7c3aed; font-weight: 600; }
    </style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>
<div class="orb orb3"></div>
<div class="orb orb4"></div>
<canvas id="bgCanvas"></canvas>

<div class="login-wrap">

    <!-- Logo -->
    <div class="logo-section">
        <div class="logo-icon">E</div>
        <div class="logo-title">Edu Team</div>
        <div class="logo-sub">Student Record System</div>
    </div>

    <!-- About Edu Team description -->
    <div class="about-box">
        <div class="about-title">🎓 About Edu Team</div>
        <div class="about-text">
            Edu Team is a modern Student Record System designed to help teachers
            manage student profiles, courses, grades and attendance all in one place.
            Built for simplicity, speed and accuracy — making education management easier every day.
        </div>

    </div>

    <!-- Login card -->
    <div class="login-card">
        <h1 class="card-title">Welcome 👋</h1>
        <p class="card-sub">Sign in to access your dashboard</p>

        <?php if($error): ?>
            <div class="alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       placeholder="your@email.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-login">Sign In →</button>
        </form>
    </div>

    <div class="login-footer">
        Developer: <span>Isha</span> | SRS-86 | Edu Team
    </div>
</div>

<script>
const canvas = document.getElementById('bgCanvas');
const ctx    = canvas.getContext('2d');
let W, H;

function resize(){ W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
function rand(a,b){ return a + Math.random()*(b-a); }
function randInt(a,b){ return Math.floor(rand(a,b)); }

function drawCap(x, y, size, angle, alpha) {
    ctx.save();
    ctx.globalAlpha = alpha;
    ctx.translate(x, y); ctx.rotate(angle);
    const s = size;
    ctx.shadowColor = 'rgba(80,30,160,0.15)'; ctx.shadowBlur = 10;

    ctx.fillStyle = '#9B5FDF';
    ctx.beginPath();
    ctx.moveTo(0,-s*0.38); ctx.lineTo(s*0.75,-s*0.18);
    ctx.lineTo(0,0); ctx.lineTo(-s*0.75,-s*0.18);
    ctx.closePath(); ctx.fill();

    ctx.fillStyle = '#7B3FBF';
    ctx.beginPath(); ctx.rect(-s*0.7,-s*0.18,s*1.4,s*0.18); ctx.fill();

    ctx.fillStyle = '#7B3FBF';
    ctx.beginPath(); ctx.ellipse(0,s*0.05,s*0.38,s*0.16,0,0,Math.PI*2); ctx.fill();
    ctx.fillStyle = '#6B2FAF';
    ctx.beginPath(); ctx.rect(-s*0.38,0,s*0.76,s*0.22); ctx.fill();
    ctx.fillStyle = '#7B3FBF';
    ctx.beginPath(); ctx.ellipse(0,s*0.22,s*0.38,s*0.14,0,0,Math.PI*2); ctx.fill();

    ctx.fillStyle = '#FFD700';
    ctx.beginPath(); ctx.arc(0,-s*0.38,s*0.07,0,Math.PI*2); ctx.fill();

    ctx.strokeStyle='#FFD700'; ctx.lineWidth=s*0.04; ctx.lineCap='round';
    ctx.beginPath();
    ctx.moveTo(s*0.55,-s*0.25);
    ctx.quadraticCurveTo(s*0.75,s*0.1,s*0.6,s*0.3);
    ctx.stroke();

    ctx.fillStyle='#FFD700';
    ctx.beginPath(); ctx.ellipse(s*0.58,s*0.34,s*0.06,s*0.1,0.2,0,Math.PI*2); ctx.fill();
    ctx.restore();
}

function drawConfetti(c){
    ctx.save();
    ctx.globalAlpha=c.alpha; ctx.translate(c.x,c.y); ctx.rotate(c.angle);
    ctx.fillStyle=c.color;
    if(c.dot){ ctx.beginPath(); ctx.arc(0,0,c.w/2,0,Math.PI*2); ctx.fill(); }
    else { ctx.fillRect(-c.w/2,-c.h/2,c.w,c.h); }
    ctx.restore();
}

const GOLD   = ['#FFD700','#FFC200','#FFE066','#DAA520','#F5C518'];
const PURPLE = ['rgba(167,139,250,0.7)','rgba(196,167,255,0.6)','rgba(124,58,237,0.5)'];
let caps=[], confetti=[];

function makeCap(ry){
    return { x:rand(0,W), y:ry?rand(-H,H*0.4):rand(-220,-40),
        size:rand(32,70), angle:rand(-0.7,0.7),
        vy:rand(0.2,0.65), vx:rand(-0.25,0.25),
        spin:rand(-0.01,0.01), alpha:rand(0.55,0.92),
        wobble:rand(0,Math.PI*2), wobbleSpeed:rand(0.008,0.022) };
}

function makeConf(ry){
    const isGold = Math.random()>0.35;
    return { x:rand(0,W), y:ry?rand(-H,H*0.6):rand(-120,-5),
        w:rand(5,15), h:rand(3,8), dot:Math.random()>0.7,
        angle:rand(0,Math.PI*2), vy:rand(0.35,1.1), vx:rand(-0.45,0.45),
        spin:rand(-0.05,0.05), alpha:rand(0.3,0.85),
        color: isGold?GOLD[randInt(0,GOLD.length)]:PURPLE[randInt(0,PURPLE.length)] };
}

function init(){
    resize();
    caps     = Array.from({length:35}, ()=>makeCap(true));
    confetti = Array.from({length:120}, ()=>makeConf(true));
}

function animate(){
    ctx.clearRect(0,0,W,H);
    confetti.forEach(c=>{
        drawConfetti(c);
        c.y+=c.vy; c.x+=c.vx; c.angle+=c.spin;
        if(c.y>H+20) Object.assign(c,makeConf(false));
        if(c.x<-20) c.x=W+20;
        if(c.x>W+20) c.x=-20;
    });
    caps.forEach(c=>{
        c.wobble+=c.wobbleSpeed;
        drawCap(c.x,c.y,c.size,c.angle+Math.sin(c.wobble)*0.9,c.alpha);
        c.y+=c.vy; c.x+=c.vx; c.angle+=c.spin;
        if(c.y>H+90) Object.assign(c,makeCap(false));
        if(c.x<-90) c.x=W+90;
        if(c.x>W+90) c.x=-90;
    });
    requestAnimationFrame(animate);
}

window.addEventListener('resize', resize);
init();
animate();
</script>

</body>
</html>