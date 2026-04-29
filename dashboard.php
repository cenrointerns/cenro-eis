<?php
include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DASHBOARD COUNTS
$totalQuery = "SELECT COUNT(*) AS total FROM employees";
$totalResult = $conn->query($totalQuery);
$totalEmployees = $totalResult->fetch_assoc()['total'] ?? 0;

$cosQuery = "SELECT COUNT(*) AS total FROM employees WHERE status = 'Contract of Service'";
$cosResult = $conn->query($cosQuery);
$cosCount = $cosResult->fetch_assoc()['total'] ?? 0;

$permQuery = "SELECT COUNT(*) AS total FROM employees WHERE status = 'Permanent'";
$permResult = $conn->query($permQuery);
$permCount = $permResult->fetch_assoc()['total'] ?? 0;

// RECENT ACTIVITY
$activityQuery = "
SELECT 
    e.name,
    d.file_name,
    d.document_type,
    d.uploaded_at
FROM documents d
JOIN employees e ON e.employee_id = d.employee_id
ORDER BY d.uploaded_at DESC
LIMIT 5
";

$activityResult = $conn->query($activityQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DENR Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #e9f0ea;
    position: relative;
}

/* WATERMARK */
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    background: url("https://upload.wikimedia.org/wikipedia/commons/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 400px;

    opacity: 0.30;
    z-index: 0;
    pointer-events: none;
}

/* DASHBOARD LAYOUT */
.dashboard {
    display: flex;
    position: relative;
    z-index: 1;
}

/* SIDEBAR */
.sidebar {
    width: 230px;
    background: rgba(0, 70, 0, 0.92);
    height: 100vh;
    padding: 20px 15px;
    color: white;
    display: flex;
    flex-direction: column;
}

.sidebar-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 25px;
}

.sidebar-logo img {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    background: #fff;
    padding: 4px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin: 6px 0;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-size: 15px;
    transition: 0.25s;
}

.sidebar-menu li a i {
    width: 22px;
    text-align: center;
    margin-right: 10px;
}

.sidebar-menu li a:hover {
    background: rgba(255,255,255,0.15);
    transform: translateX(6px);
}

/* MAIN CONTENT */
.main-content {
    flex: 1;
    padding: 20px;
}

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

/* CARDS */
.cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

/* ACTIVITY SECTION */
.activity-section {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

th {
    background: #f4f4f4;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: #fff;
    width: 350px;
    margin: 15% auto;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.modal-actions {
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
}

.btn-cancel {
    padding: 10px 15px;
    border: none;
    background: #ccc;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn-logout {
    padding: 10px 15px;
    background: #d9534f;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="sidebar-logo">
            <img src="assets/images/denr remv bg.png" alt="DENR Logo">
        </div>

        <ul class="sidebar-menu">

            <li>
                <a href="create_employee.php">
                    <i class="fa-solid fa-user-gear"></i> Manage Employee
                </a>
            </li>

            <li>
                <a href="employee_list.php">
                    <i class="fa-solid fa-users"></i> Employees
                </a>
            </li>

            <li>
                <a href="upload_form.php">
                    <i class="fa-solid fa-file-arrow-up"></i> Add Documents
                </a>
            </li>

            <li>
                <a href="document_page.php">
                    <i class="fa-solid fa-folder-open"></i> Documents
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-diagram-project"></i> Projects
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </li>

            <!-- LOGOUT -->
            <li>
                <a href="#" onclick="openLogoutModal(event)">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </li>

        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="topbar">
            <h1>Dashboard</h1>
            <span>Welcome, Admin</span>
        </div>

        <!-- CARDS -->
        <div class="cards">

            <div class="card">
                <h3><?= $totalEmployees ?></h3>
                <p>Total Employees</p>
            </div>

            <div class="card">
                <h3><?= $cosCount ?></h3>
                <p>Contract of Service</p>
            </div>

            <div class="card">
                <h3><?= $permCount ?></h3>
                <p>Permanent</p>
            </div>

        </div>

        <!-- ACTIVITY -->
        <div class="activity-section">

            <h2>Recent Activity</h2>

            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Uploaded</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($activityResult && $activityResult->num_rows > 0): ?>
                    <?php while($row = $activityResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['file_name']) ?></td>
                            <td><?= htmlspecialchars($row['document_type']) ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($row['uploaded_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No recent activity</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>

    </div>
</div>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal">

    <div class="modal-content">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout?</p>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeLogoutModal()">Cancel</button>

            <!-- 2 SECOND DELAY LOGOUT -->
            <a href="#" class="btn-logout" onclick="logoutNow(event)">
                Yes, Logout
            </a>
        </div>

    </div>

</div>

<!-- SCRIPT -->
<script>
function openLogoutModal(event) {
    event.preventDefault();
    document.getElementById("logoutModal").style.display = "block";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}

window.onclick = function(event) {
    let modal = document.getElementById("logoutModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

/* LOGOUT WITH 2 SECOND DELAY */
function logoutNow(event) {
    event.preventDefault();

    let modal = document.getElementById("logoutModal");

    modal.innerHTML = `
        <div class="modal-content">
            <h3>Logging out...</h3>
            <p>Please wait 2 seconds...</p>
        </div>
    `;

    setTimeout(() => {
        window.location.href = "logout.php";
    }, 2000);
}
</script>

</body>
</html>