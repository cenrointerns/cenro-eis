<?php
include 'config.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';
$sort   = isset($_GET['sort'])   ? $_GET['sort']   : 'name_asc';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$view   = isset($_GET['view'])   ? $_GET['view']   : 'table'; // table | grid

$orderBy = "name ASC";
switch ($sort) {
    case 'name_desc':  $orderBy = "name DESC"; break;
    case 'age_asc':    $orderBy = "date_of_birth DESC"; break;
    case 'age_desc':   $orderBy = "date_of_birth ASC"; break;
}

$limit  = 12;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where  = []; $params = []; $types = "";

if ($filter === 'Permanent' || $filter === 'Contract of Service') {
    $where[] = "status = ?"; $params[] = $filter; $types .= "s";
}
if (!empty($search)) {
    $where[] = "(name LIKE ? OR assigned_section LIKE ?)";
    $sp = "%$search%"; $params[] = $sp; $params[] = $sp; $types .= "ss";
}

$whereSQL = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM employees $whereSQL");
if (!empty($params)) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows  = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $limit));

$sql = "SELECT employee_id, name, date_of_birth, assigned_section, status, image FROM employees $whereSQL ORDER BY $orderBy LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$p2 = $params; $p2[] = $limit; $p2[] = $offset;
$stmt->bind_param($types . "ii", ...$p2);
$stmt->execute();
$result = $stmt->get_result();

$image_folder = __DIR__ . "/assets/image/employee/";
$image_url    = "assets/image/employee/";

/* Counts for filter pills */
$allCount  = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];
$permCount = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Permanent'")->fetch_assoc()['c'];
$cosCount  = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Contract of Service'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Directory — DENR</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --g950: #052710; --g900: #0a3d18; --g800: #145228; --g700: #1e6d38;
    --g600: #2a8a4a; --g500: #38a85f; --g400: #5abf7c; --g300: #84d49e;
    --g200: #b3e8c4; --g100: #dff5e7; --g50: #f0faf4;
    --gold: #c9a227; --gold-lt: #fdf3d0;
    --bg: #eef2ef;
    --surface: #ffffff;
    --surface-2: #f5f8f6;
    --border: rgba(0,0,0,0.07);
    --border-md: rgba(0,0,0,0.12);
    --text-1: #0f1a12;
    --text-2: #3d5445;
    --text-3: #7a9882;
    --sidebar-w: 240px;
    --r: 14px; --r-sm: 8px; --r-xs: 5px;
    --sh-sm: 0 1px 4px rgba(0,0,0,0.06);
    --sh-md: 0 4px 18px rgba(0,0,0,0.09);
    --sh-lg: 0 10px 40px rgba(0,0,0,0.13);
    --ease: cubic-bezier(.4,0,.2,1);
}

body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-1); min-height: 100vh; }

/* WATERMARK */
body::before {
    content: '';
    position: fixed; inset: 0;
    background: url('./assets/images/cenro.jpeg') center / cover no-repeat;
    opacity: 0.07; z-index: 0; pointer-events: none;
}

/* ── SIDEBAR ── */
.sidebar {
    position: fixed; top: 0; left: 0;
    width: var(--sidebar-w); height: 100vh;
    background: var(--g950);
    display: flex; flex-direction: column;
    z-index: 100;
}
.sidebar::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 80% 40% at 50% 0%, rgba(56,168,95,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.sb-header {
    padding: 26px 18px 18px;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    position: relative;
}
.sb-logo {
    width: 66px; height: 66px; border-radius: 50%;
    background: white; padding: 5px;
    box-shadow: 0 0 0 3px rgba(201,162,39,0.5);
}
.sb-logo img { width: 100%; height: 100%; border-radius: 50%; object-fit: contain; }
.sb-brand {
    font-family: 'Sora', sans-serif; font-size: 10px; font-weight: 600;
    letter-spacing: 0.11em; text-transform: uppercase;
    color: rgba(255,255,255,0.4); text-align: center; line-height: 1.5;
}
.sb-nav { flex: 1; padding: 14px 10px; display: flex; flex-direction: column; gap: 3px; overflow-y: auto; }
.nav-sec { font-size: 10px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.28); padding: 8px 8px 3px; margin-top: 6px; }
.nav-link {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 11px; border-radius: var(--r-sm);
    color: rgba(255,255,255,0.6); text-decoration: none; font-size: 13.5px;
    transition: all 0.2s var(--ease);
    position: relative;
}
.nav-link i { width: 17px; text-align: center; font-size: 14px; flex-shrink: 0; }
.nav-link:hover { background: rgba(255,255,255,0.08); color: white; transform: translateX(3px); }
.nav-link.active { background: rgba(56,168,95,0.22); color: var(--g300); font-weight: 500; }
.nav-link.active::before {
    content: ''; position: absolute; left: 0; top: 22%; bottom: 22%;
    width: 3px; border-radius: 0 3px 3px 0; background: var(--g400);
}
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,0.07); }
.user-pill {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 11px; border-radius: var(--r-sm);
    background: rgba(255,255,255,0.06);
}
.u-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--g600); display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: white; flex-shrink: 0;
    font-family: 'Sora', sans-serif;
}
.u-name { font-size: 12.5px; color: rgba(255,255,255,0.78); font-weight: 500; }
.u-role { font-size: 10.5px; color: rgba(255,255,255,0.38); }

/* ── MAIN ── */
.main { margin-left: var(--sidebar-w); padding: 28px; min-height: 100vh; position: relative; z-index: 1; }

/* ── PAGE HEADER ── */
.page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 24px;
}
.ph-title { font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 700; color: var(--text-1); }
.ph-sub { font-size: 13px; color: var(--text-3); margin-top: 3px; }
.ph-actions { display: flex; align-items: center; gap: 8px; }

/* ── TOOLBAR ── */
.toolbar {
    background: var(--surface); border-radius: var(--r);
    border: 1px solid var(--border); box-shadow: var(--sh-sm);
    padding: 14px 18px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}

.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-wrap i {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%); color: var(--text-3); font-size: 13px; pointer-events: none;
}
.search-input {
    width: 100%; padding: 9px 12px 9px 34px;
    border: 1px solid var(--border-md); border-radius: 9px;
    background: var(--surface-2); font-size: 13.5px;
    font-family: 'DM Sans', sans-serif; color: var(--text-1);
    outline: none; transition: all 0.2s;
}
.search-input:focus { border-color: var(--g400); box-shadow: 0 0 0 3px rgba(90,191,124,0.14); background: white; }
.search-input::placeholder { color: var(--text-3); }

.tb-select {
    padding: 9px 32px 9px 12px;
    border: 1px solid var(--border-md); border-radius: 9px;
    background: var(--surface-2) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a9882' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 10px center;
    appearance: none; font-size: 13.5px; font-family: 'DM Sans', sans-serif;
    color: var(--text-1); cursor: pointer; outline: none;
    transition: all 0.2s;
}
.tb-select:focus { border-color: var(--g400); box-shadow: 0 0 0 3px rgba(90,191,124,0.14); }

.tb-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 9px 14px; border-radius: 9px; font-size: 13px; font-weight: 500;
    border: 1px solid var(--border-md); background: var(--surface-2);
    color: var(--text-2); cursor: pointer; text-decoration: none;
    font-family: 'DM Sans', sans-serif; transition: all 0.2s;
    white-space: nowrap;
}
.tb-btn:hover { background: var(--g50); border-color: var(--g300); color: var(--g700); }
.tb-btn.primary { background: var(--g800); color: white; border-color: transparent; }
.tb-btn.primary:hover { background: var(--g700); }
.tb-btn.active-view { background: var(--g50); border-color: var(--g400); color: var(--g700); }

.divider { width: 1px; height: 28px; background: var(--border-md); flex-shrink: 0; }

/* ── FILTER PILLS ── */
.filter-pills { display: flex; gap: 6px; margin-bottom: 18px; flex-wrap: wrap; }
.pill {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 30px;
    font-size: 13px; font-weight: 500; cursor: pointer;
    border: 1.5px solid var(--border-md); background: var(--surface);
    color: var(--text-2); text-decoration: none;
    transition: all 0.18s var(--ease);
}
.pill:hover { border-color: var(--g400); color: var(--g700); background: var(--g50); }
.pill.active { background: var(--g800); color: white; border-color: transparent; }
.pill-count {
    background: rgba(255,255,255,0.2); color: inherit;
    font-size: 11px; padding: 1px 6px; border-radius: 10px;
}
.pill:not(.active) .pill-count { background: var(--g100); color: var(--g700); }

/* ── RESULT BAR ── */
.result-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px; font-size: 13px; color: var(--text-3);
}
.result-count { font-weight: 500; color: var(--text-2); }

/* ── TABLE VIEW ── */
.table-wrap {
    background: var(--surface); border-radius: var(--r);
    border: 1px solid var(--border); box-shadow: var(--sh-sm);
    overflow: hidden;
}
.emp-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.emp-table thead th {
    background: var(--surface-2); padding: 11px 16px;
    text-align: left; font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--text-3); border-bottom: 1px solid var(--border-md);
    white-space: nowrap;
}
.emp-table thead th.sortable { cursor: pointer; user-select: none; }
.emp-table thead th.sortable:hover { color: var(--g600); }
.emp-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
    cursor: pointer;
}
.emp-table tbody tr:last-child { border-bottom: none; }
.emp-table tbody tr:hover { background: var(--g50); }
.emp-table td { padding: 11px 16px; vertical-align: middle; color: var(--text-2); }

.emp-cell { display: flex; align-items: center; gap: 11px; }
.emp-photo {
    width: 38px; height: 38px; border-radius: 50%;
    object-fit: cover; border: 2px solid var(--g200); flex-shrink: 0;
}
.emp-initials {
    width: 38px; height: 38px; border-radius: 50%;
    background: var(--g100); color: var(--g800);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
    font-family: 'Sora', sans-serif;
}
.emp-name { font-weight: 500; color: var(--text-1); font-size: 14px; }
.emp-id-small { font-size: 11px; color: var(--text-3); }

.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 20px;
    font-size: 11.5px; font-weight: 500; white-space: nowrap;
}
.badge-perm { background: var(--g100); color: var(--g800); }
.badge-cos { background: #fff3cd; color: #7d5a00; }

.action-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: 7px;
    font-size: 12px; font-weight: 500;
    text-decoration: none; transition: all 0.18s;
    border: 1px solid var(--border-md);
    background: var(--surface-2); color: var(--text-2);
}
.action-btn:hover { background: var(--g800); color: white; border-color: transparent; }

/* ── GRID VIEW ── */
.emp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 16px;
}
.emp-card {
    background: var(--surface); border-radius: var(--r);
    border: 1px solid var(--border); box-shadow: var(--sh-sm);
    padding: 22px 18px 16px;
    display: flex; flex-direction: column; align-items: center;
    text-align: center; text-decoration: none;
    transition: all 0.2s var(--ease);
    position: relative; overflow: hidden;
}
.emp-card::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0;
    height: 3px; background: var(--g400);
    transform: scaleX(0); transition: transform 0.22s var(--ease);
    transform-origin: left;
}
.emp-card:hover { transform: translateY(-4px); box-shadow: var(--sh-md); border-color: var(--g200); }
.emp-card:hover::after { transform: scaleX(1); }

.card-photo {
    width: 72px; height: 72px; border-radius: 50%;
    object-fit: cover; border: 3px solid var(--g200); margin-bottom: 12px;
}
.card-initials {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, var(--g200), var(--g100));
    color: var(--g800); display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; margin-bottom: 12px;
    font-family: 'Sora', sans-serif; flex-shrink: 0;
}
.card-name { font-weight: 600; font-size: 14px; color: var(--text-1); margin-bottom: 3px; }
.card-section { font-size: 12px; color: var(--text-3); margin-bottom: 10px; line-height: 1.4; }
.card-meta { display: flex; align-items: center; gap: 6px; justify-content: center; flex-wrap: wrap; }
.card-age { font-size: 11.5px; color: var(--text-3); }

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center; padding: 60px 20px;
    background: var(--surface); border-radius: var(--r);
    border: 1px solid var(--border);
}
.empty-icon { font-size: 40px; color: var(--g300); margin-bottom: 14px; }
.empty-title { font-family: 'Sora', sans-serif; font-size: 17px; font-weight: 600; color: var(--text-2); margin-bottom: 6px; }
.empty-sub { font-size: 13.5px; color: var(--text-3); }

/* ── PAGINATION ── */
.pagination { display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 22px; flex-wrap: wrap; }
.pg-btn {
    display: flex; align-items: center; justify-content: center;
    min-width: 36px; height: 36px; padding: 0 10px;
    border-radius: 8px; font-size: 13px; font-weight: 500;
    text-decoration: none; border: 1px solid var(--border-md);
    background: var(--surface); color: var(--text-2);
    transition: all 0.18s;
}
.pg-btn:hover { border-color: var(--g400); color: var(--g700); background: var(--g50); }
.pg-btn.active { background: var(--g800); color: white; border-color: transparent; }
.pg-btn.disabled { opacity: 0.38; pointer-events: none; }
.pg-gap { color: var(--text-3); padding: 0 4px; }

/* ── ANIMATIONS ── */
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.emp-table tbody tr, .emp-card {
    animation: fadeSlideUp 0.3s var(--ease) both;
}
<?php for ($i = 1; $i <= 12; $i++): ?>
.emp-table tbody tr:nth-child(<?= $i ?>),
.emp-card:nth-child(<?= $i ?>) { animation-delay: <?= ($i - 1) * 0.035 ?>s; }
<?php endfor; ?>

/* ── SKELETON LOADER ── */
.skeleton { background: linear-gradient(90deg, #e8eee9 25%, #f2f6f3 50%, #e8eee9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 4px; }
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

/* ── TOAST ── */
.toast-box {
    position: fixed; bottom: 24px; right: 24px; z-index: 9000;
    display: flex; flex-direction: column; gap: 8px;
}
.toast {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 15px; border-radius: 10px;
    background: var(--g950); color: white; font-size: 13px; font-weight: 500;
    box-shadow: var(--sh-lg); animation: fadeSlideUp 0.28s var(--ease);
    min-width: 210px;
}
.toast i { color: var(--g300); font-size: 14px; }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    :root { --sidebar-w: 0px; }
    .sidebar { display: none; }
    .main { margin-left: 0; padding: 16px; }
}
</style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
    <div class="sb-header">
        <div class="sb-logo">
            <img src="assets/images/denr remv bg.png" alt="DENR" onerror="this.style.display='none'">
        </div>
        <p class="sb-brand">Dept. of Environment<br>&amp; Natural Resources</p>
    </div>
    <nav class="sb-nav">
        <span class="nav-sec">Overview</span>
        <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <span class="nav-sec">Employees</span>
        <a href="create_employee.php" class="nav-link"><i class="fa-solid fa-user-gear"></i> Manage Employee</a>
        <a href="employee_list.php" class="nav-link active"><i class="fa-solid fa-users"></i> Employee List</a>
        <span class="nav-sec">Documents</span>
        <a href="upload_form.php" class="nav-link"><i class="fa-solid fa-file-arrow-up"></i> Upload Document</a>
        <a href="document_page.php" class="nav-link"><i class="fa-solid fa-folder-open"></i> All Documents</a>
        <span class="nav-sec">System</span>
        <a href="create_announcement.php" class="nav-link"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
        <a href="change_employee_id.php" class="nav-link"><i class="fa-solid fa-gear"></i> Change ID</a>
    </nav>
    <div class="sb-footer">
        <div class="user-pill">
            <div class="u-avatar">AD</div>
            <div><div class="u-name">Admin</div><div class="u-role">Administrator</div></div>
        </div>
        <a href="dashboard.php" class="nav-link" style="margin-top:6px;">
            <i class="fa-solid fa-chevron-left"></i> Back to Dashboard
        </a>
    </div>
</aside>

<!-- ── MAIN ── -->
<main class="main">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="ph-title">Employee Directory</h1>
            <p class="ph-sub" id="live-sub">Loading...</p>
        </div>
        <div class="ph-actions">
            <a href="create_employee.php" class="tb-btn primary">
                <i class="fa-solid fa-user-plus"></i> Add Employee
            </a>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="filter-pills">
        <?php
        $filters = [
            'All' => $allCount,
            'Permanent' => $permCount,
            'Contract of Service' => $cosCount,
        ];
        foreach ($filters as $label => $count):
            $active = $filter === $label ? 'active' : '';
            $href = "?filter=" . urlencode($label) . "&sort=" . urlencode($sort) . "&search=" . urlencode($search) . "&view=" . urlencode($view);
        ?>
        <a href="<?= $href ?>" class="pill <?= $active ?>">
            <?= htmlspecialchars($label) ?>
            <span class="pill-count"><?= $count ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form method="GET" style="display:contents;" id="filter-form">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="hidden" name="view"   value="<?= htmlspecialchars($view) ?>">
            <input type="hidden" name="page"   value="1">

            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" id="search-input" class="search-input"
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search by name or assignment…"
                       autocomplete="off">
            </div>

            <select name="sort" class="tb-select" onchange="this.form.submit()">
                <option value="name_asc"  <?= $sort=='name_asc'  ?'selected':'' ?>>Name A → Z</option>
                <option value="name_desc" <?= $sort=='name_desc' ?'selected':'' ?>>Name Z → A</option>
                <option value="age_asc"   <?= $sort=='age_asc'   ?'selected':'' ?>>Age: Low → High</option>
                <option value="age_desc"  <?= $sort=='age_desc'  ?'selected':'' ?>>Age: High → Low</option>
            </select>

            <button type="submit" class="tb-btn primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>

        <div class="divider"></div>

        <!-- View Toggle -->
        <a href="?filter=<?= urlencode($filter) ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&view=table&page=<?= $page ?>"
           class="tb-btn <?= $view=='table'?'active-view':'' ?>" title="Table view">
            <i class="fa-solid fa-table-list"></i>
        </a>
        <a href="?filter=<?= urlencode($filter) ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&view=grid&page=<?= $page ?>"
           class="tb-btn <?= $view=='grid'?'active-view':'' ?>" title="Grid view">
            <i class="fa-solid fa-grip"></i>
        </a>
    </div>

    <!-- Result Bar -->
    <div class="result-bar">
        <span>
            Showing <span class="result-count"><?= $totalRows ?></span> employee<?= $totalRows != 1 ? 's' : '' ?>
            <?= !empty($search) ? ' for "<em>' . htmlspecialchars($search) . '</em>"' : '' ?>
        </span>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
    </div>

    <?php
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    ?>

    <?php if (empty($rows)): ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-icon"><i class="fa-solid fa-users-slash"></i></div>
        <div class="empty-title">No employees found</div>
        <div class="empty-sub">Try adjusting your search or filter criteria</div>
    </div>

    <?php elseif ($view === 'grid'): ?>
    <!-- GRID VIEW -->
    <div class="emp-grid">
    <?php foreach ($rows as $row):
        $age = 'N/A';
        if (!empty($row['date_of_birth'])) {
            $dob = new DateTime($row['date_of_birth']);
            $age = (new DateTime())->diff($dob)->y . ' yrs';
        }
        $img_file = (!empty($row['image']) && file_exists($image_folder . $row['image'])) ? $row['image'] : null;
        $initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $row['name']), 0, 2))));
        $bClass    = $row['status'] === 'Permanent' ? 'badge-perm' : 'badge-cos';
    ?>
        <a href="employee_info.php?employee_id=<?= $row['employee_id'] ?>" class="emp-card">
            <?php if ($img_file): ?>
                <img src="<?= $image_url . htmlspecialchars($img_file) ?>" class="card-photo" alt="<?= htmlspecialchars($row['name']) ?>">
            <?php else: ?>
                <div class="card-initials"><?= $initials ?></div>
            <?php endif; ?>
            <div class="card-name"><?= htmlspecialchars($row['name']) ?></div>
            <div class="card-section"><?= htmlspecialchars($row['assigned_section'] ?? '—') ?></div>
            <div class="card-meta">
                <span class="badge <?= $bClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                <?php if ($age !== 'N/A'): ?>
                <span class="card-age">· <?= $age ?></span>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- TABLE VIEW -->
    <div class="table-wrap">
        <table class="emp-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th class="sortable" onclick="sortBy('name')">Name <i class="fa-solid fa-sort" style="font-size:10px;opacity:.5;"></i></th>
                    <th>Age</th>
                    <th>Section/Unit</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row):
                $age = 'N/A';
                if (!empty($row['date_of_birth'])) {
                    $dob = new DateTime($row['date_of_birth']);
                    $age = (new DateTime())->diff($dob)->y;
                }
                $img_file = (!empty($row['image']) && file_exists($image_folder . $row['image'])) ? $row['image'] : null;
                $initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $row['name']), 0, 2))));
                $bClass    = $row['status'] === 'Permanent' ? 'badge-perm' : 'badge-cos';
            ?>
                <tr onclick="window.location='employee_info.php?employee_id=<?= $row['employee_id'] ?>'" title="View profile">
                    <td style="color:var(--text-3);font-size:12px;"><?= htmlspecialchars($row['employee_id']) ?></td>
                    <td>
                        <div class="emp-cell">
                            <?php if ($img_file): ?>
                                <img src="<?= $image_url . htmlspecialchars($img_file) ?>" class="emp-photo" alt="">
                            <?php else: ?>
                                <div class="emp-initials"><?= $initials ?></div>
                            <?php endif; ?>
                            <div>
                                <div class="emp-name"><?= htmlspecialchars($row['name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= $age !== 'N/A' ? $age . ' yrs' : '—' ?></td>
                    <td><?= htmlspecialchars($row['assigned_section'] ?? '—') ?></td>
                    <td><span class="badge <?= $bClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td onclick="event.stopPropagation()">
                        <a href="employee_info.php?employee_id=<?= $row['employee_id'] ?>" class="action-btn">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        $base = "?filter=" . urlencode($filter) . "&sort=" . urlencode($sort) . "&search=" . urlencode($search) . "&view=" . urlencode($view) . "&page=";

        // Prev
        if ($page > 1)
            echo '<a href="' . $base . ($page - 1) . '" class="pg-btn"><i class="fa-solid fa-chevron-left"></i></a>';
        else
            echo '<span class="pg-btn disabled"><i class="fa-solid fa-chevron-left"></i></span>';

        // Page numbers with ellipsis
        $window = 2;
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == 1 || $i == $totalPages || abs($i - $page) <= $window) {
                $active = $i == $page ? 'active' : '';
                echo '<a href="' . $base . $i . '" class="pg-btn ' . $active . '">' . $i . '</a>';
            } elseif (abs($i - $page) == $window + 1) {
                echo '<span class="pg-gap">…</span>';
            }
        }

        // Next
        if ($page < $totalPages)
            echo '<a href="' . $base . ($page + 1) . '" class="pg-btn"><i class="fa-solid fa-chevron-right"></i></a>';
        else
            echo '<span class="pg-btn disabled"><i class="fa-solid fa-chevron-right"></i></span>';
        ?>
    </div>
    <?php endif; ?>

</main>

<!-- Toast -->
<div class="toast-box" id="toast-box"></div>

<script>
/* Live subtitle */
function tick() {
    const now = new Date();
    document.getElementById('live-sub').textContent =
        now.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
        + ' · ' + now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit' });
}
tick(); setInterval(tick, 1000);

/* Debounced live search */
let searchTimer;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 480);
});

/* Sort helper */
function sortBy(col) {
    const current = '<?= $sort ?>';
    let next = col + '_asc';
    if (current === col + '_asc') next = col + '_desc';
    const url = new URL(location.href);
    url.searchParams.set('sort', next);
    url.searchParams.set('page', 1);
    location.href = url;
}

/* Toast */
function showToast(msg) {
    const box = document.getElementById('toast-box');
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = `<i class="fa-solid fa-circle-check"></i>${msg}`;
    box.appendChild(t);
    setTimeout(() => {
        t.style.opacity = '0'; t.style.transform = 'translateY(6px)';
        t.style.transition = '0.3s';
        setTimeout(() => t.remove(), 300);
    }, 2400);
}

/* Keyboard shortcut: / = focus search */
document.addEventListener('keydown', e => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
        e.preventDefault();
        document.getElementById('search-input').focus();
        showToast('Search activated — type to filter');
    }
    if (e.key === 'Escape') document.getElementById('search-input').blur();
});
</script>

</body>
</html>