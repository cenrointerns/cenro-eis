<?php
session_start();
include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userName = "Admin";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userQuery = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userName = $row['username'];
    }
}

$totalQuery = "SELECT COUNT(*) AS total FROM employees";
$totalResult = $conn->query($totalQuery);
$totalEmployees = $totalResult->fetch_assoc()['total'] ?? 0;

$cosQuery = "SELECT COUNT(*) AS total FROM employees WHERE status = 'Contract of Service'";
$cosResult = $conn->query($cosQuery);
$cosCount = $cosResult->fetch_assoc()['total'] ?? 0;

$permQuery = "SELECT COUNT(*) AS total FROM employees WHERE status = 'Permanent'";
$permResult = $conn->query($permQuery);
$permCount = $permResult->fetch_assoc()['total'] ?? 0;

$docQuery = "SELECT COUNT(*) AS total FROM documents";
$docResult = $conn->query($docQuery);
$docCount = $docResult->fetch_assoc()['total'] ?? 0;

/* Monthly uploads for chart (last 6 months) */
$monthlyQuery = "
    SELECT DATE_FORMAT(uploaded_at, '%b') AS month, COUNT(*) AS count
    FROM documents
    WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(uploaded_at, '%Y-%m')
    ORDER BY MIN(uploaded_at)
";
$monthlyResult = $conn->query($monthlyQuery);
$monthlyLabels = [];
$monthlyData = [];
if ($monthlyResult) {
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthlyLabels[] = $row['month'];
        $monthlyData[] = (int)$row['count'];
    }
}

/* Status distribution for doughnut */
$statusQuery = "SELECT status, COUNT(*) AS count FROM employees GROUP BY status";
$statusResult = $conn->query($statusQuery);
$statusLabels = [];
$statusData = [];
if ($statusResult) {
    while ($row = $statusResult->fetch_assoc()) {
        $statusLabels[] = $row['status'];
        $statusData[] = (int)$row['count'];
    }
}

$activityQuery = "
SELECT e.name, d.file_name, d.document_type, d.uploaded_at
FROM documents d
JOIN employees e ON e.employee_id = d.employee_id
ORDER BY d.uploaded_at DESC
LIMIT 8
";
$activityResult = $conn->query($activityQuery);
$activities = [];
if ($activityResult) {
    while ($row = $activityResult->fetch_assoc()) {
        $activities[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DENR — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --green-950: #052710;
    --green-900: #0a3d18;
    --green-800: #145228;
    --green-700: #1e6d38;
    --green-600: #2a8a4a;
    --green-500: #38a85f;
    --green-400: #5abf7c;
    --green-300: #84d49e;
    --green-200: #b3e8c4;
    --green-100: #dff5e7;
    --green-50:  #f0faf4;

    --gold-500: #c9a227;
    --gold-400: #e0b93a;
    --gold-100: #fdf3d0;

    --bg: #f0f4f1;
    --surface: #ffffff;
    --surface-2: #f6f9f7;
    --border: rgba(0,0,0,0.08);
    --border-strong: rgba(0,0,0,0.14);
    --text-primary: #111c14;
    --text-secondary: #4a5e50;
    --text-muted: #8aa090;
    --sidebar-w: 240px;
    --radius: 14px;
    --radius-sm: 8px;
    --shadow-sm: 0 1px 4px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.09);
    --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
    --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text-primary);
    min-height: 100vh;
    overflow-x: hidden;
}

/* ── SIDEBAR ──────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: var(--sidebar-w);
    height: 100vh;
    background: var(--green-950);
    display: flex;
    flex-direction: column;
    z-index: 100;
    transition: transform var(--transition);
    overflow: hidden;
}

.sidebar::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 40% at 50% 0%, rgba(56,168,95,0.18) 0%, transparent 70%),
        radial-gradient(ellipse 60% 30% at 50% 100%, rgba(201,162,39,0.10) 0%, transparent 70%);
    pointer-events: none;
}

.sidebar-header {
    padding: 28px 20px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    position: relative;
}

.sidebar-logo-wrap {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: white;
    padding: 6px;
    box-shadow: 0 0 0 3px rgba(201,162,39,0.5);
    transition: box-shadow var(--transition);
}
.sidebar-logo-wrap:hover {
    box-shadow: 0 0 0 4px rgba(201,162,39,0.9), 0 8px 24px rgba(0,0,0,0.3);
}
.sidebar-logo-wrap img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: contain;
}

.sidebar-title {
    font-family: 'Sora', sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    text-align: center;
    line-height: 1.5;
}

.sidebar-nav {
    flex: 1;
    padding: 16px 12px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    padding: 8px 8px 4px;
    margin-top: 8px;
}
.nav-label:first-child { margin-top: 0; }

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    color: rgba(255,255,255,0.65);
    text-decoration: none;
    font-size: 14px;
    font-weight: 400;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}
.nav-link i { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }
.nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: white;
    transform: translateX(3px);
}
.nav-link.active {
    background: rgba(56,168,95,0.25);
    color: var(--green-300);
    font-weight: 500;
}
.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 3px;
    border-radius: 0 4px 4px 0;
    background: var(--green-400);
}

.sidebar-footer {
    padding: 14px 12px;
    border-top: 1px solid rgba(255,255,255,0.07);
}

.user-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.06);
    cursor: default;
}
.user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--green-600);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Sora', sans-serif;
    font-size: 12px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}
.user-name { font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 500; }
.user-role { font-size: 11px; color: rgba(255,255,255,0.4); }

/* ── MAIN ──────────────────────────────────── */
.main {
    margin-left: var(--sidebar-w);
    padding: 28px;
    min-height: 100vh;
}

/* ── TOPBAR ──────────────────────────────────── */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
}

.topbar-left h1 {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.1;
}
.topbar-left p {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 3px;
}

.topbar-right { display: flex; align-items: center; gap: 10px; }

.topbar-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid var(--border-strong);
    background: var(--surface);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}
.topbar-btn:hover { background: var(--green-50); color: var(--green-700); border-color: var(--green-300); }
.topbar-btn.primary {
    background: var(--green-800);
    color: white;
    border-color: transparent;
}
.topbar-btn.primary:hover { background: var(--green-700); }

.live-badge {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 14px;
    border-radius: 9px;
    background: var(--green-50);
    border: 1px solid var(--green-200);
    font-size: 12px;
    font-weight: 500;
    color: var(--green-700);
}
.live-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--green-500);
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(0.8); }
}

/* ── STAT CARDS ──────────────────────────────── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 22px 20px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
    cursor: default;
    transition: var(--transition);
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-strong);
}
.stat-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--card-accent, var(--green-400));
    border-radius: 0 0 var(--radius) var(--radius);
    transform: scaleX(0);
    transition: transform var(--transition);
    transform-origin: left;
}
.stat-card:hover::after { transform: scaleX(1); }

.stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    margin-bottom: 14px;
    background: var(--card-icon-bg, var(--green-100));
    color: var(--card-icon-color, var(--green-700));
}
.stat-value {
    font-family: 'Sora', sans-serif;
    font-size: 30px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
    counter-reset: num var(--val, 0);
}
.stat-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 4px;
    font-weight: 400;
}
.stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 500;
    margin-top: 10px;
    padding: 3px 8px;
    border-radius: 20px;
}
.stat-trend.up { background: #e8f8ef; color: #1a7a42; }
.stat-trend.neutral { background: #f3f4f6; color: #6b7280; }

.stat-card:nth-child(1) { --card-accent: #38a85f; --card-icon-bg: #dff5e7; --card-icon-color: #145228; }
.stat-card:nth-child(2) { --card-accent: #3b82f6; --card-icon-bg: #dbeafe; --card-icon-color: #1d4ed8; }
.stat-card:nth-child(3) { --card-accent: #f59e0b; --card-icon-bg: #fef3c7; --card-icon-color: #92400e; }
.stat-card:nth-child(4) { --card-accent: #8b5cf6; --card-icon-bg: #ede9fe; --card-icon-color: #5b21b6; }

/* ── CHART ROW ──────────────────────────────── */
.chart-row {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 18px;
    margin-bottom: 24px;
}

.panel {
    background: var(--surface);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.panel-header {
    padding: 18px 20px 0;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 4px;
}
.panel-title {
    font-family: 'Sora', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
}
.panel-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

.panel-body { padding: 16px 20px 20px; }

.chart-wrap { position: relative; width: 100%; height: 220px; }

/* Doughnut center */
.donut-wrap {
    position: relative;
    width: 180px; height: 180px;
    margin: 0 auto 16px;
}
.donut-center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}
.donut-center .big { font-family: 'Sora', sans-serif; font-size: 26px; font-weight: 700; color: var(--text-primary); }
.donut-center .sm { font-size: 11px; color: var(--text-muted); }

.legend-item {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    cursor: default;
    transition: var(--transition);
}
.legend-item:last-child { border-bottom: none; }
.legend-item:hover { background: var(--surface-2); margin: 0 -8px; padding: 6px 8px; border-radius: 6px; }
.legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.legend-name { flex: 1; color: var(--text-secondary); }
.legend-val { font-weight: 600; color: var(--text-primary); }

/* ── BOTTOM ROW ──────────────────────────────── */
.bottom-row {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 18px;
}

/* Activity table */
.activity-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.activity-table th {
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    padding: 0 12px 10px;
    border-bottom: 1px solid var(--border);
}
.activity-table td {
    padding: 11px 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text-secondary);
    vertical-align: middle;
}
.activity-table tr:last-child td { border-bottom: none; }
.activity-table tr:hover td { background: var(--surface-2); }

.emp-cell { display: flex; align-items: center; gap: 8px; }
.emp-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--green-100);
    color: var(--green-800);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}
.emp-name { font-weight: 500; color: var(--text-primary); }

.doc-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 500;
    background: var(--green-50);
    color: var(--green-700);
    border: 1px solid var(--green-200);
}

/* Quick actions */
.quick-actions { display: flex; flex-direction: column; gap: 10px; padding: 8px 0; }
.qa-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    text-decoration: none;
    border: 1px solid var(--border);
    background: var(--surface-2);
    transition: var(--transition);
    cursor: pointer;
}
.qa-item:hover {
    background: var(--green-50);
    border-color: var(--green-300);
    transform: translateX(3px);
}
.qa-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.qa-text .qa-title { font-size: 13px; font-weight: 500; color: var(--text-primary); }
.qa-text .qa-sub { font-size: 11px; color: var(--text-muted); }
.qa-arrow { margin-left: auto; color: var(--text-muted); font-size: 12px; }

/* ── SEARCH BAR ──────────────────────────────── */
.search-wrap {
    position: relative;
    width: 240px;
}
.search-wrap i {
    position: absolute;
    left: 11px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 13px;
    pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 8px 12px 8px 32px;
    border-radius: 9px;
    border: 1px solid var(--border-strong);
    background: var(--surface);
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-primary);
    outline: none;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}
.search-input:focus {
    border-color: var(--green-400);
    box-shadow: 0 0 0 3px rgba(90,191,124,0.15);
}

/* ── WATERMARK ──────────────────────────────── */
.watermark {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: url("https://upload.wikimedia.org/wikipedia/commons/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg") center / 360px no-repeat;
    opacity: 0.04;
}

/* ── MODAL ──────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open { display: flex; }

.modal-box {
    background: white;
    border-radius: 18px;
    padding: 32px 28px 24px;
    width: 340px;
    text-align: center;
    box-shadow: var(--shadow-lg);
    animation: modal-in 0.2s ease;
}
@keyframes modal-in {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-icon {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 16px;
}
.modal-box h3 { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.modal-box p { font-size: 14px; color: var(--text-muted); line-height: 1.5; }
.modal-actions { display: flex; gap: 10px; margin-top: 22px; }
.btn { flex: 1; padding: 11px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: var(--transition); font-family: 'DM Sans', sans-serif; }
.btn-cancel { background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--border-strong); }
.btn-cancel:hover { background: #f3f4f6; }
.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover { background: #b91c1c; }

/* ── TOAST ──────────────────────────────────── */
.toast-container { position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 8px; z-index: 9998; }
.toast {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px;
    border-radius: 10px;
    background: var(--green-950);
    color: white;
    font-size: 13px;
    font-weight: 500;
    box-shadow: var(--shadow-lg);
    animation: toast-in 0.3s ease;
    min-width: 220px;
}
@keyframes toast-in {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.toast i { font-size: 15px; color: var(--green-300); }

/* ── ANIMATIONS ──────────────────────────────── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
.stat-card, .panel, .bottom-row > * {
    animation: fadeUp 0.45s ease both;
}
.stat-card:nth-child(1) { animation-delay: 0.05s; }
.stat-card:nth-child(2) { animation-delay: 0.10s; }
.stat-card:nth-child(3) { animation-delay: 0.15s; }
.stat-card:nth-child(4) { animation-delay: 0.20s; }

/* ── COUNTER ANIM ──────────────────────────── */
.count-up { display: inline-block; }

/* ── RESPONSIVE ──────────────────────────────── */
@media (max-width: 1100px) {
    .stat-grid { grid-template-columns: repeat(2,1fr); }
    .chart-row, .bottom-row { grid-template-columns: 1fr; }
}
@media (max-width: 700px) {
    :root { --sidebar-w: 0px; }
    .sidebar { transform: translateX(-240px); }
    .main { margin-left: 0; padding: 16px; }
    .stat-grid { grid-template-columns: 1fr 1fr; }
    .topbar { flex-wrap: wrap; gap: 10px; }
}
</style>
</head>
<body>

<div class="watermark" aria-hidden="true"></div>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo-wrap">
            <img src="assets/images/denr remv bg.png" alt="DENR Logo" onerror="this.style.display='none'">
        </div>
        <p class="sidebar-title">Department of Environment<br>&amp; Natural Resources</p>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-label">Overview</span>
        <a href="dashboard.php" class="nav-link active">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>

        <span class="nav-label">Employees</span>
        <a href="create_employee.php" class="nav-link">
            <i class="fa-solid fa-user-gear"></i> Manage Employee
        </a>
        <a href="employee_list.php" class="nav-link">
            <i class="fa-solid fa-users"></i> Employee List
        </a>

        <span class="nav-label">Documents</span>
        <a href="upload_form.php" class="nav-link">
            <i class="fa-solid fa-file-arrow-up"></i> Upload Document
        </a>
        <a href="document_page.php" class="nav-link">
            <i class="fa-solid fa-folder-open"></i> All Documents
        </a>

        <span class="nav-label">System</span>
        <a href="create_announcement.php" class="nav-link">
            <i class="fa-solid fa-bullhorn"></i> Announcements
        </a>
        <a href="change_employee_id.php" class="nav-link">
            <i class="fa-solid fa-gear"></i> Change ID
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-pill">
            <div class="user-avatar"><?= strtoupper(substr($userName, 0, 2)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="#" onclick="openLogoutModal(event)" class="nav-link" style="margin-top:8px; color: rgba(255,100,100,0.7);">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>

<!-- ── MAIN ── -->
<main class="main" style="position: relative; z-index: 1;">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <h1>Dashboard</h1>
            <p id="live-clock">Loading date...</p>
        </div>
        <div class="topbar-right">
            <div class="live-badge">
                <span class="live-dot"></span>
                Live
            </div>
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="search-input" placeholder="Search employees..." id="search-input">
            </div>
            <a href="upload_form.php" class="topbar-btn primary">
                <i class="fa-solid fa-plus"></i> Add Document
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid">
        <div class="stat-card" onclick="showToast('Viewing all employees')">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-value count-up" data-target="<?= $totalEmployees ?>">0</div>
            <div class="stat-label">Total Employees</div>
            <div class="stat-trend neutral"><i class="fa-solid fa-building"></i> All Units</div>
        </div>

        <div class="stat-card" onclick="showToast('Contract of Service employees')">
            <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fa-solid fa-file-contract"></i></div>
            <div class="stat-value count-up" data-target="<?= $cosCount ?>">0</div>
            <div class="stat-label">Contract of Service</div>
            <div class="stat-trend neutral"><i class="fa-solid fa-percent"></i>
                <?= $totalEmployees > 0 ? round($cosCount / $totalEmployees * 100) : 0 ?>% of total
            </div>
        </div>

        <div class="stat-card" onclick="showToast('Permanent employees')">
            <div class="stat-icon" style="background:#fef3c7;color:#92400e"><i class="fa-solid fa-user-tie"></i></div>
            <div class="stat-value count-up" data-target="<?= $permCount ?>">0</div>
            <div class="stat-label">Permanent</div>
            <div class="stat-trend up"><i class="fa-solid fa-percent"></i>
                <?= $totalEmployees > 0 ? round($permCount / $totalEmployees * 100) : 0 ?>% of total
            </div>
        </div>

        <div class="stat-card" onclick="showToast('Total documents in system')">
            <div class="stat-icon" style="background:#ede9fe;color:#5b21b6"><i class="fa-solid fa-folder-open"></i></div>
            <div class="stat-value count-up" data-target="<?= $docCount ?>">0</div>
            <div class="stat-label">Total Documents</div>
            <div class="stat-trend up"><i class="fa-solid fa-arrow-trend-up"></i> On record</div>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="chart-row">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Document Uploads</div>
                    <div class="panel-subtitle">Last 6 months activity</div>
                </div>
                <div style="display:flex;gap:6px;">
                    <button class="topbar-btn" onclick="toggleChartType()" style="padding:5px 10px;font-size:12px;">
                        <i class="fa-solid fa-chart-bar"></i> Toggle
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-wrap">
                    <canvas id="uploadChart" role="img" aria-label="Bar chart of document uploads over last 6 months">Document upload activity</canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Employment Status</div>
                    <div class="panel-subtitle">Distribution breakdown</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="donut-wrap">
                    <canvas id="statusChart" role="img" aria-label="Doughnut chart showing employee status distribution">Employment status distribution</canvas>
                    <div class="donut-center">
                        <div class="big"><?= $totalEmployees ?></div>
                        <div class="sm">Total</div>
                    </div>
                </div>
                <div id="status-legend"></div>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="bottom-row">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Recent Activity</div>
                    <div class="panel-subtitle">Latest document uploads</div>
                </div>
                <button class="topbar-btn" style="padding:5px 12px;font-size:12px;" onclick="filterActivity()">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
            <div class="panel-body" style="padding-top:0;">
                <table class="activity-table" id="activity-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Document</th>
                            <th>Type</th>
                            <th>Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $row): ?>
                        <tr data-search="<?= strtolower(htmlspecialchars($row['name'])) ?>">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar"><?= strtoupper(substr($row['name'],0,2)) ?></div>
                                    <span class="emp-name"><?= htmlspecialchars($row['name']) ?></span>
                                </div>
                            </td>
                            <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?= htmlspecialchars($row['file_name']) ?>
                            </td>
                            <td><span class="doc-badge"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($row['document_type']) ?></span></td>
                            <td style="white-space:nowrap; color:var(--text-muted);"><?= date('M d, Y', strtotime($row['uploaded_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:32px;">No recent activity found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Quick Actions</div>
                    <div class="panel-subtitle">Common tasks</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="quick-actions">
                    <a href="create_employee.php" class="qa-item">
                        <div class="qa-icon" style="background:#dff5e7;color:#145228;"><i class="fa-solid fa-user-plus"></i></div>
                        <div class="qa-text">
                            <div class="qa-title">Add Employee</div>
                            <div class="qa-sub">Register a new member</div>
                        </div>
                        <i class="fa-solid fa-chevron-right qa-arrow"></i>
                    </a>
                    <a href="upload_form.php" class="qa-item">
                        <div class="qa-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fa-solid fa-file-arrow-up"></i></div>
                        <div class="qa-text">
                            <div class="qa-title">Upload Document</div>
                            <div class="qa-sub">Add new file to system</div>
                        </div>
                        <i class="fa-solid fa-chevron-right qa-arrow"></i>
                    </a>
                    <a href="employee_list.php" class="qa-item">
                        <div class="qa-icon" style="background:#fef3c7;color:#92400e;"><i class="fa-solid fa-list-ul"></i></div>
                        <div class="qa-text">
                            <div class="qa-title">View All Employees</div>
                            <div class="qa-sub">Browse full directory</div>
                        </div>
                        <i class="fa-solid fa-chevron-right qa-arrow"></i>
                    </a>
                    <a href="create_announcement.php" class="qa-item">
                        <div class="qa-icon" style="background:#ede9fe;color:#5b21b6;"><i class="fa-solid fa-bullhorn"></i></div>
                        <div class="qa-text">
                            <div class="qa-title">Post Announcement</div>
                            <div class="qa-sub">Broadcast a message</div>
                        </div>
                        <i class="fa-solid fa-chevron-right qa-arrow"></i>
                    </a>
                    <a href="document_page.php" class="qa-item">
                        <div class="qa-icon" style="background:#fce7f3;color:#9d174d;"><i class="fa-solid fa-folder-open"></i></div>
                        <div class="qa-text">
                            <div class="qa-title">Browse Documents</div>
                            <div class="qa-sub">Search &amp; manage files</div>
                        </div>
                        <i class="fa-solid fa-chevron-right qa-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<!-- Logout Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
        <h3>Sign Out?</h3>
        <p>You'll be redirected to the login page. Any unsaved work will be lost.</p>
        <div class="modal-actions">
            <button class="btn btn-cancel" onclick="closeLogoutModal()">Cancel</button>
            <button class="btn btn-danger" onclick="logoutNow()">Yes, Logout</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
/* ── LIVE CLOCK ── */
function updateClock() {
    const now = new Date();
    const opts = { weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' };
    document.getElementById('live-clock').textContent = now.toLocaleDateString('en-PH', opts);
}
updateClock();
setInterval(updateClock, 1000);

/* ── COUNT-UP ANIMATION ── */
document.querySelectorAll('.count-up').forEach(el => {
    const target = parseInt(el.dataset.target) || 0;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 40));
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current;
        if (current >= target) clearInterval(timer);
    }, 30);
});

/* ── CHARTS ── */
const months = <?= json_encode($monthlyLabels) ?>;
const uploads = <?= json_encode($monthlyData) ?>;
const statusLabels = <?= json_encode($statusLabels) ?>;
const statusData = <?= json_encode($statusData) ?>;

const COLORS = ['#2a8a4a','#3b82f6','#f59e0b','#8b5cf6','#ef4444','#06b6d4'];

let uploadChartType = 'bar';
let uploadChartInst;

function buildUploadChart(type) {
    if (uploadChartInst) uploadChartInst.destroy();
    const ctx = document.getElementById('uploadChart').getContext('2d');
    uploadChartInst = new Chart(ctx, {
        type: type,
        data: {
            labels: months.length ? months : ['No Data'],
            datasets: [{
                label: 'Uploads',
                data: uploads.length ? uploads : [0],
                backgroundColor: type === 'line' ? 'rgba(42,138,74,0.12)' : COLORS[0],
                borderColor: COLORS[0],
                borderWidth: 2,
                borderRadius: type === 'bar' ? 6 : 0,
                fill: type === 'line',
                tension: 0.4,
                pointBackgroundColor: COLORS[0],
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#8aa090', font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { color: '#8aa090', font: { size: 11 } } }
            }
        }
    });
}

function toggleChartType() {
    uploadChartType = uploadChartType === 'bar' ? 'line' : 'bar';
    buildUploadChart(uploadChartType);
}

buildUploadChart('bar');

/* Doughnut */
const dCtx = document.getElementById('statusChart').getContext('2d');
new Chart(dCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels.length ? statusLabels : ['No data'],
        datasets: [{
            data: statusData.length ? statusData : [1],
            backgroundColor: COLORS,
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '72%',
        plugins: { legend: { display: false }, tooltip: {
            callbacks: {
                label: ctx => ` ${ctx.parsed} employees`
            }
        }}
    }
});

/* Legend */
const legendEl = document.getElementById('status-legend');
statusLabels.forEach((label, i) => {
    const pct = <?= $totalEmployees ?> > 0 ? Math.round(statusData[i] / <?= $totalEmployees ?> * 100) : 0;
    legendEl.innerHTML += `
        <div class="legend-item">
            <div class="legend-dot" style="background:${COLORS[i]}"></div>
            <span class="legend-name">${label}</span>
            <span class="legend-val">${statusData[i]} <small style="color:var(--text-muted);font-weight:400;">(${pct}%)</small></span>
        </div>`;
});

/* ── SEARCH FILTER ── */
document.getElementById('search-input').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#activity-table tbody tr[data-search]').forEach(tr => {
        tr.style.display = tr.dataset.search.includes(q) ? '' : 'none';
    });
});

/* ── FILTER BUTTON ── */
let filterState = 0;
const filterTypes = ['All', 'Today', 'This Week'];
function filterActivity() {
    filterState = (filterState + 1) % filterTypes.length;
    showToast('Filter: ' + filterTypes[filterState]);
}

/* ── TOAST ── */
function showToast(msg) {
    const container = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${msg}`;
    container.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(8px)'; t.style.transition='0.3s'; setTimeout(()=>t.remove(), 300); }, 2500);
}

/* ── MODAL ── */
function openLogoutModal(e) { e.preventDefault(); document.getElementById('logoutModal').classList.add('open'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('open'); }

document.getElementById('logoutModal').addEventListener('click', function(e) {
    if (e.target === this) closeLogoutModal();
});

function logoutNow() {
    const box = document.querySelector('.modal-box');
    box.innerHTML = `
        <div class="modal-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fa-solid fa-spinner fa-spin"></i></div>
        <h3>Signing out…</h3>
        <p>See you next time, <?= htmlspecialchars($userName) ?>!</p>`;
    setTimeout(() => { window.location.href = 'logout.php'; }, 2000);
}
</script>

</body>
</html>
