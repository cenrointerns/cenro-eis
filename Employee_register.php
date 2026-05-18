    <?php
    include 'config.php';

    $message = "";

    if(isset($_POST['register'])){

        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        $check = mysqli_query($conn, "SELECT * FROM employees_login WHERE email='$email'");

        if(mysqli_num_rows($check) > 0){

            $message = "Email already exists!";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert = mysqli_query($conn, "INSERT INTO employees_login(fullname,email,password)
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

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        font-family:Arial, sans-serif;

        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
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
        background:rgba(255, 255, 255, 0.2);
        padding:30px;
        border-radius:15px;
        box-shadow:0px 0px 20px rgba(0,0,0,0.3);
        backdrop-filter: blur(5px);
    }

    .logo{
        display:block;
        margin:0 auto 15px;
        width:85px;
        height:85px;
        border-radius:50%;
        border:4px solid #2e7d32;
        object-fit:cover;
        background:white;
        padding:5px;
    }

    h2{
        text-align:center;
        margin-bottom:20px;
        color: white;
    }

    input{
        width:100%;
        padding:12px;
        margin-top:10px;
        border:1px solid #ccc;
        border-radius:8px;
        outline:none;
    }

    input:focus{
        border-color:#2e7d32;
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
    }

    button:hover{
        background:#1b5e20;
    }

    .message{
        text-align:center;
        color:red;
        margin-bottom:10px;
    }

    a{
        text-decoration:none;
        color:whitesmoke;
        font-weight:bold;
    }

    a:hover{
        text-decoration:underline;
    }

    </style>

    </head>
    <body>

    <div class="container">

        <!-- LOGO -->
        <img src="assets/images/DENR_logo.png" class="logo" alt="DENR Logo">

        <h2>Create Account</h2>

        <p class="message"><?php echo $message; ?></p>

        <form method="POST">

            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="register">Register</button>

        </form>

        <p style="text-align:center;margin-top:15px;">
            Already have account?
            <a href="Employee_login.php">Login</a>
        </p>

    </div>

    </body>
    </html>