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

/* IMAGE FIX */
$image_path = 'assets/image/employee/default.png';

if (!empty($employee['image'])) {
    $temp_path = 'assets/image/employee/' . $employee['image'];

    if (file_exists($temp_path)) {
        $image_path = $temp_path;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>DENR Employee Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    background:#f4f7f9;
    min-height:100vh;
    display:flex;
    overflow-x:hidden;
}

/* TOP BAR (MOBILE) */
.mobile-topbar{
    display:none;
}

/* SIDEBAR */
.sidebar{
    width:280px;
    background:#0b5d3b;
    color:white;
    position:fixed;
    top:0;
    left:0;
    height:100%;
    padding:30px 20px;
    overflow-y:auto;
    z-index:1000;
    transition:0.3s ease;
}

.logo-section{
    text-align:center;
    margin-bottom:40px;
}

.logo-section img{
    width:100px;
    height:100px;
    border-radius:50%;
    object-fit:cover;
    background:white;
    border:4px solid white;
}

.logo-section h2{
    font-size:20px;
}

.nav-links{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.nav-links a{
    text-decoration:none;
    color:white;
    padding:14px 18px;
    border-radius:12px;
    transition:0.3s;
    display:flex;
    gap:10px;
}

.nav-links a:hover{
    background:rgba(255,255,255,0.15);
}

/* MAIN */
.main{
    margin-left:280px;
    width:calc(100% - 280px);
    padding:40px;
}

/* HEADER */
.dashboard-header h1{
    font-size:34px;
    color:#0b5d3b;
}

.dashboard-header p{
    color:#666;
}

/* WELCOME */
.welcome-box{
    background:white;
    padding:25px;
    border-radius:20px;
    margin-bottom:30px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

/* PROFILE */
.profile-card{
    background:white;
    border-radius:25px;
    padding:35px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
}

/* HEADER */
.profile-header{
    display:flex;
    gap:40px;
    flex-wrap:wrap;
    align-items:center;
}

/* IMAGE */
.avatar-box img{
    width:230px;
    height:230px;
    border-radius:50%;
    object-fit:cover;
    border:8px solid #0b5d3b;
}

/* INFO */
.employee-main-info h2{
    font-size:34px;
    color:#0b5d3b;
}

.employee-main-info p{
    margin:8px 0;
    color:#444;
}

.employee-main-info span{
    font-weight:600;
}

/* DETAILS CARD */
.employee-details-card{
    margin-top:25px;
    background:#f8fafb;
    border-radius:20px;
    padding:25px;
    border:1px solid #e3e7ea;
}

/* ROW */
.detail-row{
    display:flex;
    justify-content:space-between;
    padding:16px 10px;
    border-bottom:1px solid #dde5ea;
    gap:20px;
}

.detail-row span{
    font-weight:600;
    color:#0b5d3b;
    min-width:240px;
}

.detail-row p{
    flex:1;
    text-align:right;
    color:#444;
}

/* =========================
   MOBILE RESPONSIVE
========================= */
@media(max-width:991px){

    /* TOP BAR */
    .mobile-topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        background:#0b5d3b;
        color:white;
        padding:15px 20px;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        z-index:1100;
    }

    .menu-btn{
        font-size:28px;
        cursor:pointer;
    }

    /* MAIN */
    .main{
        margin-left:0;
        width:100%;
        padding:90px 15px 20px;
    }

    /* SIDEBAR */
    .sidebar{
        left:-100%;
        width:260px;
    }

    .sidebar.active{
        left:0;
    }

    /* PROFILE STACK */
    .profile-header{
        flex-direction:column;
        text-align:center;
    }

    .avatar-box img{
        width:160px;
        height:160px;
    }

    .employee-main-info h2{
        font-size:22px;
    }

    /* DETAILS STACK */
    .detail-row{
        flex-direction:column;
        align-items:flex-start;
    }

    .detail-row span{
        min-width:auto;
    }

    .detail-row p{
        text-align:left;
        width:100%;
        margin-top:5px;
    }
}

/* SMALL PHONE */
@media(max-width:480px){
    .avatar-box img{
        width:140px;
        height:140px;
    }

    .dashboard-header h1{
        font-size:20px;
    }
}

</style>
</head>

<body>

<!-- MOBILE TOP BAR -->
<div class="mobile-topbar">
    <div>DENR Portal</div>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <div class="logo-section">
        <img src="assets/images/DENR_logo.png">
        <h2>DENR-CENRO EMPLOYEE PORTAL</h2>
    </div>

    <div class="nav-links">
        <a href="#">🏠 Dashboard</a>
        <a href="Employee_docs_view.php">📁 Documents</a>
        <a href="announcement_fetcher.php">📊 News</a>
        <a href="Employee_logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

    <div class="dashboard-header">
        <h1>Employee Dashboard</h1>
        <p>Department of Environment and Natural Resources</p>
    </div>

    <div class="welcome-box">
        <h2>Welcome, <?php echo htmlspecialchars($employee['name']); ?> 👋</h2>
    </div>

    <div class="profile-card">

        <div class="profile-header">

            <div class="avatar-box">
                <img src="<?php echo htmlspecialchars($image_path); ?>">
            </div>

            <div class="employee-main-info">

                <h2><?php echo htmlspecialchars($employee['name']); ?></h2>

                <p><span>ID:</span> <?php echo $employee['employee_id']; ?></p>
                <p><span>Position:</span> <?php echo htmlspecialchars($employee['position_title']); ?></p>
                <p><span>Assignment:</span> <?php echo htmlspecialchars($employee['place_of_assignment']); ?></p>
                <p><span>Status:</span> <?php echo htmlspecialchars($employee['status']); ?></p>

            </div>

        </div>

        <!-- ALL DETAILS RESTORED -->
        <div class="employee-details-card">

            <div class="detail-row"><span>Age</span><p><?php echo $employee['age']; ?></p></div>
            <div class="detail-row"><span>Gender</span><p><?php echo $employee['gender']; ?></p></div>
            <div class="detail-row"><span>Date of Birth</span><p><?php echo $employee['date_of_birth']; ?></p></div>
            <div class="detail-row"><span>NOSCA Item Number</span><p><?php echo $employee['nosca_item_number']; ?></p></div>
            <div class="detail-row"><span>Salary Grade</span><p><?php echo $employee['salary_grade']; ?></p></div>
            <div class="detail-row"><span>Education</span><p><?php echo $employee['education']; ?></p></div>
            <div class="detail-row"><span>Civil Service Eligibility</span><p><?php echo $employee['civil_service_eligibility']; ?></p></div>
            <div class="detail-row"><span>Date of Appointment</span><p><?php echo $employee['date_of_appointment']; ?></p></div>
            <div class="detail-row"><span>Length of Service</span><p><?php echo $employee['length_of_service']; ?></p></div>

        </div>

    </div>

</div>

<script>

function toggleMenu(){
    document.getElementById("sidebar").classList.toggle("active");
}

document.addEventListener("click", function(e){
    const sidebar = document.getElementById("sidebar");
    const btn = document.querySelector(".menu-btn");

    if(window.innerWidth <= 991){
        if(!sidebar.contains(e.target) && !btn.contains(e.target)){
            sidebar.classList.remove("active");
        }
    }
});

</script>

</body>
</html>