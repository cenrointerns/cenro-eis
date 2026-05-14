<?php
session_start();
include 'config.php';

if(!isset($_SESSION['employee_id'])){
    header("Location: Employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// FETCH EMPLOYEE DATA
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$employee = $result->fetch_assoc();

if(!$employee){
    die("Employee not found!");
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
    background: url('assets/images/cenro.jpeg') no-repeat center center fixed;
    background-size: cover;
    opacity: 800%;
}

/* DARK OVERLAY */
.overlay {
    position: fixed;
    width: 100%;
    height: 100%;
    background: rgba(252, 255, 255, 0.82);
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
    border-radius: 50%;
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
    color: #1b5e1fb4;
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
    <img src="assets/images/DENR_logo.png" alt="DENR Logo">

    <h2>DENR-CENRO PORTAL</h2>

    <a href="#">🏠 Dashboard</a>
    <a href="Employy_docs_view.php">📁 Documents</a>
    <a href="#">📊 News</a>
    <a href="Employee_logout.php">🚪 Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="card">

        <!-- ONLY CONTENT CHANGED (NOT DESIGN) -->
        <h1>Welcome, <?php echo htmlspecialchars($employee['name']); ?>!</h1>

        <p>Department of Environment and Natural Resources - Employee Portal</p>

        <p>
            You are now logged in to the official DENR employee dashboard.
            Use the sidebar to navigate through system modules.
        </p>

        <!-- DISPLAY DATA INSIDE SAME CONTENT STYLE -->
        <p><b>Employee ID:</b> <?php echo $employee['employee_id']; ?></p>
        <p><b>Age:</b> <?php echo $employee['age']; ?></p>
        <p><b>Status:</b> <?php echo $employee['status']; ?></p>
        <p><b>Gender:</b> <?php echo $employee['gender']; ?></p>
        <p><b>Date of Birth:</b> <?php echo $employee['date_of_birth']; ?></p>
        <p><b>Position Title:</b> <?php echo $employee['position_title']; ?></p>
        <p><b>Salary Grade:</b> <?php echo $employee['salary_grade']; ?></p>
        <p><b>Education:</b> <?php echo $employee['education']; ?></p>
        <p><b>Assignment:</b> <?php echo $employee['place_of_assignment']; ?></p>
        <p><b>Length of Service:</b> <?php echo $employee['length_of_service']; ?></p>

    </div>

</div>

</body>
</html>