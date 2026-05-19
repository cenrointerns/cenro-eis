<?php
session_start();
include 'config.php';

$message = "";

if(!isset($_SESSION['otp_verified'])){
    header("Location: reset_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if(isset($_POST['update_password'])){

    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    mysqli_query($conn, "UPDATE employees_login 
        SET password='$new_password',
            reset_otp=NULL,
            otp_expiry=NULL
        WHERE email='$email'");

    session_destroy();

    echo "<script>
        alert('Password updated successfully!');
        window.location.href='Employee_login.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create New Password</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    padding:20px;
    background: linear-gradient(to bottom, #dbe7d3, #a8c3a0, #7da87b);
}

/* 🌿 Soft Nature Floating Leaves */
.leaves{
    position:absolute;
    width:100%;
    height:100%;
    overflow:hidden;
    top:0;
    left:0;
    z-index:1;
}

.leaves span{
    position:absolute;
    display:block;
    width:20px;
    height:20px;
    background: rgba(255,255,255,0.25);
    border-radius: 50% 0 50% 0;
    transform: rotate(45deg);
    animation: floatLeaves 18s linear infinite;
    bottom:-150px;
}

/* different leaf sizes */
.leaves span:nth-child(1){left:10%; width:25px; height:25px; animation-duration:15s;}
.leaves span:nth-child(2){left:20%; width:15px; height:15px; animation-duration:22s;}
.leaves span:nth-child(3){left:35%; width:30px; height:30px; animation-duration:18s;}
.leaves span:nth-child(4){left:50%; width:18px; height:18px; animation-duration:25s;}
.leaves span:nth-child(5){left:65%; width:22px; height:22px; animation-duration:20s;}
.leaves span:nth-child(6){left:80%; width:28px; height:28px; animation-duration:17s;}

@keyframes floatLeaves{
    0%{
        transform: translateY(0) rotate(0deg);
        opacity:0.8;
    }
    100%{
        transform: translateY(-1000px) rotate(360deg);
        opacity:0;
    }
}

/* Container */
.container{
    position:relative;
    z-index:2;
    width:100%;
    max-width:420px;
}

/* Card */
.card{
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(12px);
    border-radius:25px;
    padding:40px 30px;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
    animation: fadeIn 1s ease;
    border:1px solid rgba(255,255,255,0.4);
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

/* Icon */
.icon-box{
    width:85px;
    height:85px;
    margin:auto;
    border-radius:50%;
    background: linear-gradient(135deg, #6fbf73, #3d7d3a);
    display:flex;
    justify-content:center;
    align-items:center;
    margin-bottom:20px;
}

.icon-box i{
    font-size:38px;
    color:#fff;
}

/* Text */
h2{
    text-align:center;
    font-size:26px;
    color:#2f4f2f;
}

.subtitle{
    text-align:center;
    font-size:13px;
    color:#3e5f3e;
    margin-bottom:25px;
}

/* Input */
.input-group{
    position:relative;
    margin-bottom:25px;
}

.input-group input{
    width:100%;
    padding:14px 50px 14px 15px;
    border:none;
    border-radius:14px;
    outline:none;
    background:rgba(255,255,255,0.7);
    font-size:15px;
}

.input-group input:focus{
    box-shadow:0 0 8px rgba(80,120,80,0.4);
}

/* Eye */
.toggle-password{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#3d5c3d;
}

/* Strength */
.password-strength{
    font-size:13px;
    margin-top:-15px;
    margin-bottom:20px;
    color:#2f4f2f;
}

/* Button */
.btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    background: linear-gradient(135deg, #6fbf73, #3d7d3a);
    color:#fff;
    font-size:15px;
    cursor:pointer;
    transition:0.3s;
}

.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(60,120,60,0.3);
}

/* Responsive */
@media(max-width:480px){
    .card{
        padding:30px 20px;
    }
    h2{
        font-size:22px;
    }
}
</style>
</head>

<body>

<div class="leaves">
    <span></span><span></span><span></span>
    <span></span><span></span><span></span>
</div>

<div class="container">

<form method="POST">

<div class="card">

    <div class="icon-box">
        <i class="fas fa-leaf"></i>
    </div>

    <h2>Create New Password</h2>
    <p class="subtitle">Set a strong and natural-secure password 🌿</p>

    <div class="input-group">
        <input type="password" name="new_password" id="password" placeholder="New Password" required>

        <span class="toggle-password" onclick="togglePassword()">
            <i class="fas fa-eye" id="eyeIcon"></i>
        </span>
    </div>

    <div class="password-strength" id="strengthText">
        Password Strength: Weak
    </div>

    <button type="submit" name="update_password" class="btn">
        <i class="fas fa-seedling"></i> Update Password
    </button>

</div>

</form>

</div>

<script>
function togglePassword(){
    const password = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    if(password.type === "password"){
        password.type = "text";
        eyeIcon.classList.replace("fa-eye","fa-eye-slash");
    }else{
        password.type = "password";
        eyeIcon.classList.replace("fa-eye-slash","fa-eye");
    }
}

const input = document.getElementById("password");
const strengthText = document.getElementById("strengthText");

input.addEventListener("input", function(){
    const v = input.value;

    if(v.length < 6){
        strengthText.textContent = "Password Strength: Weak 🌱";
        strengthText.style.color = "#b23b3b";
    }
    else if(v.length >= 8 && /[A-Z]/.test(v) && /[0-9]/.test(v)){
        strengthText.textContent = "Password Strength: Strong 🌿";
        strengthText.style.color = "#2f6f2f";
    }
    else{
        strengthText.textContent = "Password Strength: Medium 🌼";
        strengthText.style.color = "#6b6b2f";
    }
});
</script>

</body>
</html>