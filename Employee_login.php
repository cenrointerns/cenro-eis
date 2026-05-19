<?php
session_start();
include 'config.php';

$message = "";

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM employees_login WHERE email='$email'");

    if(mysqli_num_rows($query) > 0){

        $row = mysqli_fetch_assoc($query);

        if(password_verify($password, $row['password'])){

            $_SESSION['employee_id'] = $row['employee_id'];
            $_SESSION['fullname'] = $row['fullname'];

            header("Location: Employee_dashboard.php");
            exit();

        } else {

            $message = "Incorrect password!";
        }

    } else {

        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Login</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: Arial, sans-serif;

            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
            url('assets/images/bg_cenro.jpeg');

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;
        }

        .container{
            width:350px;
            background:rgba(255, 255, 255, 0.15);
            padding:30px;
            border-radius:15px;
            box-shadow:0px 0px 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(5px);
        }

        h2{
            text-align:center;
            margin-bottom:20px;
            color:white;
            font-size:22px;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            border:1px solid #ccc;
            border-radius:8px;
            outline:none;
        }

        input:focus{
            border-color:#2e7d32;
        }

        .password-container{
            position:relative;
            width:100%;
        }

        .toggle-password{
            position:absolute;
            right:15px;
            top:50%;
            transform:translateY(-50%);
            cursor:pointer;
            color:#555;
            font-size:14px;
            user-select:none;
        }

        button{
            width:100%;
            padding:12px;
            margin-top:15px;
            background:#2e7d32;
            color:white;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:16px;
            transition:0.3s;
        }

        button:hover{
            background:#1b5e20;
        }

        .message{
            text-align:center;
            color:#ff5252;
            margin-bottom:10px;
            font-weight:bold;
        }

        a{
            text-decoration:none;
            color:whitesmoke;
            font-weight:bold;
        }

        a:hover{
            text-decoration:underline;
        }

        .logo{
            display:block;
            margin:0 auto 15px;
            width:90px;
            height:90px;
            border-radius:50%;
            border:4px solid #2e7d32;
            object-fit:cover;
            background:white;
            padding:5px;
        }

        .forgot-password{
            text-align:right;
            margin-top:8px;
        }

        .forgot-password a{
            font-size:14px;
            color:#fff;
        }

        @media(max-width:400px){

            .container{
                width:90%;
                padding:20px;
            }

            h2{
                font-size:18px;
            }

        }

    </style>

</head>
<body>

<div class="container">

    <!-- LOGO -->
    <img src="assets/images/DENR_logo.png" class="logo" alt="Logo">

    <h2>DENR-CENRO<br>Manolo Fortich, Bukidnon</h2>

    <p class="message"><?php echo $message; ?></p>

    <form method="POST">

        <input type="email" name="email" placeholder="Email Address" required>

        <div class="password-container">

            <input type="password" name="password" id="password" placeholder="Password" required>

            <span class="toggle-password" onclick="togglePassword()">
                Show
            </span>

        </div>

        <div class="forgot-password">
            <a href="reset_password.php">Forgot Password?</a>
        </div>

        <button type="submit" name="login">Login</button>

    </form>

    <p style="text-align:center;margin-top:15px;color:white;">
        No account yet?
        <a href="Employee_register.php">Create Account</a>
    </p>

</div>

<script>

function togglePassword(){

    var passwordField = document.getElementById("password");
    var toggleText = document.querySelector(".toggle-password");

    if(passwordField.type === "password"){

        passwordField.type = "text";
        toggleText.innerHTML = "Hide";

    }else{

        passwordField.type = "password";
        toggleText.innerHTML = "Show";
    }
}

</script>

</body>
</html>