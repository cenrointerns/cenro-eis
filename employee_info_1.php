<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) die("Employee not found.");

$image_path = 'assets/image/employee/' . $employee['image'];
if (!file_exists($image_path) || empty($employee['image'])) {
    $image_path = 'assets/image/employee/default.png';
}

$age = 'N/A';
if (!empty($employee['date_of_birth'])) {
    $dob = new DateTime($employee['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
}

$service = 'N/A';
$service_years = 0;
if (!empty($employee['date_of_appointment'])) {
    $start = new DateTime($employee['date_of_appointment']);
    $today = new DateTime();
    $diff = $today->diff($start);
    $service_years = $diff->y;
    $service = $diff->y . " yrs, " . $diff->m . " mos, " . $diff->d . " days";
}

// FIXED: Handle monthly salary properly - strip commas and convert to number
$salary_raw_value = isset($employee['monthly_salary']) ? $employee['monthly_salary'] : '0';
// Remove commas and any non-numeric characters except decimal point
$salary_numeric = preg_replace('/[^0-9.]/', '', $salary_raw_value);
$salary_float = floatval($salary_numeric);
$salary_display = $salary_float > 0 ? '₱' . number_format($salary_float, 2) : 'N/A';

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($employee['name']) ?> — Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── RESET ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── TOKENS ── */
:root {
  --green-deep:    #1a4d1a;
  --green-forest:  #2d6a2d;
  --green-moss:    #3d8b3d;
  --green-sage:    #6aaa6a;
  --green-mint:    #a8d5a8;
  --green-pale:    #d4ecd4;
  --green-light:   #eaf6ea;
  --green-white:   #f5faf5;
  --bark:          #5a3a1a;
  --earth:         #8b6946;
  --stone:         #7a8f74;
  --gold:          #c9a83a;
  --gold-light:    #f5ecc4;
  --shadow:        rgba(26, 77, 26, 0.1);

  --font-main: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 20px;
  --radius-xl: 28px;
  
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── BASE ── */
body {
  font-family: var(--font-main);
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--bark);
  background: linear-gradient(135deg, var(--green-white) 0%, var(--green-pale) 100%);
  min-height: 100vh;
  padding: 2rem 1.25rem 3rem;
}

/* ── UTIL ── */
.page { max-width: 1000px; margin: 0 auto; }

/* ── ANIMATIONS ── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes slideIn {
  from { opacity: 0; transform: translateX(-20px); }
  to   { opacity: 1; transform: translateX(0); }
}

.page { animation: fadeUp 0.6s ease both; }

/* ── TYPOGRAPHY UNIFORMITY ── */
h1, h2, h3, .hero-name, .salary-amount, .stat-val, .comp-tile-val {
  font-family: var(--font-main);
  font-weight: 700;
  letter-spacing: -0.02em;
}

.hero-name {
  font-size: 2.2rem;
  font-weight: 800;
  color: var(--green-deep);
  line-height: 1.2;
  margin-bottom: 1rem;
}

h2 {
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  color: var(--green-forest);
}

/* ── TOP NAV ── */
.topnav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  border-radius: 40px;
  font-family: var(--font-main);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: var(--transition);
  cursor: pointer;
  border: none;
}

.btn svg { 
  width: 16px; 
  height: 16px; 
  flex-shrink: 0;
  transition: transform 0.2s;
}

.btn-primary { 
  background: var(--green-forest); 
  color: white;
  box-shadow: 0 2px 8px var(--shadow);
}

.btn-primary:hover { 
  background: var(--green-deep); 
  transform: translateY(-2px);
  box-shadow: 0 4px 12px var(--shadow);
}

.btn-primary:hover svg {
  transform: translateX(-3px);
}

.btn-outline { 
  background: transparent; 
  color: var(--green-forest); 
  border: 2px solid var(--green-forest);
}

.btn-outline:hover { 
  background: var(--green-forest); 
  color: white;
  transform: translateY(-2px);
}

/* ── HERO CARD ── */
.hero {
  background: white;
  border-radius: var(--radius-xl);
  padding: 2rem 2.5rem;
  display: flex;
  gap: 2.5rem;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px var(--shadow);
  border: 1px solid var(--green-mint);
}

.hero::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 6px;
  background: linear-gradient(90deg, var(--green-deep), var(--green-sage), var(--gold));
}

/* ── AVATAR ── */
.avatar-wrap { 
  position: relative; 
  flex-shrink: 0; 
}

.avatar-ring {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  padding: 4px;
  background: linear-gradient(135deg, var(--green-moss), var(--green-sage));
  box-shadow: 0 8px 24px rgba(45, 106, 45, 0.25);
}

.avatar-ring img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid white;
  display: block;
}

.status-dot {
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 3px solid white;
  background: #4caf50;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-dot.inactive { 
  background: var(--stone); 
}

/* ── HERO BODY ── */
.hero-body { 
  flex: 1; 
}

.hero-section { 
  font-size: 12px; 
  font-weight: 600; 
  letter-spacing: 1px; 
  text-transform: uppercase; 
  color: var(--green-moss); 
  margin-bottom: 8px; 
}

.badge-row { 
  display: flex; 
  flex-wrap: wrap; 
  gap: 10px; 
  margin-bottom: 1.5rem; 
}

.badge {
  padding: 5px 14px;
  border-radius: 20px;
  font-family: var(--font-main);
  font-size: 12px;
  font-weight: 600;
  transition: var(--transition);
}

.badge-green { 
  background: var(--green-light); 
  color: var(--green-deep); 
  border: 1px solid var(--green-mint);
}

.badge-gold { 
  background: var(--gold-light); 
  color: #8b6914; 
  border: 1px solid #e6d499;
}

.badge-bark {  
  background: #f5efe5; 
  color: var(--bark); 
  border: 1px solid #ddd0bc;
}

/* ── QUICK STATS ── */
.stats-row { 
  display: grid; 
  grid-template-columns: repeat(3, 1fr); 
  gap: 12px; 
}

.stat-pill {
  background: var(--green-light);
  border: 1px solid var(--green-mint);
  border-radius: var(--radius-md);
  padding: 12px 16px;
  text-align: center;
  transition: var(--transition);
  position: relative;
}

.stat-pill:hover { 
  transform: translateY(-4px); 
  box-shadow: 0 6px 16px var(--shadow);
  border-color: var(--green-sage);
}

.stat-val { 
  font-size: 1.6rem; 
  font-weight: 800; 
  color: var(--green-deep); 
  line-height: 1.2;
}

.stat-lbl { 
  font-size: 11px; 
  font-weight: 600; 
  text-transform: uppercase; 
  letter-spacing: 0.5px; 
  color: var(--earth); 
  margin-top: 4px; 
}

/* ── TOOLTIP ── */
.tip { 
  position: relative; 
  display: block; 
}

.tip-box {
  display: none;
  position: absolute;
  bottom: calc(100% + 10px);
  left: 50%;
  transform: translateX(-50%);
  background: var(--green-deep);
  color: white;
  font-family: var(--font-main);
  font-size: 12px;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 8px;
  white-space: nowrap;
  z-index: 20;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.tip-box::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: var(--green-deep);
}

.stat-pill:hover .tip-box { 
  display: block; 
}

/* ── SECTION HEADER ── */
.sec-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 1rem;
}

.sec-head svg { 
  color: var(--green-moss); 
  width: 20px; 
  height: 20px; 
  flex-shrink: 0; 
}

.sec-head h2 { 
  font-size: 1rem; 
  font-weight: 700; 
  color: var(--green-deep);
  margin: 0;
}

.sec-line { 
  flex: 1; 
  height: 2px; 
  background: linear-gradient(90deg, var(--green-mint), transparent);
}

/* ── SERVICE BAR ── */
.bar-card {
  background: white;
  border-radius: var(--radius-lg);
  border: 1px solid var(--green-mint);
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px var(--shadow);
  transition: var(--transition);
}

.bar-card:hover {
  box-shadow: 0 4px 16px var(--shadow);
  transform: translateY(-2px);
}

.bar-track { 
  height: 10px; 
  background: var(--green-pale); 
  border-radius: 10px; 
  overflow: hidden; 
  margin-top: 10px; 
}

.bar-fill { 
  height: 100%; 
  background: linear-gradient(90deg, var(--green-forest), var(--green-sage)); 
  border-radius: 10px; 
  width: 0; 
  transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.bar-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.bar-meta { 
  display: flex; 
  justify-content: space-between; 
  font-size: 11px; 
  font-weight: 500;
  color: var(--earth); 
  margin-top: 8px; 
}

/* ── TWO PANELS ── */
.panels { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 1.5rem; 
  margin-bottom: 1.5rem; 
}

.panel {
  background: white;
  border-radius: var(--radius-lg);
  border: 1px solid var(--green-mint);
  padding: 1.5rem;
  box-shadow: 0 2px 8px var(--shadow);
  transition: var(--transition);
}

.panel:hover {
  box-shadow: 0 4px 16px var(--shadow);
  transform: translateY(-2px);
}

/* ── INFO ROWS ── */
.info-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid var(--green-pale);
}

.info-row:last-child { 
  border-bottom: none; 
}

.info-icon {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  background: var(--green-light);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-icon svg { 
  width: 18px; 
  height: 18px; 
  color: var(--green-forest); 
}

.i-lbl { 
  font-size: 11px; 
  font-weight: 600; 
  text-transform: uppercase; 
  letter-spacing: 0.5px; 
  color: var(--earth); 
}

.i-val { 
  font-size: 14px; 
  font-weight: 600; 
  color: var(--bark); 
  margin-top: 2px;
}

/* ── SALARY DISPLAY ── */
.salary-box {
  text-align: center;
  padding: 1rem 0;
  background: linear-gradient(135deg, var(--green-light), var(--green-white));
  border-radius: var(--radius-md);
  margin-bottom: 1.25rem;
}

.salary-amount {
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--green-deep);
  line-height: 1;
  margin-bottom: 6px;
}

.salary-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--earth);
}

.salary-divider { 
  width: 50px; 
  height: 3px; 
  background: linear-gradient(90deg, var(--green-moss), var(--green-sage));
  border-radius: 3px; 
  margin: 10px auto; 
}

.comp-grid { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 12px; 
  margin-bottom: 1.25rem;
}

.comp-tile {
  background: var(--green-light);
  border-radius: var(--radius-md);
  padding: 12px;
  text-align: center;
  transition: var(--transition);
}

.comp-tile:hover {
  transform: translateY(-2px);
  background: var(--green-mint);
}

.comp-tile-val { 
  font-size: 1.4rem; 
  font-weight: 800; 
  color: var(--green-deep); 
}

.comp-tile-lbl { 
  font-size: 10px; 
  font-weight: 600; 
  text-transform: uppercase; 
  letter-spacing: 0.5px; 
  color: var(--earth); 
  margin-top: 4px; 
}

.promo-strip {
  background: linear-gradient(135deg, var(--gold-light), #fff5e6);
  border-radius: var(--radius-md);
  padding: 12px 16px;
  border-left: 4px solid var(--gold);
}

.promo-strip .i-lbl { 
  margin-bottom: 4px; 
}

/* ── DETAILS GRID ── */
.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
  margin-top: 1rem;
}

.d-card {
  background: white;
  border: 1px solid var(--green-mint);
  border-radius: var(--radius-md);
  padding: 1rem 1.25rem;
  position: relative;
  overflow: hidden;
  transition: var(--transition);
  animation: slideIn 0.4s ease both;
}

.d-card:hover { 
  border-color: var(--green-sage); 
  transform: translateY(-4px); 
  box-shadow: 0 6px 16px var(--shadow);
}

.d-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: var(--green-mint);
  transition: var(--transition);
}

.d-card:hover::before { 
  background: var(--green-forest); 
}

/* Animation delays */
.d-card:nth-child(1) { animation-delay: 0.02s; }
.d-card:nth-child(2) { animation-delay: 0.04s; }
.d-card:nth-child(3) { animation-delay: 0.06s; }
.d-card:nth-child(4) { animation-delay: 0.08s; }
.d-card:nth-child(5) { animation-delay: 0.10s; }
.d-card:nth-child(6) { animation-delay: 0.12s; }
.d-card:nth-child(7) { animation-delay: 0.14s; }
.d-card:nth-child(8) { animation-delay: 0.16s; }
.d-card:nth-child(9) { animation-delay: 0.18s; }

.d-lbl { 
  font-size: 10px; 
  font-weight: 600; 
  text-transform: uppercase; 
  letter-spacing: 0.7px; 
  color: var(--earth); 
  margin-bottom: 6px; 
}

.d-val { 
  font-size: 14px; 
  font-weight: 600; 
  color: var(--bark); 
}

/* ── TOAST ── */
#toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%) translateY(80px);
  background: var(--green-deep);
  color: white;
  font-family: var(--font-main);
  font-size: 13px;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: 40px;
  transition: transform 0.3s ease;
  z-index: 100;
  pointer-events: none;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

#toast.show { 
  transform: translateX(-50%) translateY(0); 
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  body {
    padding: 1rem;
  }
  
  .hero { 
    flex-direction: column; 
    align-items: center; 
    text-align: center; 
    padding: 1.5rem; 
  }
  
  .badge-row, 
  .stats-row { 
    justify-content: center; 
  }
  
  .panels { 
    grid-template-columns: 1fr; 
    gap: 1rem;
  }
  
  .hero-name {
    font-size: 1.8rem;
  }
  
  .avatar-ring {
    width: 120px;
    height: 120px;
  }
  
  .stat-val {
    font-size: 1.3rem;
  }
  
  .salary-amount {
    font-size: 1.8rem;
  }
}

@media (max-width: 480px) {
  .topnav {
    flex-direction: column;
    align-items: stretch;
  }
  
  .btn {
    justify-content: center;
  }
  
  .stats-row {
    gap: 8px;
  }
  
  .stat-pill {
    padding: 8px 12px;
  }
  
  .stat-val {
    font-size: 1.1rem;
  }
  
  .salary-amount {
    font-size: 1.5rem;
  }
}

/* ── COPYABLE STYLE ── */
.copyable {
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.copyable:hover {
  color: var(--green-forest);
  transform: scale(1.02);
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

::-webkit-scrollbar-track {
  background: var(--green-pale);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: var(--green-sage);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--green-forest);
}
</style>
</head>
<body>
<div class="page">

  <!-- TOP NAV -->
  <div class="topnav">
    <a href="create_employee.php" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M19 12H5M12 5l-7 7 7 7"/>
      </svg>
      Back to List
    </a>
    <a href="employee_document_1.php?employee_id=<?= $employee_id ?>" class="btn btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
      </svg>
      View Documents
    </a>
  </div>

  <!-- HERO -->
  <div class="hero">
    <div class="avatar-wrap">
      <div class="avatar-ring">
        <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($employee['name']) ?>">
      </div>
      <div class="status-dot <?= strtolower($employee['status'] ?? '') === 'active' ? '' : 'inactive' ?>"></div>
    </div>
    <div class="hero-body">
      <div class="hero-section"><?= htmlspecialchars($employee['assigned_section'] ?? 'DENR Employee') ?></div>
      <h1 class="hero-name"><?= htmlspecialchars($employee['name']) ?></h1>
      <div class="badge-row">
        <span class="badge badge-green"><?= htmlspecialchars($employee['status'] ?? 'N/A') ?></span>
        <span class="badge badge-gold"><?= htmlspecialchars($employee['position_title'] ?? 'N/A') ?></span>
        <?php if (!empty($employee['civil_service_eligibility'])): ?>
        <span class="badge badge-bark">CS: <?= htmlspecialchars($employee['civil_service_eligibility']) ?></span>
        <?php endif; ?>
      </div>
      <div class="stats-row">
        <div class="stat-pill">
          <div class="tip">
            <div class="stat-val"><?= htmlspecialchars($age) ?></div>
            <div class="stat-lbl">Age</div>
            <div class="tip-box">Born <?= htmlspecialchars($employee['date_of_birth'] ?? 'N/A') ?></div>
          </div>
        </div>
        <div class="stat-pill">
          <div class="tip">
            <div class="stat-val">SG-<?= htmlspecialchars($employee['salary_grade'] ?? 'N/A') ?></div>
            <div class="stat-lbl">Salary Grade</div>
            <div class="tip-box">Step Increment: <?= htmlspecialchars($employee['step_increment'] ?? 'N/A') ?></div>
          </div>
        </div>
        <div class="stat-pill">
          <div class="tip">
            <div class="stat-val"><?= $service_years ?>yr</div>
            <div class="stat-lbl">Service</div>
            <div class="tip-box"><?= htmlspecialchars($service) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SERVICE BAR -->
  <div class="bar-card">
    <div class="sec-head">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
      <h2>Length of Service</h2>
      <div class="sec-line"></div>
      <span style="font-size:12px; font-weight:600; color:var(--earth)"><?= htmlspecialchars($service) ?></span>
    </div>
    <div class="bar-track">
      <div class="bar-fill" id="svcBar" data-y="<?= $service_years ?>"></div>
    </div>
    <div class="bar-meta">
      <span>📅 Appointed: <?= htmlspecialchars($employee['date_of_appointment'] ?? 'N/A') ?></span>
      <span>🌟 <?= $service_years ?> / 35 years</span>
    </div>
  </div>

  <!-- PANELS -->
  <div class="panels">

    <!-- PERSONAL INFO -->
    <div class="panel">
      <div class="sec-head">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        <h2>Personal Information</h2>
        <div class="sec-line"></div>
      </div>
      <?php
      $infoRows = [
        ['Gender',          $employee['gender'] ?? 'N/A',           'M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zM9 9h6M9 15h6'],
        ['Date of Birth',   $employee['date_of_birth'] ?? 'N/A',    'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Assigned Section', $employee['place_of_assignment'] ?? $employee['assigned_section'] ?? 'N/A', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
        ['NOSCA Item No.',  $employee['nosca_item_number'] ?? 'N/A', 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14'],
        ['Education',       $employee['education'] ?? 'N/A',        'M12 14l9-5-9-5-9 5 9 5zm0 0v6'],
      ];
      foreach ($infoRows as $ir): ?>
      <div class="info-row">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="<?= $ir[2] ?>"/>
          </svg>
        </div>
        <div style="flex:1">
          <div class="i-lbl"><?= $ir[0] ?></div>
          <div class="i-val <?= $ir[0]==='NOSCA Item No.' ? 'copyable' : '' ?>"
               <?= $ir[0]==='NOSCA Item No.' ? 'data-copy="'.htmlspecialchars($ir[1]).'" title="Click to copy"' : '' ?>>
            <?= htmlspecialchars($ir[1]) ?>
            <?= $ir[0]==='NOSCA Item No.' ? '<span style="margin-left: 6px; font-size: 11px;">📋</span>' : '' ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- MONTHLY SALARY SECTION - FIXED -->
    <div class="panel">
      <div class="sec-head">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
        </svg>
        <h2>Monthly Salary</h2>
        <div class="sec-line"></div>
      </div>

      <div class="salary-box">
        <div class="salary-amount"><?= $salary_display ?></div>
        <div class="salary-divider"></div>
        <div class="salary-label">Monthly Basic Salary</div>
      </div>

      <div class="comp-grid">
        <div class="comp-tile">
          <div class="comp-tile-val">SG-<?= htmlspecialchars($employee['salary_grade'] ?? 'N/A') ?></div>
          <div class="comp-tile-lbl">Salary Grade</div>
        </div>
        <div class="comp-tile">
          <div class="comp-tile-val"><?= htmlspecialchars($employee['step_increment'] ?? 'N/A') ?></div>
          <div class="comp-tile-lbl">Step Increment</div>
        </div>
      </div>

      <div class="promo-strip">
        <div class="i-lbl">📈 Date of Last Promotion</div>
        <div class="i-val"><?= htmlspecialchars($employee['date_of_last_promotion'] ?? 'N/A') ?></div>
      </div>
    </div>

  </div>

  <!-- EMPLOYMENT DETAILS -->
  <div style="margin-bottom: 1.5rem">
    <div class="sec-head">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="2" y="3" width="20" height="14" rx="2"/>
        <line x1="8" y1="21" x2="16" y2="21"/>
        <line x1="12" y1="17" x2="12" y2="21"/>
      </svg>
      <h2>Employment Details</h2>
      <div class="sec-line"></div>
    </div>
    <div class="details-grid">
      <?php
      $dfields = [
        ['Civil Service Eligibility', $employee['civil_service_eligibility'] ?? 'N/A'],
        ['Position Title',            $employee['position_title'] ?? 'N/A'],
        ['Education',                 $employee['education'] ?? 'N/A'],
        ['Monthly Salary',            $salary_display],
        ['Salary Grade',              'SG-' . ($employee['salary_grade'] ?? 'N/A')],
        ['Date of Appointment',       $employee['date_of_appointment'] ?? 'N/A'],
        ['Length of Service',         $service],
        ['Date of Last Promotion',    $employee['date_of_last_promotion'] ?? 'N/A'],
        ['Step Increment',            $employee['step_increment'] ?? 'N/A'],
      ];
      foreach ($dfields as $df): ?>
      <div class="d-card">
        <div class="d-lbl"><?= htmlspecialchars($df[0]) ?></div>
        <div class="d-val"><?= htmlspecialchars($df[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div><!-- .page -->

<div id="toast">📋 Copied to clipboard!</div>

<script>
window.addEventListener('load', () => {
  const bar = document.getElementById('svcBar');
  if (bar) {
    const y = parseInt(bar.dataset.y, 10) || 0;
    const percentage = Math.min((y / 35) * 100, 100);
    requestAnimationFrame(() => { 
      bar.style.width = percentage + '%'; 
    });
  }
});

document.querySelectorAll('.copyable').forEach(el => {
  el.addEventListener('click', () => {
    const v = el.dataset.copy;
    if (!v) return;
    navigator.clipboard.writeText(v).then(() => {
      const t = document.getElementById('toast');
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2200);
    });
  });
});
</script>
</body>
</html>