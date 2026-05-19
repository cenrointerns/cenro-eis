<?php
session_start();
include "config.php";

$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
    
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $success = true;
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

<style>
            body{
            font-family:Arial, sans-serif;
            background:linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
            url('assets/images/cenro.jpeg');
            background-size:cover;
            background-position:center;
            background-repeat:no-repeat;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }
/* BACKDROP */
.loader-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.13);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* BUBBLE SPINNER */
.bubble-loader {
    display: flex;
    gap: 10px;
}

.bubble-loader div {
    width: 15px;
    height: 15px;
    background: #4caf50;
    border-radius: 50%;
    animation: bounce 0.6s infinite alternate;
}

.bubble-loader div:nth-child(2) {
    animation-delay: 0.2s;
}

.bubble-loader div:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {
    from { transform: translateY(0); opacity: 0.6; }
    to { transform: translateY(-15px); opacity: 1; }
}

/* SUCCESS TEXT */
.success-text {
    color: #00c853;
    font-weight: bold;
    margin-top: 10px;
    text-align: center;
}
</style>

</head>
<body>

<div class="login-container">
    <img src="assets/images/denr remv bg.png" alt="DENR Logo">

    <h2>Admin Login</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- SUCCESS STATE -->
    <?php if ($success): ?>
        <p class="success-text">Login successful! Redirecting...</p>

        <!-- LOADER -->
        <div class="loader-overlay" id="loader">
            <div class="bubble-loader">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>

        <script>
            // show loader
            document.getElementById("loader").style.display = "flex";

            // redirect after 2 seconds
            setTimeout(function () {
                window.location.href = "dashboard.php";
            }, 2000);
        </script>
    <?php endif; ?>

    <!-- FORM -->
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