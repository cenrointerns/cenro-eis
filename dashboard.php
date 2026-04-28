<?php
// =====================
// DATABASE CONNECTION
// =====================
$conn = new mysqli("localhost", "root", "", "cenro");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =====================
// DASHBOARD COUNTS
// =====================

// Total Employees
$totalQuery = "SELECT COUNT(*) AS total FROM employees";
$totalResult = $conn->query($totalQuery);
$totalEmployees = $totalResult->fetch_assoc()['total'] ?? 0;

// Contract of Service
$cosQuery = "SELECT COUNT(*) AS total FROM employees WHERE status = 'Contract of Service'";
$cosResult = $conn->query($cosQuery);
$cosCount = $cosResult->fetch_assoc()['total'] ?? 0;

// Permanent
$permQuery = "SELECT COUNT(*) AS total FROM employees WHERE status = 'Permanent'";
$permResult = $conn->query($permQuery);
$permCount = $permResult->fetch_assoc()['total'] ?? 0;

// =====================
// RECENT ACTIVITY
// =====================
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

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: #fff;
            width: 320px;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .modal-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .modal-actions button {
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        #confirmLogout {
            background: #e74c3c;
            color: white;
        }

        #cancelLogout {
            background: #7f8c8d;
            color: white;
        }

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
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f4f4f4;
        }
    </style>
</head>

<body>

<div class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>DENR</h2>
        <ul>
            <li><a href="create_employee.php">Manage Employee</a></li>
            <li><a href="employee_list.php">Employees</a></li>
            <li><a href="upload_form.php">Add Documents</a></li>
            <li><a href="document_page.php">Documents</a></li>
            <li>Projects</li>
            <li>Settings</li>
            <li><a href="#" id="logoutBtn">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Topbar -->
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

        <!-- RECENT ACTIVITY -->
        <div class="activity-section">
            <h2>Recent Activity</h2>

            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Uploaded At</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($activityResult && $activityResult->num_rows > 0): ?>
                    <?php while($row = $activityResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['file_name']) ?></td>
                            <td><?= htmlspecialchars($row['document_type']) ?></td>
                            <td>
                                <?= date('M d, Y h:i A', strtotime($row['uploaded_at'])) ?>
                            </td>
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

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout?</p>

        <div class="modal-actions">
            <button id="cancelLogout">Cancel</button>
            <button id="confirmLogout">Logout</button>
        </div>
    </div>
</div>

<script>
const logoutBtn = document.getElementById("logoutBtn");
const modal = document.getElementById("logoutModal");
const cancelLogout = document.getElementById("cancelLogout");
const confirmLogout = document.getElementById("confirmLogout");

logoutBtn.addEventListener("click", function(e) {
    e.preventDefault();
    modal.style.display = "block";
});

cancelLogout.addEventListener("click", function() {
    modal.style.display = "none";
});

confirmLogout.addEventListener("click", function() {
    window.location.href = "index.php";
});

window.addEventListener("click", function(e) {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});
</script>

</body>
</html>