<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "cenro";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee_id safely
$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

// Fetch employee info
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

// If not found
if (!$employee) {
    die("Employee not found.");
}

// Image handling
$image_path = 'assets/image/employee/' . $employee['image'];
if (!file_exists($image_path) || empty($employee['image'])) {
    $image_path = 'assets/image/employee/default.png';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($employee['name']); ?> - Employee Info</title>

<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;

    /* 🔥 Background image */
    background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png'); /* change path here */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    margin: 0;
    padding: 20px;
}
.container {
    max-width: 950px;
    margin: auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.employee-header {
    display: flex;
    flex-wrap: wrap;
    padding: 20px;
}
.employee-image {
    flex: 0 0 200px;
    text-align: center;
}
.employee-image img {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #4facfe;
}
.employee-info {
    flex: 1;
    padding-left: 20px;
}
.employee-info h2 {
    margin-top: 0;
}
.employee-info p {
    margin: 6px 0;
    color: #555;
}
.employee-info span {
    font-weight: bold;
}
.details-section {
    padding: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: #4facfe;
    color: #fff;
    padding: 10px;
}
td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
</style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="employee-header">

        <div class="employee-image">
            <img src="<?= htmlspecialchars($image_path); ?>" 
                 alt="<?= htmlspecialchars($employee['name']); ?>">
        </div>

        <div class="employee-info">
            <h2><?= htmlspecialchars($employee['name']); ?></h2>

            <p><span>Age:</span> <?= htmlspecialchars($employee['age']); ?></p>
            <p><span>Gender:</span> <?= htmlspecialchars($employee['gender'] ?? 'N/A'); ?></p>
            <p><span>Date of Birth:</span> <?= htmlspecialchars($employee['date_of_birth'] ?? 'N/A'); ?></p>
            <p><span>NOSCA Item Number:</span> <?= htmlspecialchars($employee['nosca_item_number'] ?? 'N/A'); ?></p>

            <p><span>Place of Assignment:</span> <?= htmlspecialchars($employee['place_of_assignment'] ?? 'N/A'); ?></p>

            <p><span>Status:</span> <?= htmlspecialchars($employee['status']); ?></p>

            <!-- ✅ REMOVED JOINED -->
        </div>

    </div>

    <!-- DETAILS TABLE -->
    <div class="details-section">
        <h3>Employment Details</h3>

        <table>
            <tr>
                <th>Civil Service Eligibility</th>
                <th>Position Title</th>
                <th>Education</th>
                <th>Salary Grade</th>
                <th>Date of Appointment</th>
                <th>Length of Service</th>
            </tr>

            <tr>
                <td><?= htmlspecialchars($employee['civil_service_eligibility'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($employee['position_title'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($employee['education'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($employee['salary_grade'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($employee['date_of_appointment'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($employee['length_of_service'] ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>