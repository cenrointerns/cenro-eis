<?php
session_start();
include 'config.php';

if(!isset($_SESSION['employee_id'])){
    header("Location: Employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if(!$employee){ die("Employee not found!"); }

$image_path = 'assets/image/employee/default.png';
if (!empty($employee['image'])) {
    $temp_path = 'assets/image/employee/' . $employee['image'];
    if (file_exists($temp_path)) { $image_path = $temp_path; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DENR Employee Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --forest:    #0b5d3b;
    --forest-dk: #084529;
    --forest-lt: #e8f5ee;
    --leaf:      #22c55e;
    --bark:      #5c4a2a;
    --cream:     #faf9f6;
    --paper:     #ffffff;
    --mist:      #f0f4f2;
    --stone:     #8a9a90;
    --pebble:    #c8d6cd;
    --ink:       #1a2820;
    --charcoal:  #3d4f45;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-xl: 32px;
    --sidebar-w: 270px;
    --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--ink);
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
}

/* ── ORGANIC BACKGROUND TEXTURE ── */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        radial-gradient(ellipse 80% 60% at -10% 20%, rgba(11,93,59,0.06), transparent),
        radial-gradient(ellipse 60% 80% at 110% 80%, rgba(34,197,94,0.04), transparent),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230b5d3b' fill-opacity='0.015'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
}

/* ── SIDEBAR ── */
.sidebar {
    width: var(--sidebar-w);
    background: var(--forest);
    position: fixed;
    top: 0; left: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    z-index: 900;
    transition: left var(--transition);
    overflow: hidden;
}

/* leaf-vein texture overlay */
.sidebar::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        repeating-linear-gradient(
            135deg,
            transparent,
            transparent 40px,
            rgba(255,255,255,0.015) 40px,
            rgba(255,255,255,0.015) 80px
        );
    pointer-events: none;
}

.sidebar-logo {
    padding: 32px 24px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-logo img {
    width: 72px; height: 72px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.25);
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.sidebar-logo-text {
    text-align: center;
}

.sidebar-logo-text .dept {
    font-family: 'Fraunces', serif;
    font-size: 13px;
    font-weight: 600;
    color: #a8d4bc;
    letter-spacing: 0.5px;
    line-height: 1.4;
}

.sidebar-logo-text .portal {
    font-size: 10px;
    color: rgba(255,255,255,0.45);
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-top: 3px;
}

.sidebar-nav {
    flex: 1;
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-item {
    text-decoration: none;
    color: rgba(255,255,255,0.7);
    padding: 13px 16px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
    transition: all var(--transition);
    position: relative;
}

.nav-item:hover {
    color: white;
    background: rgba(255,255,255,0.1);
}

.nav-item.active {
    color: white;
    background: rgba(255,255,255,0.15);
    box-shadow: inset 3px 0 0 var(--leaf);
}

.nav-item .nav-icon {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    background: rgba(255,255,255,0.08);
    font-size: 16px;
    flex-shrink: 0;
}

.nav-item.active .nav-icon {
    background: rgba(34,197,94,0.2);
}

.nav-divider {
    height: 1px;
    background: rgba(255,255,255,0.08);
    margin: 8px 0;
}

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.nav-item.logout {
    color: rgba(255,100,100,0.7);
}
.nav-item.logout:hover {
    color: #ff8080;
    background: rgba(255,100,100,0.1);
}

/* ── MOBILE TOPBAR ── */
.mobile-topbar {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%;
    background: var(--forest);
    padding: 0 20px;
    height: 60px;
    align-items: center;
    justify-content: space-between;
    z-index: 1100;
    box-shadow: 0 2px 20px rgba(0,0,0,0.2);
}

.mobile-topbar .topbar-title {
    font-family: 'Fraunces', serif;
    font-size: 15px;
    color: white;
    font-weight: 600;
}

.menu-btn {
    width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    border-radius: 8px;
    color: white;
    background: rgba(255,255,255,0.1);
    border: none;
    font-size: 20px;
    transition: background var(--transition);
}
.menu-btn:hover { background: rgba(255,255,255,0.2); }

/* ── SIDEBAR OVERLAY ── */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 850;
    backdrop-filter: blur(4px);
}
.sidebar-overlay.active { display: block; }

/* ── MAIN CONTENT ── */
.main {
    margin-left: var(--sidebar-w);
    width: calc(100% - var(--sidebar-w));
    padding: 48px 48px 60px;
    position: relative;
    z-index: 1;
}

/* ── PAGE HEADER ── */
.page-header {
    margin-bottom: 36px;
}

.page-header .eyebrow {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--stone);
    margin-bottom: 8px;
}

.page-header h1 {
    font-family: 'Fraunces', serif;
    font-size: 40px;
    font-weight: 600;
    color: var(--forest);
    line-height: 1.1;
}

.page-header .subtitle {
    color: var(--stone);
    font-size: 14px;
    margin-top: 6px;
}

/* ── WELCOME STRIP ── */
.welcome-strip {
    background: var(--forest);
    border-radius: var(--radius-lg);
    padding: 28px 36px;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    position: relative;
    overflow: hidden;
}

.welcome-strip::before {
    content: '';
    position: absolute;
    right: -40px; top: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.welcome-strip::after {
    content: '';
    position: absolute;
    right: 60px; bottom: -80px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(34,197,94,0.08);
}

.welcome-text {
    position: relative; z-index: 1;
}

.welcome-text h2 {
    font-family: 'Fraunces', serif;
    font-size: 26px;
    font-weight: 600;
    color: white;
    margin-bottom: 4px;
}

.welcome-text p {
    color: rgba(255,255,255,0.55);
    font-size: 14px;
}

.welcome-badge {
    position: relative; z-index: 1;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: var(--radius-sm);
    padding: 10px 18px;
    color: rgba(255,255,255,0.8);
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--leaf);
    box-shadow: 0 0 0 3px rgba(34,197,94,0.25);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(34,197,94,0.25); }
    50%       { box-shadow: 0 0 0 6px rgba(34,197,94,0.1); }
}

/* ── PROFILE SECTION ── */
.profile-section {
    background: var(--paper);
    border-radius: var(--radius-xl);
    border: 1px solid var(--pebble);
    overflow: hidden;
}

.profile-top {
    padding: 40px 44px;
    display: flex;
    align-items: flex-start;
    gap: 44px;
    border-bottom: 1px solid var(--mist);
    position: relative;
}

/* Leaf decoration */
.profile-top::before {
    content: '🌿';
    position: absolute;
    right: 44px; top: 40px;
    font-size: 48px;
    opacity: 0.07;
    transform: rotate(15deg);
    pointer-events: none;
}

.avatar-wrap {
    flex-shrink: 0;
    position: relative;
}

.avatar-wrap img {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--forest-lt);
    outline: 2px solid var(--pebble);
    display: block;
}

.avatar-ring {
    position: absolute;
    inset: -10px;
    border-radius: 50%;
    border: 1px dashed var(--pebble);
    animation: spin-slow 20s linear infinite;
}

@keyframes spin-slow {
    to { transform: rotate(360deg); }
}

.profile-info {
    flex: 1;
    padding-top: 8px;
}

.profile-info .emp-id {
    font-size: 12px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--stone);
    margin-bottom: 8px;
}

.profile-info h2 {
    font-family: 'Fraunces', serif;
    font-size: 34px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1.15;
    margin-bottom: 16px;
}

.quick-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.tag {
    padding: 5px 14px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.tag-forest {
    background: var(--forest-lt);
    color: var(--forest);
}

.tag-stone {
    background: var(--mist);
    color: var(--charcoal);
}

.tag-active {
    background: #e6faf0;
    color: #166534;
}

.tag-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.profile-meta {
    display: flex;
    gap: 28px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.meta-item .meta-label {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--stone);
}

.meta-item .meta-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--charcoal);
}

/* ── DETAILS GRID ── */
.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.detail-item {
    padding: 22px 44px;
    border-bottom: 1px solid var(--mist);
    border-right: 1px solid var(--mist);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    transition: background var(--transition);
}

.detail-item:hover {
    background: var(--cream);
}

.detail-item:nth-child(even) {
    border-right: none;
}

.detail-item:nth-last-child(-n+2) {
    border-bottom: none;
}

.detail-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--stone);
    font-weight: 500;
}

.detail-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: var(--mist);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.detail-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--charcoal);
    text-align: right;
}

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .mobile-topbar { display: flex; }
    .sidebar { left: calc(var(--sidebar-w) * -1); }
    .sidebar.active { left: 0; }
    .sidebar-overlay { display: none; }
    .sidebar-overlay.active { display: block; }
    .main { margin-left: 0; width: 100%; padding: 76px 20px 40px; }
    .profile-top { flex-direction: column; padding: 28px 24px; gap: 24px; align-items: center; text-align: center; }
    .profile-info h2 { font-size: 26px; }
    .quick-tags { justify-content: center; }
    .profile-meta { justify-content: center; }
    .details-grid { grid-template-columns: 1fr; }
    .detail-item { padding: 18px 24px; border-right: none !important; }
    .detail-item:nth-last-child(-n+2) { border-bottom: 1px solid var(--mist); }
    .detail-item:last-child { border-bottom: none; }
    .welcome-strip { padding: 22px 24px; flex-direction: column; align-items: flex-start; gap: 14px; }
    .welcome-badge { align-self: flex-start; }
    .page-header h1 { font-size: 28px; }
}

@media (max-width: 480px) {
    .main { padding: 70px 16px 32px; }
    .avatar-wrap img { width: 120px; height: 120px; }
}
</style>
</head>
<body>

<!-- MOBILE TOPBAR -->
<div class="mobile-topbar">
    <span class="topbar-title">DENR Portal</span>
    <button class="menu-btn" onclick="toggleMenu()" aria-label="Toggle menu">☰</button>
</div>

<!-- OVERLAY -->
<div class="sidebar-overlay" id="overlay" onclick="closeMenu()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <img src="assets/images/DENR_logo.png" alt="DENR Logo">
        <div class="sidebar-logo-text">
            <div class="dept">DENR–CENRO</div>
            <div class="portal">Employee Portal</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="#" class="nav-item active">
            <span class="nav-icon">🏠</span>
            Dashboard
        </a>
        <a href="Employee_docs_view.php" class="nav-item">
            <span class="nav-icon">📁</span>
            Documents
        </a>
        <a href="announcement_fetcher.php" class="nav-item">
            <span class="nav-icon">📰</span>
            News & Updates
        </a>
    </nav>

    <div class="nav-divider"></div>

    <div class="sidebar-footer">
        <a href="Employee_logout.php" class="nav-item logout">
            <span class="nav-icon">🚪</span>
            Sign Out
        </a>
    </div>

</aside>

<!-- MAIN -->
<main class="main">

    <div class="page-header">
        <div class="eyebrow">Department of Environment and Natural Resources</div>
        <h1>My Dashboard</h1>
        <p class="subtitle">Manage your profile and documents</p>
    </div>

    <div class="welcome-strip">
        <div class="welcome-text">
            <h2>Good day, <?php echo htmlspecialchars($employee['name']); ?> 🌿</h2>
            <p>Here's an overview of your employee profile</p>
        </div>
        <div class="welcome-badge">
            <span class="status-dot"></span>
            <?php echo htmlspecialchars($employee['status']); ?>
        </div>
    </div>

    <div class="profile-section">

        <div class="profile-top">
            <div class="avatar-wrap">
                <div class="avatar-ring"></div>
                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Profile photo">
            </div>

            <div class="profile-info">
                <div class="emp-id">Employee ID · <?php echo $employee['employee_id']; ?></div>
                <h2><?php echo htmlspecialchars($employee['name']); ?></h2>

                <div class="quick-tags">
                    <span class="tag tag-forest">
                        <span class="tag-dot"></span>
                        <?php echo htmlspecialchars($employee['position_title']); ?>
                    </span>
                    <span class="tag tag-stone">
                        📍 <?php echo htmlspecialchars($employee['assigned_section']); ?>
                    </span>
                    <span class="tag tag-active">
                        <span class="tag-dot"></span>
                        <?php echo htmlspecialchars($employee['status']); ?>
                    </span>
                </div>

                <div class="profile-meta">
                    <div class="meta-item">
                        <span class="meta-label">Salary Grade</span>
                        <span class="meta-value">SG-<?php echo $employee['salary_grade']; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Length of Service</span>
                        <span class="meta-value"><?php echo htmlspecialchars($employee['length_of_service']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Appointed</span>
                        <span class="meta-value"><?php echo htmlspecialchars($employee['date_of_appointment']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="details-grid">

            <div class="detail-item">
                <div class="detail-label">
                    <div class="detail-icon">🎂</div>Age
                </div>
                <div class="detail-value"><?php echo $employee['age']; ?> years</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">
                    <div class="detail-icon">👤</div>Gender
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['gender']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">
                    <div class="detail-icon">📅</div>Date of Birth
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['date_of_birth']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">
                    <div class="detail-icon">🏷️</div>NOSCA Item No.
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['nosca_item_number']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">
                    <div class="detail-icon">🎓</div>Education
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['education']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">
                    <div class="detail-icon">📜</div>Civil Service Eligibility
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['civil_service_eligibility']); ?></div>
            </div>

        </div>
    </div>

</main>

<script>
function toggleMenu() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}
function closeMenu() {
    document.getElementById('sidebar').classList.remove('active');
    document.getElementById('overlay').classList.remove('active');
}
</script>
</body>
</html>