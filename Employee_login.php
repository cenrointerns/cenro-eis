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

            // Instead of direct redirect, we'll show a loading spinner
            $redirect = true;
            
            // Store login success flag
            $_SESSION['login_success'] = true;

        } else {

            $message = "Incorrect password!";
        }

    } else {

        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR — Employee Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        *, *::before, *::after { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        :root {
            --green-950: #052710;
            --green-900: #0a3d18;
            --green-800: #145228;
            --green-700: #1e6d38;
            --green-600: #2a8a4a;
            --green-500: #38a85f;
            --green-400: #5abf7c;
            --green-300: #84d49e;
            --green-200: #b3e8c4;
            --green-100: #dff5e7;
            --green-50:  #f0faf4;
            --gold-500: #c9a227;
            --gold-400: #e0b93a;
            --bg: #f0f4f1;
            --surface: rgba(255, 255, 255, 0.95);
            --surface-glass: rgba(255, 255, 255, 0.25);
            --border: rgba(255, 255, 255, 0.2);
            --border-strong: rgba(255, 255, 255, 0.3);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.85);
            --text-muted: rgba(255, 255, 255, 0.7);
            --radius: 14px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 4px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.09);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                        url('assets/images/bg_cenro.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Glass morphism effect */
        .login-container {
            width: 420px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border-radius: var(--radius);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: fadeUp 0.5s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, rgba(30, 109, 56, 0.9) 0%, rgba(5, 39, 16, 0.9) 100%);
            padding: 32px 28px;
            text-align: center;
            position: relative;
            backdrop-filter: blur(4px);
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(255, 255, 255, 0.12);
            clip-path: polygon(0 0, 100% 0, 50% 100%);
        }

        .logo-wrap {
            width: 85px;
            height: 85px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: white;
            padding: 8px;
            box-shadow: 0 0 0 4px rgba(201,162,39,0.3);
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: contain;
        }

        .login-header h2 {
            font-family: 'Sora', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: white;
            line-height: 1.3;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .login-header p {
            font-size: 13px;
            color: var(--green-200);
            margin-top: 6px;
        }

        .login-body {
            padding: 36px 32px 32px;
        }

        .message {
            background: rgba(254, 226, 226, 0.95);
            border-left: 4px solid #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            color: #991b1b;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(4px);
        }

        .message i {
            font-size: 16px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .input-label i {
            margin-right: 6px;
            color: var(--green-300);
            font-size: 12px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 40px 12px 42px;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            transition: var(--transition);
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .input-wrapper input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-wrapper input:focus {
            border-color: var(--green-400);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 3px rgba(90, 191, 124, 0.2);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-weight: 500;
            user-select: none;
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: white;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 24px;
        }

        .forgot-password a {
            font-size: 12px;
            color: var(--green-300);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .forgot-password a:hover {
            color: white;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--green-700) 0%, var(--green-800) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, var(--green-600) 0%, var(--green-700) 100%);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .register-link p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }

        .register-link a {
            color: var(--green-300);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .register-link a:hover {
            color: white;
            text-decoration: underline;
        }

        /* Loading Overlay Styles */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .loading-content {
            text-align: center;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .spinner {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
        }

        .spinner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid rgba(90, 191, 124, 0.2);
            border-top-color: var(--green-400);
            border-right-color: var(--green-400);
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner-inner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid rgba(201, 162, 39, 0.3);
            border-top-color: var(--gold-400);
            border-right-color: var(--gold-400);
            animation: spin-reverse 0.6s linear infinite;
        }

        @keyframes spin-reverse {
            to {
                transform: translate(-50%, -50%) rotate(-360deg);
            }
        }

        .loading-content h3 {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            color: white;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .loading-content p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
        }

        .progress-bar {
            width: 250px;
            height: 3px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--green-400), var(--gold-400));
            border-radius: 10px;
            transition: width 2s linear;
        }

        .welcome-message {
            font-size: 16px;
            color: var(--green-300);
            margin-top: 15px;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                margin: 20px;
            }
            
            .login-header {
                padding: 24px 20px;
            }
            
            .login-body {
                padding: 28px 24px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <div class="logo-wrap">
            <img src="assets/images/DENR_logo.png" alt="DENR Logo" onerror="this.style.display='none'">
        </div>
        <h2>DENR-CENRO<br>Manolo Fortich, Bukidnon</h2>
        <p>Employee Portal Access</p>
    </div>

    <div class="login-body">
        <?php if ($message): ?>
            <div class="message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="input-group">
                <label class="input-label">
                    <i class="fa-solid fa-envelope"></i> Email Address
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="employee@denr.gov.ph" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">
                    <i class="fa-solid fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fa-solid fa-key"></i>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <span class="toggle-password" onclick="togglePassword()">
                        Show
                    </span>
                </div>
            </div>

            <div class="forgot-password">
                <a href="reset_password.php">
                    <i class="fa-solid fa-question-circle"></i> Forgot Password?
                </a>
            </div>

            <button type="submit" name="login" class="login-btn" id="loginBtn">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="register-link">
            <p>
                No account yet? 
                <a href="Employee_register.php">
                    Create Account <i class="fa-solid fa-arrow-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner">
            <div class="spinner-inner"></div>
        </div>
        <h3>Welcome!</h3>
        <p>Accessing your dashboard</p>
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="welcome-message" id="welcomeMessage">
            <i class="fa-solid fa-leaf"></i> Securing your session...
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var passwordField = document.getElementById("password");
    var toggleText = document.querySelector(".toggle-password");
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleText.innerHTML = "Hide";
    } else {
        passwordField.type = "password";
        toggleText.innerHTML = "Show";
    }
}

// Handle form submission with spinner
document.getElementById('loginForm').addEventListener('submit', function(e) {
    // Check if there's already a message from PHP (error case)
    <?php if(empty($redirect)): ?>
        // If no redirect flag, let the form submit normally
        return true;
    <?php else: ?>
        // If redirect flag is set, show spinner and prevent default
        e.preventDefault();
        
        // Show loading overlay
        const overlay = document.getElementById('loadingOverlay');
        const progressFill = document.getElementById('progressFill');
        const welcomeMessage = document.getElementById('welcomeMessage');
        
        overlay.style.display = 'flex';
        
        // Animate progress bar
        setTimeout(() => {
            progressFill.style.width = '100%';
        }, 100);
        
        // Rotate welcome messages
        const messages = [
            '<i class="fa-solid fa-leaf"></i> Securing your session...',
            '<i class="fa-solid fa-chart-line"></i> Loading your dashboard...',
            '<i class="fa-solid fa-users"></i> Fetching employee data...',
            '<i class="fa-solid fa-file-alt"></i> Preparing documents...',
            '<i class="fa-solid fa-check-circle"></i> Almost there...'
        ];
        
        let msgIndex = 0;
        const msgInterval = setInterval(() => {
            msgIndex = (msgIndex + 1) % messages.length;
            welcomeMessage.innerHTML = messages[msgIndex];
        }, 400);
        
        // Redirect after 2 seconds
        setTimeout(function() {
            clearInterval(msgInterval);
            window.location.href = 'Employee_dashboard.php';
        }, 2000);
        
        return false;
    <?php endif; ?>
});
</script>

<?php
// If login was successful, we need to output JavaScript to trigger the spinner
if(isset($redirect) && $redirect === true) {
    echo '<script>
        // Auto-trigger the spinner on page load if login was successful
        document.addEventListener("DOMContentLoaded", function() {
            const overlay = document.getElementById("loadingOverlay");
            const progressFill = document.getElementById("progressFill");
            const welcomeMessage = document.getElementById("welcomeMessage");
            
            overlay.style.display = "flex";
            
            setTimeout(() => {
                progressFill.style.width = "100%";
            }, 100);
            
            const messages = [
                \'<i class="fa-solid fa-leaf"></i> Securing your session...\',
                \'<i class="fa-solid fa-chart-line"></i> Loading your dashboard...\',
                \'<i class="fa-solid fa-users"></i> Fetching employee data...\',
                \'<i class="fa-solid fa-file-alt"></i> Preparing documents...\',
                \'<i class="fa-solid fa-check-circle"></i> Almost there...\'
            ];
            
            let msgIndex = 0;
            const msgInterval = setInterval(() => {
                msgIndex = (msgIndex + 1) % messages.length;
                welcomeMessage.innerHTML = messages[msgIndex];
            }, 400);
            
            setTimeout(function() {
                clearInterval(msgInterval);
                window.location.href = "Employee_dashboard.php";
            }, 2000);
        });
    </script>';
}
?>

</body>
</html>