<?php
include 'config.php';
if ($conn->connect_error) die("Connection failed");

$image_folder = "assets/image/employee/";
if (!file_exists($image_folder)) mkdir($image_folder, 0777, true);

$successMessage = "";
$showSuccess = false;

/* FILTERS */
$statusFilter = $_GET['status'] ?? "";
$searchTerm = $_GET['search'] ?? "";
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

/* BUILD WHERE CLAUSE with SEARCH */
$where = "";
$params = [];
$types = "";

if ($statusFilter) {
    $where .= " status = ? ";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($searchTerm)) {
    $searchWildcard = "%" . $conn->real_escape_string($searchTerm) . "%";
    if ($where) {
        $where .= " AND (name LIKE ? OR assigned_section LIKE ? OR position_title LIKE ?) ";
    } else {
        $where .= " (name LIKE ? OR assigned_section LIKE ? OR position_title LIKE ?) ";
    }
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= "sss";
}

$whereSQL = $where ? "WHERE $where" : "";

/* GET TOTAL COUNT with search */
if (!empty($params)) {
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM employees $whereSQL");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query("SELECT COUNT(*) total FROM employees")->fetch_assoc()['total'];
}

$totalPages = ceil($total / $limit);

/* FETCH DATA with search and pagination */
$sql = "SELECT * FROM employees $whereSQL ORDER BY name ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

// Merge search params with limit & offset
$bindParams = $params;
$bindParams[] = $limit;
$bindParams[] = $offset;
$bindTypes = $types . "ii";

if (!empty($bindParams)) {
    $stmt->bind_param($bindTypes, ...$bindParams);
}
$stmt->execute();
$result = $stmt->get_result();

/* CREATE */
if(isset($_POST['add'])){
    $data = $_POST;
    $image_name = null;
    if(!empty($_FILES['image']['name'])){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_folder.$image_name);
    }
    $length_of_service = null;
    if(!empty($data['date_of_appointment'])){
        $start = new DateTime($data['date_of_appointment']);
        $now = new DateTime();
        $diff = $start->diff($now);
        $length_of_service = $diff->y . " years " . $diff->m . " months";
    }
    $stmt = $conn->prepare("INSERT INTO employees 
    (name,status,image,gender,date_of_birth,nosca_item_number,assigned_section,
    position_title,monthly_salary,civil_service_eligibility,education,date_of_appointment,
    length_of_service,date_of_last_promotion,step_increment,salary_grade)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssssssssssssss",
        $data['name'],$data['status'],$image_name,$data['gender'],
        $data['date_of_birth'],$data['nosca_item_number'],$data['assigned_section'],
        $data['position_title'],$data['monthly_salary'],$data['civil_service_eligibility'],
        $data['education'],$data['date_of_appointment'],$length_of_service,
        $data['date_of_last_promotion'],$data['step_increment'],$data['salary_grade']
    );
    $stmt->execute();
    $successMessage = "Employee successfully added!";
    $showSuccess = true;
}

/* DELETE */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $img = $conn->query("SELECT image FROM employees WHERE employee_id=$id")->fetch_assoc();
    if($img && $img['image'] && file_exists($image_folder.$img['image'])){
        unlink($image_folder.$img['image']);
    }
    $conn->query("DELETE FROM employees WHERE employee_id=$id");
    // Redirect to avoid resubmission, but keep filter/search
    header("Location: ?status=" . urlencode($statusFilter) . "&search=" . urlencode($searchTerm) . "&page=" . $page);
    exit();
}

/* UPDATE */
if(isset($_POST['update'])){
    $data = $_POST;
    $id = $data['id'];
    $image_name = $data['old_image'];
    if(!empty($_FILES['image']['name'])){
        if($image_name && file_exists($image_folder.$image_name)) unlink($image_folder.$image_name);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_folder.$image_name);
    }
    $length_of_service = null;
    if(!empty($data['date_of_appointment'])){
        $start = new DateTime($data['date_of_appointment']);
        $now = new DateTime();
        $diff = $start->diff($now);
        $length_of_service = $diff->y . " years " . $diff->m . " months";
    }
    $stmt = $conn->prepare("UPDATE employees SET 
    name=?,status=?,image=?,gender=?,date_of_birth=?,nosca_item_number=?,
    assigned_section=?,position_title=?,monthly_salary=?,
    civil_service_eligibility=?,education=?,date_of_appointment=?,
    length_of_service=?,date_of_last_promotion=?,step_increment=?,salary_grade=?
    WHERE employee_id=?");
    $stmt->bind_param(
        "ssssssssssssssssi",
        $data['name'],$data['status'],$image_name,$data['gender'],
        $data['date_of_birth'],$data['nosca_item_number'],$data['assigned_section'],
        $data['position_title'],$data['monthly_salary'],$data['civil_service_eligibility'],
        $data['education'],$data['date_of_appointment'],$length_of_service,
        $data['date_of_last_promotion'],$data['step_increment'],$data['salary_grade'],$id
    );
    $stmt->execute();
    $successMessage = "Employee successfully updated!";
    $showSuccess = true;
}

/* EDIT */
$edit = false;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $editData = $conn->query("SELECT * FROM employees WHERE employee_id=$id")->fetch_assoc();
    $edit = true;
}

// Counts for filter pills
$allCount = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];
$permCount = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Permanent'")->fetch_assoc()['c'];
$cosCount = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Contract of Service'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR CENRO - Employee Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --g950: #052710;
            --g900: #0a3d18;
            --g800: #145228;
            --g700: #1e6d38;
            --g600: #2a8a4a;
            --g500: #38a85f;
            --g400: #5abf7c;
            --g300: #84d49e;
            --g200: #b3e8c4;
            --g100: #dff5e7;
            --g50: #f0faf4;
            --gold: #c9a227;
            --gold-lt: #fdf3d0;
            --bg: #eef2ef;
            --surface: #ffffff;
            --surface-2: #f5f8f6;
            --border: rgba(0,0,0,0.07);
            --border-md: rgba(0,0,0,0.12);
            --text-1: #0f1a12;
            --text-2: #3d5445;
            --text-3: #7a9882;
            --sidebar-w: 240px;
            --radius-lg: 14px;
            --radius-md: 8px;
            --shadow-sm: 0 1px 4px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 18px rgba(0,0,0,0.09);
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-1);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: url('assets/image/cenro-bg.png') center/cover no-repeat;
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--g950);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        .sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 40% at 50% 0%, rgba(56,168,95,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .sb-header {
            padding: 26px 18px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sb-logo {
            width: 68px;
            height: 68px;
            background: #fff;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 0 0 3px rgba(201,162,39,0.4);
        }
        .sb-logo img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: contain;
        }
        .sb-brand {
            font-family: 'Sora', sans-serif;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            text-align: center;
        }
        .sb-nav {
            flex: 1;
            padding: 14px 10px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            overflow-y: auto;
        }
        .nav-sec {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: rgba(255,255,255,0.28);
            padding: 8px 8px 3px;
            margin-top: 6px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--radius-md);
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13.5px;
            transition: 0.2s var(--ease);
        }
        .nav-link i {
            width: 18px;
            font-size: 14px;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(3px);
        }
        .nav-link.active {
            background: rgba(56,168,95,0.22);
            color: var(--g300);
            font-weight: 500;
        }
        .sb-footer {
            padding: 12px 10px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 11px;
            border-radius: var(--radius-md);
            background: rgba(255,255,255,0.06);
        }
        .u-avatar {
            width: 32px;
            height: 32px;
            background: var(--g600);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            color: white;
        }
        .u-name { font-size: 12.5px; color: rgba(255,255,255,0.8); font-weight: 500; }
        .u-role { font-size: 10px; color: rgba(255,255,255,0.4); }

        /* MAIN LAYOUT */
        .main {
            margin-left: var(--sidebar-w);
            padding: 28px 32px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .ph-title {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-1);
        }
        .ph-sub {
            font-size: 13px;
            color: var(--text-3);
            margin-top: 4px;
        }
        .btn-primary {
            background: var(--g800);
            border: none;
            color: white;
            padding: 9px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-primary:hover {
            background: var(--g700);
            transform: translateY(-1px);
        }

        /* FILTER PILLS */
        .filter-pills {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            background: var(--surface);
            border: 1.5px solid var(--border-md);
            color: var(--text-2);
            text-decoration: none;
            transition: all 0.2s;
        }
        .pill:hover {
            border-color: var(--g400);
            background: var(--g50);
        }
        .pill.active {
            background: var(--g800);
            border-color: transparent;
            color: white;
        }
        .pill-count {
            background: rgba(0,0,0,0.08);
            padding: 1px 6px;
            border-radius: 16px;
            font-size: 11px;
        }
        .pill.active .pill-count {
            background: rgba(255,255,255,0.2);
        }

        /* TOOLBAR */
        .toolbar {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .search-wrap {
            position: relative;
            flex: 2;
            min-width: 250px;
        }
        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-3);
            font-size: 14px;
            pointer-events: none;
        }
        .search-input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1px solid var(--border-md);
            border-radius: 10px;
            background: var(--surface-2);
            font-size: 13.5px;
            outline: none;
            transition: 0.2s;
            font-family: inherit;
        }
        .search-input:focus {
            border-color: var(--g400);
            box-shadow: 0 0 0 3px rgba(90,191,124,0.15);
            background: white;
        }
        .clear-search {
            background: var(--surface-2);
            border: 1px solid var(--border-md);
            padding: 9px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s;
            text-decoration: none;
            color: var(--text-2);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .clear-search:hover {
            background: var(--g50);
            border-color: var(--g400);
        }
        .result-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            font-size: 13px;
            color: var(--text-3);
            flex-wrap: wrap;
            gap: 8px;
        }
        .search-highlight {
            background: rgba(90,191,124,0.2);
            color: var(--g800);
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 4px;
        }

        /* TABLE */
        .table-wrap {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
        }
        .emp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .emp-table th {
            background: var(--surface-2);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-3);
            border-bottom: 1px solid var(--border-md);
        }
        .emp-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text-2);
        }
        .emp-table tr {
            cursor: pointer;
            transition: background 0.15s;
        }
        .emp-table tr:hover {
            background: var(--g50);
        }
        .emp-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--g200);
        }
        .emp-initials {
            width: 40px;
            height: 40px;
            background: var(--g100);
            color: var(--g800);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        .emp-name {
            font-weight: 600;
            color: var(--text-1);
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 11.5px;
            font-weight: 500;
        }
        .badge-perm {
            background: var(--g100);
            color: var(--g800);
        }
        .badge-cos {
            background: #fef3c7;
            color: #9b6b00;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            background: var(--surface-2);
            border: 1px solid var(--border-md);
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            color: var(--text-2);
            transition: 0.2s;
            display: inline-block;
            margin: 0 3px;
        }
        .action-btn:hover {
            background: var(--g800);
            color: white;
            border-color: transparent;
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 24px;
            flex-wrap: wrap;
        }
        .pg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--surface);
            border: 1px solid var(--border-md);
            color: var(--text-2);
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        .pg-btn.active {
            background: var(--g800);
            color: white;
            border: none;
        }
        .pg-btn:hover:not(.disabled) {
            border-color: var(--g400);
            background: var(--g50);
        }

        /* DRAWER */
        .drawer-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(3px);
            z-index: 200;
        }
        .drawer {
            position: fixed;
            top: 0;
            right: -500px;
            width: 500px;
            max-width: 94vw;
            height: 100vh;
            background: var(--surface);
            z-index: 201;
            display: flex;
            flex-direction: column;
            transition: right 0.3s var(--ease);
            box-shadow: -8px 0 40px rgba(0,0,0,0.2);
            border-radius: 20px 0 0 20px;
        }
        .drawer.open {
            right: 0;
        }
        .drawer-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .drawer-header h3 {
            font-size: 18px;
            font-weight: 600;
        }
        .drawer-close {
            background: var(--surface-2);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 20px;
        }
        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }
        .drawer-footer {
            padding: 18px 24px;
            border-top: 1px solid var(--border);
            background: var(--surface-2);
        }
        .form-section {
            margin-bottom: 28px;
        }
        .form-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 16px;
            letter-spacing: 0.6px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }
        .form-row.single {
            grid-template-columns: 1fr;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-2);
        }
        .form-group input, .form-group select {
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid var(--border-md);
            background: var(--surface-2);
            font-size: 13px;
            font-family: inherit;
        }
        .btn-success {
            background: var(--g800);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            font-size: 14px;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-success:hover {
            background: var(--g700);
        }

        /* SUCCESS MODAL */
        .success-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 300;
            align-items: center;
            justify-content: center;
        }
        .success-overlay.show {
            display: flex;
        }
        .success-card {
            background: white;
            padding: 28px 32px;
            border-radius: 24px;
            text-align: center;
            max-width: 300px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        .success-icon {
            font-size: 42px;
            background: var(--g100);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--g700);
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            background: var(--surface);
            border-radius: var(--radius-lg);
        }
        
        @media (max-width: 800px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sb-header">
        <div class="sb-logo"><img src="assets/image/denr-logo.png" alt="DENR" onerror="this.style.display='none'"></div>
        <p class="sb-brand">Department of Environment & Natural Resources<br>CENRO</p>
    </div>
    <nav class="sb-nav">
        <span class="nav-sec">HR Hub</span>
        <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="#" class="nav-link active"><i class="fa-solid fa-users-viewfinder"></i> Employee Directory</a>
        <span class="nav-sec">Records</span>
        <a href="upload_form.php" class="nav-link"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Document</a>
        <a href="document_page.php" class="nav-link"><i class="fa-regular fa-folder-open"></i> All Documents</a>
    </nav>
    <div class="sb-footer">
        <div class="user-pill">
            <div class="u-avatar">AD</div>
            <div><div class="u-name">Admin User</div><div class="u-role">HR Manager</div></div>
        </div>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1 class="ph-title"><i class="fa-regular fa-id-card"></i> Manage Employee</h1>
            <p class="ph-sub" id="live-sub">Loading...</p>
        </div>
        <button class="btn-primary" onclick="openDrawer()"><i class="fa-solid fa-user-plus"></i> Add Employee</button>
    </div>

    <!-- Filter Pills -->
    <div class="filter-pills">
        <a href="?status=&page=1<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" class="pill <?= $statusFilter==''?'active':'' ?>">
            All <span class="pill-count"><?= $allCount ?></span>
        </a>
        <a href="?status=Permanent&page=1<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" class="pill <?= $statusFilter=='Permanent'?'active':'' ?>">
            Permanent <span class="pill-count"><?= $permCount ?></span>
        </a>
        <a href="?status=Contract of Service&page=1<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" class="pill <?= $statusFilter=='Contract of Service'?'active':'' ?>">
            Contract of Service <span class="pill-count"><?= $cosCount ?></span>
        </a>
    </div>

    <!-- Toolbar with Search -->
    <div class="toolbar">
        <form method="GET" id="searchForm" style="display: contents;">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <input type="hidden" name="page" value="1">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" id="searchInput" class="search-input" 
                       value="<?= htmlspecialchars($searchTerm) ?>" 
                       placeholder="Search by name, section, or position..." 
                       autocomplete="off">
            </div>
            <button type="submit" class="btn-primary" style="padding: 9px 18px;">
                <i class="fa-solid fa-search"></i> Search
            </button>
        </form>
        <?php if(!empty($searchTerm)): ?>
            <a href="?status=<?= urlencode($statusFilter) ?>" class="clear-search">
                <i class="fa-solid fa-times"></i> Clear Search
            </a>
        <?php endif; ?>
    </div>

    <!-- Result Bar with Search Info -->
    <div class="result-bar">
        <div>
            <i class="fa-regular fa-users"></i> 
            <strong><?= $total ?></strong> employee(s) found
            <?php if(!empty($searchTerm)): ?>
                for "<strong class="search-highlight"><?= htmlspecialchars($searchTerm) ?></strong>"
            <?php endif; ?>
        </div>
        <div>Page <?= $page ?> of <?= max(1, $totalPages) ?></div>
    </div>

    <!-- Employee Table -->
    <div class="table-wrap">
        <table class="emp-table" id="employeeTable">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th style="width: 70px;">Photo</th>
                    <th>Full Name</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($total === 0): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa-regular fa-face-frown" style="font-size: 48px; opacity: 0.5;"></i>
                            <p style="margin-top: 12px; font-size: 15px;">No employees found</p>
                            <?php if(!empty($searchTerm)): ?>
                                <p style="margin-top: 8px; font-size: 13px; color: var(--text-3);">
                                    Try a different search term or 
                                    <a href="?status=<?= urlencode($statusFilter) ?>" style="color: var(--g600);">clear the search</a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr onclick="window.location='employee_info_1.php?employee_id=<?= $row['employee_id'] ?>'" style="cursor: pointer;">
                    <td><?= $row['employee_id'] ?></td>
                    <td>
                        <?php if($row['image'] && file_exists($image_folder . $row['image'])): ?>
                            <img class="emp-photo" src="<?= $image_folder . $row['image'] ?>" alt="Photo">
                        <?php else: ?>
                            <div class="emp-initials"><?= strtoupper(substr($row['name'], 0, 2)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="emp-name"><?= htmlspecialchars($row['name']) ?></span></td>
                    <td>
                        <span class="badge <?= $row['status'] == 'Permanent' ? 'badge-perm' : 'badge-cos' ?>">
                            <?= $row['status'] == 'Permanent' ? 'Permanent' : 'COS' ?>
                        </span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <a href="?edit=<?= $row['employee_id'] ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchTerm) ?>" class="action-btn">
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </a>
                        <a href="?delete=<?= $row['employee_id'] ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchTerm) ?>" 
                           onclick="return confirm('Delete <?= addslashes(htmlspecialchars($row['name'])) ?> permanently?')" 
                           class="action-btn">
                            <i class="fa-regular fa-trash-can"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchTerm) ?>" 
               class="pg-btn <?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</main>

<!-- DRAWER OVERLAY -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- SLIDE-IN FORM (ADD / EDIT) -->
<div class="drawer" id="formDrawer">
    <div class="drawer-header">
        <h3><?= $edit ? '<i class="fa-regular fa-pen-to-square"></i> Edit Employee' : '<i class="fa-solid fa-user-plus"></i> Add New Employee' ?></h3>
        <button class="drawer-close" onclick="closeDrawer()">✕</button>
    </div>
    <div class="drawer-body">
        <form method="POST" enctype="multipart/form-data" id="empForm" action="">
            <input type="hidden" name="id" value="<?= $edit ? $editData['employee_id'] : '' ?>">
            <input type="hidden" name="old_image" value="<?= $edit ? $editData['image'] : '' ?>">

            <div class="form-section">
                <div class="form-section-title">Basic Information</div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" value="<?= $edit ? htmlspecialchars($editData['name']) : '' ?>" required placeholder="e.g., Juan M. Dela Cruz">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Employment Status</label>
                        <select name="status">
                            <option value="Permanent" <?= ($edit && $editData['status'] == 'Permanent') ? 'selected' : '' ?>>Permanent</option>
                            <option value="Contract of Service" <?= ($edit && $editData['status'] == 'Contract of Service') ? 'selected' : '' ?>>Contract of Service</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="Male" <?= ($edit && $editData['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($edit && $editData['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= $edit ? $editData['date_of_birth'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>NOSCA Item Number</label>
                        <input type="text" name="nosca_item_number" value="<?= $edit ? htmlspecialchars($editData['nosca_item_number']) : '' ?>" placeholder="e.g., DENR-2024-001">
                    </div>
                </div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Employee Photo</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if($edit && $editData['image']): ?>
                            <small style="color: var(--text-3); margin-top: 4px;">Current photo: <?= htmlspecialchars($editData['image']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Position & Assignment</div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Assigned Section/Unit</label>
                        <input type="text" name="assigned_section" value="<?= $edit ? htmlspecialchars($editData['assigned_section']) : '' ?>" placeholder="e.g., Administrative Division">
                    </div>
                </div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Position Title</label>
                        <input type="text" name="position_title" value="<?= $edit ? htmlspecialchars($editData['position_title']) : '' ?>" placeholder="e.g., Administrative Officer II">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Salary Grade</label>
                        <input type="number" name="salary_grade" value="<?= $edit ? $editData['salary_grade'] : '' ?>" placeholder="e.g., 15">
                    </div>
                    <div class="form-group">
                        <label>Monthly Salary (₱)</label>
                        <input type="text" name="monthly_salary" value="<?= $edit ? htmlspecialchars($editData['monthly_salary']) : '' ?>" placeholder="e.g., 32,000">
                    </div>
                </div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Step Increment</label>
                        <input type="text" name="step_increment" value="<?= $edit ? htmlspecialchars($editData['step_increment']) : '' ?>" placeholder="e.g., Step 3">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Qualifications</div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Highest Educational Attainment</label>
                        <input type="text" name="education" value="<?= $edit ? htmlspecialchars($editData['education']) : '' ?>" placeholder="e.g., Bachelor of Science">
                    </div>
                </div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Civil Service Eligibility</label>
                        <input type="text" name="civil_service_eligibility" value="<?= $edit ? htmlspecialchars($editData['civil_service_eligibility']) : '' ?>" placeholder="e.g., Career Service Professional">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Service Timeline</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Appointment</label>
                        <input type="date" name="date_of_appointment" value="<?= $edit ? $editData['date_of_appointment'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Date of Last Promotion</label>
                        <input type="date" name="date_of_last_promotion" value="<?= $edit ? $editData['date_of_last_promotion'] : '' ?>">
                    </div>
                </div>
                <small style="font-size: 11px; color: var(--text-3);">✓ Length of service is calculated automatically</small>
            </div>
        </form>
    </div>
    <div class="drawer-footer">
        <?php if($edit): ?>
            <button type="submit" name="update" form="empForm" class="btn-success">
                <i class="fa-regular fa-floppy-disk"></i> Update Employee
            </button>
        <?php else: ?>
            <button type="submit" name="add" form="empForm" class="btn-success">
                <i class="fa-solid fa-user-check"></i> Add Employee
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- SUCCESS MODAL -->
<div class="success-overlay" id="successOverlay">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h3 id="successText"></h3>
        <button class="btn-primary" style="margin-top: 16px; width: 100%; justify-content: center;" onclick="closeSuccess()">
            Done
        </button>
    </div>
</div>

<script>
    // Drawer controls
    const drawer = document.getElementById('formDrawer');
    const overlay = document.getElementById('drawerOverlay');
    
    function openDrawer() {
        drawer.classList.add('open');
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeDrawer() {
        drawer.classList.remove('open');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
        // Remove edit parameter from URL without reloading
        if (window.history.pushState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('edit');
            window.history.pushState({}, '', url);
        }
    }
    
    function showSuccess(msg) {
        document.getElementById('successText').innerText = msg;
        const successOverlay = document.getElementById('successOverlay');
        successOverlay.classList.add('show');
        
        // Auto close after 3 seconds
        setTimeout(function() {
            if (successOverlay.classList.contains('show')) {
                closeSuccess();
            }
        }, 3000);
    }
    
    function closeSuccess() {
        document.getElementById('successOverlay').classList.remove('show');
        // Reload the page to refresh data, preserving filters
        const url = new URL(window.location.href);
        url.searchParams.delete('edit');
        window.location.href = url.toString();
    }
    
    // Show success modal if there's a message
    <?php if($showSuccess && !empty($successMessage)): ?>
    showSuccess("<?= addslashes($successMessage) ?>");
    <?php endif; ?>
    
    // Auto-open drawer if editing
    <?php if($edit): ?>
    openDrawer();
    <?php endif; ?>
    
    // Live date/time
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateStr = now.toLocaleDateString('en-PH', options);
        const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
        const subElement = document.getElementById('live-sub');
        if (subElement) {
            subElement.innerText = `${dateStr} · ${timeStr}`;
        }
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Submit search on Enter key
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchForm').submit();
            }
        });
    }
    
    // Prevent drawer from closing when clicking inside form
    document.getElementById('formDrawer')?.addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>

</body>
</html>