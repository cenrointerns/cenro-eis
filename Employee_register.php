<?php
include 'config.php';

$message = "";
$message_type = ""; // success or error

if(isset($_POST['register'])){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM employees_login WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){

        $message = "Email already exists!";
        $message_type = "error";

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn, "INSERT INTO employees_login(fullname,email,password)
        VALUES('$fullname','$email','$hashed_password')");

        if($insert){
            $message = "Account created successfully! You can now login.";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR — Employee Registration</title>
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
        .register-container {
            width: 450px;
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

        .register-header {
            background: linear-gradient(135deg, rgba(30, 109, 56, 0.9) 0%, rgba(5, 39, 16, 0.9) 100%);
            padding: 28px 28px;
            text-align: center;
            position: relative;
            backdrop-filter: blur(4px);
        }

        .register-header::after {
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
            width: 75px;
            height: 75px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: white;
            padding: 6px;
            box-shadow: 0 0 0 3px rgba(201,162,39,0.3);
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: contain;
        }

        .register-header h2 {
            font-family: 'Sora', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .register-header p {
            font-size: 12px;
            color: var(--green-200);
            margin-top: 4px;
        }

        .register-body {
            padding: 36px 32px 32px;
        }

        .message {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(4px);
        }

        .message.success {
            background: rgba(223, 245, 231, 0.95);
            border-left: 4px solid #2a8a4a;
            color: #145228;
        }

        .message.error {
            background: rgba(254, 226, 226, 0.95);
            border-left: 4px solid #dc2626;
            color: #991b1b;
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

        .password-strength {
            margin-top: 8px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
        }

        .register-btn {
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
            margin-top: 8px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, var(--green-600) 0%, var(--green-700) 100%);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-link p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }

        .login-link a {
            color: var(--green-300);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: white;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-container {
                width: 90%;
                margin: 20px;
            }
            
            .register-header {
                padding: 20px;
            }
            
            .register-body {
                padding: 28px 24px;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-header">
        <div class="logo-wrap">
            <img src="assets/images/DENR_logo.png" alt="DENR Logo" onerror="this.style.display='none'">
        </div>
        <h2>Create Account</h2>
        <p>Join the DENR-CENRO Team</p>
    </div>

    <div class="register-body">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fa-solid fa-<?php echo $message_type == 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label class="input-label">
                    <i class="fa-solid fa-user"></i> Full Name
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fa-solid fa-user"></i>
                    <input type="text" name="fullname" placeholder="Juan M. Dela Cruz" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">
                    <i class="fa-solid fa-envelope"></i> Email Address
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="juan.delacruz@denr.gov.ph" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">
                    <i class="fa-solid fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fa-solid fa-key"></i>
                    <input type="password" name="password" id="password" placeholder="Create a strong password" required>
                </div>
                <div class="password-strength">
                    <i class="fa-solid fa-shield-alt"></i> Use at least 8 characters
                </div>
            </div>

            <button type="submit" name="register" class="register-btn">
                <i class="fa-solid fa-user-plus"></i> Register Account
            </button>
        </form>

        <div class="login-link">
            <p>
                Already have an account? 
                <a href="Employee_login.php">
                    Sign In <i class="fa-solid fa-arrow-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>

</body>
</html>