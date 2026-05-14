<?php
session_start();

if(!isset($_SESSION['employee_id'])){
    header("Location: Employee_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>DENR Employee Dashboard</title>

<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* BACKGROUND */
body {
    background: url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1600&q=80') no-repeat center center fixed;
    background-size: cover;
}

/* DARK OVERLAY */
.overlay {
    position: fixed;
    width: 100%;
    height: 100%;
    background: rgba(0, 50, 20, 0.55);
    z-index: 0;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    height: 100%;
    background: rgba(0, 70, 30, 0.95);
    color: white;
    padding: 20px;
    z-index: 2;
}

.sidebar img {
    width: 80px;
    display: block;
    margin: 0 auto 15px;
}

.sidebar h2 {
    text-align: center;
    font-size: 18px;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 12px;
    margin: 8px 0;
    border-radius: 6px;
    transition: 0.3s;
    background: rgba(255,255,255,0.1);
}

.sidebar a:hover {
    background: #2e7d32;
}

/* MAIN CONTENT */
.main {
    margin-left: 260px;
    padding: 40px;
    position: relative;
    z-index: 1;
}

/* DASHBOARD CARD */
.card {
    background: rgba(255,255,255,0.92);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    max-width: 600px;
}

.card h1 {
    color: #1b5e20;
    margin-bottom: 10px;
}

.card p {
    color: #333;
    margin-bottom: 20px;
}

/* BUTTON */
.logout {
    display: inline-block;
    padding: 10px 18px;
    background: #1b5e20;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
}

.logout:hover {
    background: #2e7d32;
}

/* RESPONSIVE */
@media screen and (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        text-align: center;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }
}
</style>

</head>

<body>

<div class="overlay"></div>

<!-- SIDEBAR -->
<div class="sidebar">
    <img src="assets/images/denr remv bg.png" alt="DENR Logo">

    <h2>DENR SYSTEM</h2>

    <a href="#">🏠 Dashboard</a>
        <a href="#">📁 Documents</a>
    <a href="#">📊 News</a>
    <a href="Employee_logout.php">🚪 Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="card">
        <h1>Welcome, <?php echo $_SESSION['fullname']; ?>!</h1>
        <p>Department of Environment and Natural Resources - Employee Portal</p>

        <p>
            You are now logged in to the official DENR employee dashboard.
            Use the sidebar to navigate through system modules.
        </p>

  
    </div>

</div>

</body>
</html>