<?php
session_start();
include 'config.php';

$message = "";

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM employeeslogin WHERE email='$email'");

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

        body{
            font-family: Arial;
            background: #f2f2f2;
        }

        .container{
            width:350px;
            margin:80px auto;
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0px 0px 10px rgba(0,0,0,0.1);
        }

        h2{
            text-align:center;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
        }

        button{
            width:100%;
            padding:12px;
            margin-top:15px;
            background:#28a745;
            color:white;
            border:none;
            cursor:pointer;
        }

        button:hover{
            background:#1e7e34;
        }

        .message{
            text-align:center;
            color:red;
        }

        a{
            text-decoration:none;
        }

    </style>

</head>
<body>

<div class="container">

    <h2>Employee Login</h2>

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