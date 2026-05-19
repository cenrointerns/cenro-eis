<?php
include 'config.php';

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

// AGE
$age = 'N/A';
if (!empty($employee['date_of_birth'])) {
    $dob = new DateTime($employee['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
}

// LENGTH OF SERVICE
$service = 'N/A';
if (!empty($employee['date_of_appointment'])) {
    $start = new DateTime($employee['date_of_appointment']);
    $today = new DateTime();
    $diff = $today->diff($start);
    $service = $diff->y . " years, " . $diff->m . " months, " . $diff->d . " days";
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
    background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png');
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
    position: relative;
}

/* BACK BUTTON */
.back-btn {
    padding: 15px;
}

.back-btn a {
    display: inline-block;
    padding: 8px 14px;
    background: #4facfe;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

/* DOCUMENT ICON */
.doc-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 26px;
}

.doc-icon a {
    text-decoration: none;
    color: #4facfe;
    transition: 0.2s;
}

.doc-icon a:hover {
    color: #1e88e5;
    transform: scale(1.2);
}

/* HEADER */
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

/* SECTION TITLE (BLUE HEADER) */
.section-title {
    background: linear-gradient(90deg, #4facfe, #00c6ff);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 18px;
}

/* DETAILS GRID */
.details-section {
    padding: 20px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

/* DETAIL CARD */
.detail-item {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #eee;
    transition: all 0.25s ease;
    cursor: pointer;
}

.detail-item:hover {
    background: #e6f4ff;
    border-color: #4facfe;
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(79, 172, 254, 0.2);
}

/* LABEL */
.detail-item label {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: 0.2s;
}

/* LABEL HOVER */
.detail-item:hover label {
    color: #1e88e5;
}

/* VALUE */
.detail-item span {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}
h3{
text-align: center;
}
</style>
</head>

<body>

<div class="container">

    <!-- BACK BUTTON -->
    <div class="back-btn">
        <a href="employee_list.php">← Go Back</a>
    </div>

    <!-- DOCUMENT ICON -->
    <div class="doc-icon">
        <a href="employee_document.php?employee_id=<?= $employee_id; ?>" title="View Documents">
            📄
        </a>
    </div>

    <!-- HEADER -->
    <div class="employee-header">

        <div class="employee-image">
            <img src="<?= htmlspecialchars($image_path); ?>" 
                 alt="<?= htmlspecialchars($employee['name']); ?>">
        </div>

        <div class="employee-info">
            <h2><?= htmlspecialchars($employee['name']); ?></h2>

            <p><span>Age:</span> <?= htmlspecialchars($age); ?></p>
            <p><span>Gender:</span> <?= htmlspecialchars($employee['gender'] ?? 'N/A'); ?></p>
            <p><span>Date of Birth:</span> <?= htmlspecialchars($employee['date_of_birth'] ?? 'N/A'); ?></p>
            <p><span>NOSCA Item Number:</span> <?= htmlspecialchars($employee['nosca_item_number'] ?? 'N/A'); ?></p>
            <p><span>Assigned Section:</span> <?= htmlspecialchars($employee['assigned_section'] ?? 'N/A'); ?></p>
            <p><span>Status:</span> <?= htmlspecialchars($employee['status']); ?></p>

        </div>

    </div>

    <!-- DETAILS -->
    <div class="details-section">

        <h3 class="section-title">Employee Details</h3>

        <div class="details-grid">

            <div class="detail-item">
                <label>Civil Service Eligibility</label>
                <span><?= htmlspecialchars($employee['civil_service_eligibility'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Position Title</label>
                <span><?= htmlspecialchars($employee['position_title'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Education</label>
                <span><?= htmlspecialchars($employee['education'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Monthly Salary</label>
                <span><?= htmlspecialchars($employee['monthly_salary'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Salary Grade</label>
                <span><?= htmlspecialchars($employee['salary_grade'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Date of Appointment</label>
                <span><?= htmlspecialchars($employee['date_of_appointment'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Length of Service</label>
                <span><?= htmlspecialchars($service); ?></span>
            </div>

            <div class="detail-item">
                <label>Date of Last Promotion</label>
                <span><?= htmlspecialchars($employee['date_of_last_appointment'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-item">
                <label>Step Increment</label>
                <span><?= htmlspecialchars($employee['step_increment'] ?? 'N/A'); ?></span>
            </div>

        </div>

    </div>

</div>

</body>
</html>