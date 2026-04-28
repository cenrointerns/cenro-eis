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

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// --------------------
// COUNT QUERY
// --------------------
if ($filter === 'Permanent' || $filter === 'Contract of Service') {
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM employees WHERE status = ?");
    $countStmt->bind_param("s", $filter);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM employees");
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// --------------------
// MAIN QUERY
// --------------------
if ($filter === 'Permanent' || $filter === 'Contract of Service') {
    $stmt = $conn->prepare("
        SELECT employee_id, name, age, place_of_assignment, status, image 
        FROM employees 
        WHERE status = ?
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sii", $filter, $limit, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT employee_id, name, age, place_of_assignment, status, image 
        FROM employees
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Image paths
$image_folder = __DIR__ . "/assets/image/employee/";
$image_url    = "assets/image/employee/";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employees</title>
    <link rel="stylesheet" href="assets/css/employee_list.css">

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

        img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }

        .btn {
            padding: 6px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin: 2px;
            display: inline-block;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 5px;
            color: white;
        }

        .permanent { background-color: #28a745; }
        .contract-of-service { background-color: orange; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>List of Employees</h2>

    <!-- Filter -->
    <form method="GET">
        <label>Show: </label>
        <select name="filter" onchange="this.form.submit()">
            <option value="All" <?= $filter == 'All' ? 'selected' : '' ?>>All</option>
            <option value="Permanent" <?= $filter == 'Permanent' ? 'selected' : '' ?>>Permanent</option>
            <option value="Contract of Service" <?= $filter == 'Contract of Service' ? 'selected' : '' ?>>Contract of Service</option>
        </select>
    </form>

    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Age</th>
                <th>Place of Assignment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()): 
            $img_file = (!empty($row['image']) && file_exists($image_folder . $row['image']))
                ? $row['image']
                : 'default.png';

            $statusClass = strtolower(str_replace(' ', '-', $row['status']));
        ?>
            <tr>
                <td><?= htmlspecialchars($row['employee_id']); ?></td>

                <td>
                    <img src="<?= $image_url . htmlspecialchars($img_file); ?>">
                </td>

                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= htmlspecialchars($row['age']); ?></td>
                <td><?= htmlspecialchars($row['place_of_assignment']); ?></td>

                <td>
                    <span class="badge <?= $statusClass ?>">
                        <?= htmlspecialchars($row['status']); ?>
                    </span>
                </td>

                <td>
                    <a href="employee_info.php?employee_id=<?= $row['employee_id']; ?>" class="btn">
                        View
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div style="margin-top: 20px; text-align: center;">
        <?php if ($page > 1): ?>
            <a class="btn" href="?filter=<?= $filter ?>&page=<?= $page - 1 ?>">Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="btn" href="?filter=<?= $filter ?>&page=<?= $i ?>"
               style="<?= $i == $page ? 'background-color:#333;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a class="btn" href="?filter=<?= $filter ?>&page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>

</div>

</body>
</html>