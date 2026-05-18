<?php
session_start();
include 'config.php';

$email = $_GET['email'] ?? '';

if (!$email) {
    die("Invalid request");
}

$message = "";

if (isset($_POST['verify'])) {

    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("
        SELECT employee_login_id 
        FROM employees_login 
        WHERE email = ? 
        AND reset_otp = ? 
        AND otp_expiry > NOW()
    ");

    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $_SESSION['reset_email'] = $email;

        // OPTIONAL: clear OTP after success (recommended)
        $clear = $conn->prepare("
            UPDATE employees_login 
            SET reset_otp = NULL, otp_expiry = NULL 
            WHERE email = ?
        ");
        $clear->bind_param("s", $email);
        $clear->execute();

        header("Location: reset_password.php");
        exit();

    } else {
        $message = "Invalid or expired OTP!";
    }
}
?>

<form method="POST">
    <h3>Enter OTP</h3>
    <p style="color:red;"><?php echo $message; ?></p>

    <input type="text" name="otp" placeholder="6-digit OTP" required>

    <button type="submit" name="verify">Verify</button>
</form>