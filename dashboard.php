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
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


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