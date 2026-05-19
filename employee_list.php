<?php
include 'config.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --------------------
// FILTER + SORT + SEARCH
// --------------------
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';
$sort   = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --------------------
// SORT LOGIC
// --------------------
$orderBy = "name ASC";

switch ($sort) {
    case 'name_desc':
        $orderBy = "name DESC";
        break;
    case 'age_asc':
        $orderBy = "date_of_birth DESC"; // younger first
        break;
    case 'age_desc':
        $orderBy = "date_of_birth ASC"; // older first
        break;
}

// --------------------
// PAGINATION
// --------------------
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// --------------------
// BUILD WHERE CLAUSE DYNAMICALLY
// --------------------
$where = [];
$params = [];
$types = "";

// Filter
if ($filter === 'Permanent' || $filter === 'Contract of Service') {
    $where[] = "status = ?";
    $params[] = $filter;
    $types .= "s";
}

// Search
if (!empty($search)) {
    $where[] = "(name LIKE ? OR place_of_assignment LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Combine WHERE
$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

// --------------------
// COUNT QUERY
// --------------------
$countSql = "SELECT COUNT(*) as total FROM employees $whereSQL";
$countStmt = $conn->prepare($countSql);

if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// --------------------
// MAIN QUERY
// --------------------
$sql = "
    SELECT employee_id, name, date_of_birth, assigned_section, status, image 
    FROM employees
    $whereSQL
    ORDER BY $orderBy
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

// Add limit + offset
$paramsWithLimit = $params;
$paramsWithLimit[] = $limit;
$paramsWithLimit[] = $offset;
$typesWithLimit = $types . "ii";

$stmt->bind_param($typesWithLimit, ...$paramsWithLimit);

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
       body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    background-image: url('./assets/images/cenro.jpeg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;

    opacity: 0.3; /* Adjust transparency here */
    
    z-index: -1;
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

        .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.back-btn {
    padding: 8px 14px;
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: 0.3s ease;
}

.back-btn:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-1px);
}

.back-btn:active {
    transform: scale(0.98);
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

        .search-box {
            padding: 6px;
            width: 200px;
        }

        
    </style>
</head>

<body>

<div class="container">
        <div class="header">
        <h2>List of Employees</h2>
        <a href="dashboard.php" class="back-btn">← Go Back</a>
    </div>

    <!-- FILTER + SEARCH -->
    <form method="GET">
        <label>Search: </label>
        <input type="text" name="search" class="search-box"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Enter name or place">

        <label>Show: </label>
        <select name="filter">
            <option value="All" <?= $filter == 'All' ? 'selected' : '' ?>>All</option>
            <option value="Permanent" <?= $filter == 'Permanent' ? 'selected' : '' ?>>Permanent</option>
            <option value="Contract of Service" <?= $filter == 'Contract of Service' ? 'selected' : '' ?>>Contract of Service</option>
        </select>

        <label>Sort by: </label>
        <select name="sort">
            <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
            <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
            <option value="age_asc" <?= $sort == 'age_asc' ? 'selected' : '' ?>>Age (Low-High)</option>
            <option value="age_desc" <?= $sort == 'age_desc' ? 'selected' : '' ?>>Age (High-Low)</option>
        </select>

        <button type="submit" class="btn">Apply</button>
    </form>

    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Age</th>
                <th>Assigned Section</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()): 

            $age = 'N/A';
            if (!empty($row['date_of_birth'])) {
                $dob = new DateTime($row['date_of_birth']);
                $age = (new DateTime())->diff($dob)->y;
            }

            $img_file = (!empty($row['image']) && file_exists($image_folder . $row['image']))
                ? $row['image']
                : 'default.png';

            $statusClass = strtolower(str_replace(' ', '-', $row['status']));
        ?>
            <tr>
                <td><?= htmlspecialchars($row['employee_id']); ?></td>
                <td><img src="<?= $image_url . htmlspecialchars($img_file); ?>"></td>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= $age; ?></td>
                <td><?= htmlspecialchars($row['assigned_section']); ?></td>
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

    <!-- PAGINATION -->
    <div style="margin-top: 20px; text-align: center;">

        <?php if ($page > 1): ?>
            <a class="btn" href="?filter=<?= $filter ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="btn"
               href="?filter=<?= $filter ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"
               style="<?= $i == $page ? 'background-color:#333;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a class="btn" href="?filter=<?= $filter ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>

    </div>

</div>

</body>
</html>