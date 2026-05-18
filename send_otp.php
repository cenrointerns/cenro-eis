<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;

$email = $_GET['email'] ?? '';

if (!$email) {
    die("Invalid request");
}

$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// CHECK IF EMAIL EXISTS
$check = $conn->prepare("SELECT employee_login_id FROM employees_login WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    die("Email not found");
}

// SAVE OTP (FIXED COLUMN)
$stmt = $conn->prepare("UPDATE employees_login SET reset_otp = ?, otp_expiry = ? WHERE email = ?");
$stmt->bind_param("sss", $otp, $expiry, $email);
$stmt->execute();

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
    $mail->Subject = "Your Login OTP";
    $mail->Body = "Your OTP is <b>$otp</b>. It expires in 10 minutes.";

    $mail->send();

    header("Location: verify_otp.php?email=" . urlencode($email));
    exit();

} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>