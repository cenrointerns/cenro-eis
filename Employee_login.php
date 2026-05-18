<?php
session_start();
include 'config.php';

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$message = "";

/* ================= LOGIN ================= */
if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM employees_login WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['temp_employee_id'] = $row['employee_id'];
            $_SESSION['temp_fullname'] = $row['fullname'];
            $_SESSION['temp_email'] = $row['email'];

            header("Location: send_otp.php?email=" . urlencode($row['email']));
            exit();

        } else {
            $message = "Incorrect password!";
        }

    } else {
        $message = "Email not found!";
    }
}


/* ================= FORGOT PASSWORD ================= */
if (isset($_POST['forgot_submit'])) {

    $email = trim($_POST['forgot_email']);

    $stmt = $conn->prepare("SELECT employee_login_id FROM employees_login WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        // 🔥 GENERATE OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // ✅ FIXED COLUMN (reset_otp NOT otp_code)
        $update = $conn->prepare("
            UPDATE employees_login 
            SET reset_otp = ?, otp_expiry = ?
            WHERE email = ?
        ");
        $update->bind_param("sss", $otp, $expiry, $email);
        $update->execute();

        /* ================= SEND EMAIL ================= */
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cenrointerns@gmail.com';
            $mail->Password = 'aadq zrtr xbne fyze';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('cenrointerns@gmail.com', 'CENRO-EIS');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset OTP";

            $mail->Body = "
                <h3>Password Reset OTP</h3>
                <p>Your OTP code is:</p>
                <h2>$otp</h2>
                <p>This OTP will expire in 10 minutes.</p>
            ";

            $mail->send();

            header("Location: verify_reset_otp.php?email=" . urlencode($email));
            exit();

        } catch (Exception $e) {
            $message = "Mailer Error: " . $mail->ErrorInfo;
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
            backdrop-filter: blur(5px);
            box-shadow:0px 0px 20px rgba(0,0,0,0.3);
        }

        h2,p{
            text-align:center;
            color:white;
        }

        .message{
            text-align:center;
            color:red;
            margin-bottom:10px;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            border-radius:8px;
            border:1px solid #ccc;
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

        .logo{
            width:90px;
            height:90px;
            border-radius:50%;
            display:block;
            margin:0 auto 15px;
            border:4px solid #2e7d32;
            object-fit:cover;
            background:white;
        }

        .pass-wrapper{
            position:relative;
        }

        .pass-wrapper span{
            position:absolute;
            right:10px;
            top:50%;
            transform:translateY(-50%);
            cursor:pointer;
        }

        /* MODAL */
        .modal{
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.5);
            justify-content:center;
            align-items:center;
        }

        .modal-content{
            background:white;
            padding:20px;
            border-radius:10px;
            width:300px;
        }
    </style>

</head>
<body>

<div class="container">

    <img src="assets/images/DENR_logo.png" class="logo">

    <h2>Employee Login</h2>
    <p>DENR-CENRO MANOLO FORTICH</p>

    <p class="message"><?php echo $message; ?></p>

    <form method="POST">

        <input type="email" name="email" placeholder="Email Address" required>

        <div class="pass-wrapper">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span onclick="togglePassword()">👁</span>
        </div>

        <button type="submit" name="login">Login</button>

    </form>

    <p style="margin-top:10px;">
        <a href="#" onclick="openModal()">Forgot Password?</a>
    </p>

    <p>
        No account yet?
        <a href="Employee_register.php">Create Account</a>
    </p>

</div>

<!-- FORGOT PASSWORD MODAL -->
<div class="modal" id="forgotModal">
    <div class="modal-content">

        <h3>Forgot Password</h3>

        <form method="POST">

            <input type="email" name="forgot_email" placeholder="Enter Email" required>

            <button type="submit" name="forgot_submit">Send Reset Link</button>

        </form>

        <button onclick="closeModal()" style="background:red;margin-top:10px;">
            Close
        </button>

    </div>
</div>

<script>
function togglePassword(){
    var pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

function openModal(){
    document.getElementById("forgotModal").style.display = "flex";
}

function closeModal(){
    document.getElementById("forgotModal").style.display = "none";
}
</script>

</body>
</html>