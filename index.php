<?php
session_start();
include "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
    
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $dbPassword = $user['password']; // hashed password

        if (password_verify($password, $dbPassword)) {
            // Password matches
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }

    } else {
        $error = "Invalid username or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CENRO LOGIN</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container">
    <img src="assets/images/denr remv bg.png" alt="DENR Logo">

    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="footer-text">
        Department of Environment and Natural Resources - Manolo Fortich, Bukidnon
    </div>
</div>

</body>
</html>