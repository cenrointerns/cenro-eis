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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DENR Employee Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="assets/css/employee_dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


</head>

<body>

<div class="overlay"></div>
<!-- MOBILE TOPBAR -->
<div class="mobile-topbar">
    <h2>DENR Portal</h2>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <div class="logo-section">
        <img src="assets/images/DENR_logo.png" alt="DENR Logo">
        <h2>DENR-CENRO<br>EMPLOYEE PORTAL</h2>
    </div>

    <div class="nav-links">
        <a href="#">
            <span>🏠</span> Dashboard
        </a>

        <a href="Employee_docs_view.php">
            <span>📁</span> Documents
        </a>

        <a href="#">
            <span>📊</span> News
        </a>

        <a href="Employee_logout.php">
            <span>🚪</span> Logout
        </a>
    </div>

</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="dashboard-header">
        <h1>Employee Dashboard</h1>
        <p>Department of Environment and Natural Resources</p>
    </div>

    <!-- WELCOME -->
    <div class="welcome-box">
        <h2>
            Welcome,
            <?php echo htmlspecialchars($employee['name']); ?> 👋
        </h2>

        <p>
            You are successfully logged in to the official DENR Employee Portal.
            Manage your documents, access employee records, and stay updated with announcements.
        </p>
    </div>

    <!-- CARD -->
    <div class="card">

        <div class="info-grid">

            <div class="info-box">
                <h3>Employee ID</h3>
                <p><?php echo $employee['employee_id']; ?></p>
            </div>

            <div class="info-box">
                <h3>Age</h3>
                <p><?php echo $employee['age']; ?></p>
            </div>

            <div class="info-box">
                <h3>Status</h3>
                <p><?php echo $employee['status']; ?></p>
            </div>

            <div class="info-box">
                <h3>Gender</h3>
                <p><?php echo $employee['gender']; ?></p>
            </div>

            <div class="info-box">
                <h3>Date of Birth</h3>
                <p><?php echo $employee['date_of_birth']; ?></p>
            </div>

            <div class="info-box">
                <h3>Position Title</h3>
                <p><?php echo $employee['position_title']; ?></p>
            </div>

            <div class="info-box">
                <h3>Salary Grade</h3>
                <p><?php echo $employee['salary_grade']; ?></p>
            </div>

            <div class="info-box">
                <h3>Education</h3>
                <p><?php echo $employee['education']; ?></p>
            </div>

            <div class="info-box">
                <h3>Assignment</h3>
                <p><?php echo $employee['place_of_assignment']; ?></p>
            </div>

            <div class="info-box">
                <h3>Length of Service</h3>
                <p><?php echo $employee['length_of_service']; ?></p>
            </div>

        </div>

    </div>

</div>

<script>

function toggleMenu(){
    document.getElementById("sidebar").classList.toggle("active");
}

// AUTO CLOSE SIDEBAR ON MOBILE
document.addEventListener('click', function(event){

    const sidebar = document.getElementById("sidebar");
    const menuBtn = document.querySelector(".menu-btn");

    if(
        window.innerWidth <= 991 &&
        !sidebar.contains(event.target) &&
        !menuBtn.contains(event.target)
    ){
        sidebar.classList.remove("active");
    }

});

</script>

</body>
</html>