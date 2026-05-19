<?php
session_start();
include 'config.php';

date_default_timezone_set('Asia/Manila');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$message = "";

if(isset($_POST['send_otp'])){

    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));

    // CHECK IF EMAIL EXISTS
    $check = mysqli_query($conn, "SELECT * FROM employees_login WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){

        // GENERATE 6 DIGIT OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // OTP EXPIRY 10 MINUTES
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // SAVE OTP
        $update = mysqli_query($conn, "
            UPDATE employees_login 
            SET 
                reset_otp='$otp',
                otp_expiry='$expiry'
            WHERE email='$email'
        ");

        if($update){

            $mail = new PHPMailer(true);

            try{

                // SMTP SETTINGS
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;

                // YOUR GMAIL
                $mail->Username   = 'cenrointerns@gmail.com';

                // YOUR 16 DIGIT GOOGLE APP PASSWORD
                $mail->Password   = 'uhns epcz ebmj mglf';

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // SENDER
                $mail->setFrom('cenrointerns@gmail.com', 'DENR-CENRO');

                // RECEIVER
                $mail->addAddress($email);

                // EMAIL CONTENT
                $mail->isHTML(true);

                $mail->Subject = 'DENR-CENRO Password Reset OTP';

                $mail->Body = "
                    <div style='font-family:Arial,sans-serif;padding:20px;'>

                        <h2 style='color:#2e7d32;'>
                            DENR-CENRO Password Reset
                        </h2>

                        <p>Hello,</p>

                        <p>Your OTP verification code is:</p>

                        <h1 style='
                            background:#2e7d32;
                            color:white;
                            display:inline-block;
                            padding:10px 20px;
                            border-radius:8px;
                            letter-spacing:5px;
                        '>
                            $otp
                        </h1>

                        <p>
                            This OTP will expire in 
                            <b>10 minutes</b>.
                        </p>

                        <p>
                            If you did not request this,
                            please ignore this email.
                        </p>

                    </div>
                ";

                // SEND EMAIL
                $mail->send();

                // STORE EMAIL SESSION
                $_SESSION['reset_email'] = $email;

                // REDIRECT
                header("Location: verify_otp.php");
                exit();

            }catch(Exception $e){

                $message = "Mailer Error: " . $mail->ErrorInfo;
            }

        }else{

            $message = "Failed to save OTP!";
        }

    }else{

        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Reset Password</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;

            background:linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
            url('assets/images/bg_cenro.jpeg');

            background-size:cover;
            background-position:center;
            background-repeat:no-repeat;

            height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;
        }

        .container{
            width:350px;
            background:rgba(255,255,255,0.15);
            padding:30px;
            border-radius:15px;
            box-shadow:0px 0px 20px rgba(0,0,0,0.3);
            backdrop-filter:blur(5px);
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

        h2{
            text-align:center;
            color:white;
            margin-bottom:20px;
            font-size:22px;
        }

        .message{
            text-align:center;
            color:#ff5252;
            margin-bottom:10px;
            font-weight:bold;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            border:none;
            border-radius:8px;
            outline:none;
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

        .back{
            text-align:center;
            margin-top:15px;
        }

        .back a{
            color:white;
            text-decoration:none;
            font-weight:bold;
        }

        .back a:hover{
            text-decoration:underline;
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

    <img src="assets/images/DENR_logo.png" class="logo" alt="Logo">

    <h2>Reset Password</h2>

    <p class="message">
        <?php echo $message; ?>
    </p>

    <form method="POST">

        <input type="email"
               name="email"
               placeholder="Enter your email"
               required>

        <button type="submit" name="send_otp">
            Send OTP
        </button>

    </form>

    <div class="back">
        <a href="Employee_login.php">
            Back to Login
        </a>
    </div>

</div>

</body>
</html>