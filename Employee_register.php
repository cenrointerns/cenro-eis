<?php
include 'config.php';

$message = "";

if(isset($_POST['register'])){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // check email
    $check = mysqli_query($conn, "SELECT * FROM employeeslogin WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){

        $message = "Email already exists!";

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn, "INSERT INTO employeeslogin(fullname,email,password)
        VALUES('$fullname','$email','$hashed_password')");

        if($insert){
            $message = "Account created successfully!";
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Employee Register</title>
<style>
body{font-family:Arial;background:#f2f2f2;}
.container{width:350px;margin:80px auto;background:#fff;padding:30px;border-radius:10px;}
input{width:100%;padding:10px;margin-top:10px;}
button{width:100%;padding:10px;margin-top:10px;background:blue;color:#fff;border:none;}
.message{text-align:center;color:red;}
a{text-decoration:none;}
</style>
</head>
<body>

<div class="container">

<h2>Create Account</h2>

<p class="message"><?php echo $message; ?></p>

<form method="POST">

<input type="text" name="fullname" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="register">Register</button>

</form>

<p style="text-align:center;">
Already have account? <a href="Employee_login.php">Login</a>
</p>

</div>

</body>
</html>