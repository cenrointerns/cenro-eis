<?php
session_start();
include 'config.php';

$message = "";

if(!isset($_SESSION['reset_email'])){
    header("Location: reset_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if(isset($_POST['verify'])){

    // REMOVE SPACES
    $otp = trim($_POST['otp']);

    // DEBUGGING QUERY
    $query = mysqli_query($conn, "
        SELECT * FROM employees_login 
        WHERE email='$email'
        AND reset_otp='$otp'
    ");

    if(mysqli_num_rows($query) > 0){

        $row = mysqli_fetch_assoc($query);

        // CHECK EXPIRY MANUALLY
        $current_time = date("Y-m-d H:i:s");

        if($row['otp_expiry'] > $current_time){

            $_SESSION['otp_verified'] = true;

            header("Location: new_password.php");
            exit();

        }else{

            $message = "OTP already expired!";
        }

    }else{

        $message = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Verify OTP</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#f1f1f1;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .container{
            width:350px;
            background:white;
            padding:30px;
            border-radius:12px;
            box-shadow:0px 0px 15px rgba(0,0,0,0.2);
        }

        h2{
            text-align:center;
            margin-bottom:20px;
            color:#2e7d32;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            border:1px solid #ccc;
            border-radius:8px;
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
        }

        button:hover{
            background:#1b5e20;
        }

        .message{
            text-align:center;
            color:red;
            margin-bottom:10px;
            font-weight:bold;
        }

    </style>

</head>
<body>

<div class="container">

    <h2>Verify OTP</h2>

    <p class="message"><?php echo $message; ?></p>

    <form method="POST">

        <input type="text" 
               name="otp" 
               placeholder="Enter 6-digit OTP"
               maxlength="6"
               required>

        <button type="submit" name="verify">
            Verify OTP
        </button>

    </form>

</div>

</body>
</html>




