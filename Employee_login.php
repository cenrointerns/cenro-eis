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

            /* BACKGROUND IMAGE */
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
        }
        p{
            text-align:center;
            margin-bottom:20px;
             color:white;
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
            color:red;
            margin-bottom:10px;
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

    </style>

</head>
<body>

<div class="container">

    <!-- LOGO -->
    <img src="assets/images/DENR_logo.png" class="logo" alt="Logo">

    <h2>Employee Login</h2>
    <p> DENR-CENRO MANOLO FORTICH</p>


    <p class="message"><?php echo $message; ?></p>

    <form method="POST">

        <input type="email" name="email" placeholder="Email Address" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Login</button>

    </form>

    <p style="text-align:center;margin-top:15px;">
        No account yet?
        <a href="Employee_register.php">Create Account</a>
    </p>

</div>

</body>
</html>