<?php
session_start();
include 'config.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: Employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT id, file_name, document_type, file_path, file_type, uploaded_at
    FROM documents
    WHERE employee_id = ?
    ORDER BY uploaded_at DESC
");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$docs = [];
while ($row = $result->fetch_assoc()) {
    $docs[] = $row;
}
$total = count($docs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Documents — CENRO DENR</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --moss:      #2e5c3a;
  --fern:      #4a7c59;
  --sage:      #7aac85;
  --mist:      #c8e6cc;
  --parchment: #f0ede5;
  --bark:      #3d2e1e;
  --clay:      #8c6a4b;
  --stone:     #c4b8a8;
  --cream:     #faf7f2;
  --amber:     #c68b3a;
  --shadow:    rgba(61,46,30,0.12);
}

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--parchment);
  min-height: 100vh;
  overflow-x: hidden;
  color: var(--bark);
}

/* === BACKGROUND === */
.bg-layer {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background:
    radial-gradient(ellipse 70% 50% at 10% 20%, rgba(74,124,89,0.16) 0%, transparent 60%),
    radial-gradient(ellipse 60% 60% at 90% 80%, rgba(122,172,133,0.13) 0%, transparent 55%),
    radial-gradient(ellipse 80% 40% at 50% 110%, rgba(46,92,58,0.10) 0%, transparent 50%);
}
.bg-layer::before {
  content: "";
  position: absolute; inset: 0;
  background-image: url("./assets/images/cenro.jpeg");
  background-size: cover; background-position: center;
  opacity: 0.05;
}

.blob {
  position: fixed; border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%;
  filter: blur(52px); opacity: 0.18; pointer-events: none; z-index: 0;
  animation: morph 18s ease-in-out infinite alternate;
}
.blob-1 { width: 420px; height: 380px; background: var(--fern); top: -80px; left: -80px; animation-delay: 0s; }
.blob-2 { width: 300px; height: 260px; background: var(--sage); bottom: 60px; right: -60px; animation-delay: -7s; }

@keyframes morph {
  0%   { border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%; transform: scale(1); }
  50%  { border-radius: 40% 60% 30% 70% / 60% 40% 70% 30%; transform: scale(1.07); }
  100% { border-radius: 70% 30% 50% 50% / 30% 60% 50% 70%; transform: scale(0.97); }
}

/* === LAYOUT === */
.wrapper {
  position: relative; z-index: 1;
  max-width: 1100px; margin: 0 auto;
  padding: 36px 24px 80px;
}

/* === HEADER CARD === */
.page-header {
  background: var(--moss);
  border-radius: 24px;
  padding: 32px 36px;
  position: relative; overflow: hidden;
  box-shadow: 0 12px 36px rgba(46,92,58,0.28);
  margin-bottom: 24px;
  animation: fadeUp 0.5s ease both;
}
.page-header::before {
  content: "";
  position: absolute; top: -50px; right: -50px;
  width: 180px; height: 180px;
  background: rgba(255,255,255,0.07);
  border-radius: 40% 60% 70% 30% / 50%;
  pointer-events: none;
}
.page-header::after {
  content: "";
  position: absolute; bottom: -40px; left: -20px;
  width: 120px; height: 120px;
  background: rgba(255,255,255,0.05);
  border-radius: 60% 40% 50% 50%;
  pointer-events: none;
}

.header-inner {
  display: flex; align-items: center;
  justify-content: space-between; gap: 20px;
  flex-wrap: wrap; position: relative; z-index: 1;
}
.header-left-block { display: flex; align-items: center; gap: 16px; }

.logo-leaf {
  width: 54px; height: 54px;
  background: rgba(255,255,255,0.15);
  border: 1.5px solid rgba(255,255,255,0.25);
  border-radius: 50% 20% 50% 20%;
  display: flex; align-items: center; justify-content: center;
  color: var(--mist); font-size: 22px;
  animation: float 4s ease-in-out infinite;
  flex-shrink: 0;
}
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

.header-text-block span {
  font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
  color: rgba(255,255,255,0.60); font-weight: 500; display: block; margin-bottom: 2px;
}
.header-text-block h1 {
  font-family: 'Playfair Display', serif;
  font-size: 26px; font-weight: 600; color: #fff; line-height: 1.1;
}

.back-btn {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.14);
  border: 1.5px solid rgba(255,255,255,0.25);
  color: #fff; padding: 10px 20px; border-radius: 40px;
  font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
  text-decoration: none; transition: background 0.2s, transform 0.2s;
  position: relative; z-index: 1;
}
.back-btn:hover { background: rgba(255,255,255,0.24); transform: translateY(-2px); }

.header-sub {
  margin-top: 16px; position: relative; z-index: 1;
  font-size: 14px; color: rgba(255,255,255,0.75);
}

/* === STAT + SEARCH ROW === */
.controls-row {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 24px; flex-wrap: wrap;
  animation: fadeUp 0.5s 0.1s ease both;
}

.stat-pills { display: flex; gap: 10px; flex-wrap: wrap; }
.stat-pill {
  display: flex; align-items: center; gap: 8px;
  background: var(--cream);
  border: 1.5px solid var(--mist);
  border-radius: 40px; padding: 8px 16px;
  font-size: 13px; color: var(--bark);
  box-shadow: 0 2px 8px var(--shadow);
}
.stat-pill i { color: var(--fern); }
.stat-pill strong { font-size: 17px; font-weight: 600; color: var(--moss); }

.search-wrap {
  position: relative; flex: 1; min-width: 220px;
}
.search-wrap i {
  position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
  color: var(--clay); font-size: 14px; pointer-events: none;
}
.search-wrap input {
  width: 100%; padding: 12px 16px 12px 40px;
  background: var(--cream); border: 1.5px solid var(--stone);
  border-radius: 40px; font-family: 'DM Sans', sans-serif;
  font-size: 14px; color: var(--bark); outline: none;
  box-shadow: 0 2px 8px var(--shadow);
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-wrap input:focus {
  border-color: var(--fern); box-shadow: 0 0 0 3px rgba(74,124,89,0.18);
}
.search-wrap input::placeholder { color: var(--stone); }

/* === TABLE CARD === */
.table-card {
  background: var(--cream);
  border: 1.5px solid rgba(122,172,133,0.35);
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 8px 28px var(--shadow);
  animation: fadeUp 0.5s 0.15s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.table-wrap { overflow-x: auto; }

table {
  width: 100%; border-collapse: collapse; min-width: 760px;
}

thead { background: var(--moss); }
thead th {
  padding: 16px 20px;
  font-size: 12px; font-weight: 500;
  letter-spacing: 1.5px; text-transform: uppercase;
  color: rgba(255,255,255,0.85); text-align: left;
}

tbody tr {
  transition: background 0.2s;
  animation: fadeUp 0.3s ease both;
}
tbody tr:not(:last-child) td { border-bottom: 1px solid rgba(196,184,168,0.30); }
tbody tr:hover { background: rgba(200,230,204,0.18); }

tbody td {
  padding: 16px 20px;
  font-size: 14px; color: var(--bark);
  vertical-align: middle;
}

.file-cell { display: flex; align-items: center; gap: 10px; }
.file-thumb {
  width: 36px; height: 36px; border-radius: 10px;
  background: var(--mist); flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: var(--moss); font-size: 15px;
}
.file-name { font-weight: 500; word-break: break-word; }
.file-sub { font-size: 11px; color: var(--clay); }

/* === BADGE === */
.badge {
  display: inline-block; padding: 5px 13px;
  border-radius: 40px; font-size: 11px; font-weight: 600;
  letter-spacing: 0.5px; text-transform: uppercase;
}
.badge-pdf   { background: rgba(192,57,43,0.12); color: #c0392b; border: 1px solid rgba(192,57,43,0.25); }
.badge-doc   { background: rgba(41,128,185,0.12); color: #2980b9; border: 1px solid rgba(41,128,185,0.25); }
.badge-image { background: rgba(46,92,58,0.12);  color: var(--moss); border: 1px solid rgba(46,92,58,0.25); }
.badge-other { background: rgba(140,106,75,0.12); color: var(--clay); border: 1px solid rgba(140,106,75,0.25); }

.doc-type-tag {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(200,230,204,0.40);
  border: 1px solid rgba(122,172,133,0.35);
  border-radius: 40px; padding: 4px 12px;
  font-size: 12px; color: var(--fern); font-weight: 500;
}

.date-col { color: var(--clay); font-size: 13px; }

/* === VIEW BUTTON === */
.btn-view {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--moss); color: #fff;
  padding: 8px 16px; border-radius: 10px;
  font-family: 'DM Sans', sans-serif;
  font-size: 12px; font-weight: 500;
  text-decoration: none; white-space: nowrap;
  border: none; cursor: pointer;
  transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 3px 10px rgba(46,92,58,0.22);
}
.btn-view:hover {
  background: var(--fern); transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(46,92,58,0.30);
}

/* === MOBILE CARDS === */
.mobile-section { display: none; }

.m-card {
  background: var(--cream);
  border: 1.5px solid rgba(122,172,133,0.35);
  border-radius: 20px; padding: 18px 20px;
  margin-bottom: 14px;
  box-shadow: 0 4px 16px var(--shadow);
  border-left: 4px solid var(--moss);
  animation: fadeUp 0.3s ease both;
  transition: transform 0.25s, box-shadow 0.25s;
}
.m-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(46,92,58,0.16); }
.m-card-top { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.m-card-icon {
  width: 40px; height: 40px; border-radius: 11px;
  background: var(--mist); display: flex; align-items: center;
  justify-content: center; color: var(--moss); font-size: 16px; flex-shrink: 0;
}
.m-card-title { font-weight: 500; font-size: 14px; color: var(--bark); word-break: break-word; }
.m-card-meta { font-size: 12px; color: var(--clay); margin-top: 2px; }
.m-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 12px; flex-wrap: wrap; }

/* === EMPTY STATE === */
.empty-state {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 80px 20px; text-align: center; gap: 14px;
}
.empty-state .leaf-ico {
  font-size: 52px; color: var(--sage); opacity: 0.6;
  animation: float 3s ease-in-out infinite;
}
.empty-state h3 {
  font-family: 'Playfair Display', serif;
  font-size: 22px; color: var(--bark); font-weight: 400;
}
.empty-state p { font-size: 14px; color: var(--clay); max-width: 280px; line-height: 1.6; }

/* === FOOTER === */
.page-footer {
  text-align: center; margin-top: 32px;
  font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase;
  color: var(--clay);
}
.page-footer i { color: var(--sage); margin: 0 4px; font-size: 10px; }

/* === NO RESULTS === */
.no-results-row td {
  text-align: center; padding: 50px 20px;
  color: var(--clay); font-style: italic;
}

/* === RESPONSIVE === */
@media(max-width: 768px) {
  .table-section { display: none; }
  .mobile-section { display: block; }
  .header-inner { flex-direction: column; align-items: flex-start; }
  .controls-row { flex-direction: column; align-items: stretch; }
}
@media(max-width: 480px) {
  .page-header { border-radius: 18px; padding: 24px 20px; }
  .wrapper { padding: 24px 16px 60px; }
}
</style>
</head>
<body>

<div class="bg-layer"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrapper">

  <!-- HEADER -->
  <div class="page-header">
    <div class="header-inner">
      <div class="header-left-block">
        <div class="logo-leaf"><i class="fas fa-leaf"></i></div>
        <div class="header-text-block">
          <span>Human Resources</span>
          <h1>My Documents</h1>
        </div>
      </div>
      <a href="Employee_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
    <p class="header-sub">
      Access and manage all your uploaded CENRO-DENR employee files securely.
    </p>
  </div>

  <!-- CONTROLS -->
  <div class="controls-row">
    <div class="stat-pills">
      <div class="stat-pill">
        <i class="fas fa-folder-open"></i>
        <strong><?= $total ?></strong>
        <span>Document<?= $total !== 1 ? 's' : '' ?></span>
      </div>
    </div>
    <div class="search-wrap">
      <i class="fas fa-search"></i>
      <input type="text" id="searchInput" placeholder="Search documents…">
    </div>
  </div>

  <?php if ($total > 0): ?>

    <!-- DESKTOP TABLE -->
    <div class="table-card table-section">
      <div class="table-wrap">
        <table id="docTable">
          <thead>
            <tr>
              <th>File</th>
              <th>Type</th>
              <th>Document Category</th>
              <th>Uploaded</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($docs as $i => $row):
            $ft = strtolower($row['file_type']);
            if (str_contains($ft,'pdf'))                              { $badgeClass='badge-pdf';   $icon='fa-file-pdf';  }
            elseif (str_contains($ft,'doc')||str_contains($ft,'word')){ $badgeClass='badge-doc';   $icon='fa-file-word'; }
            elseif (str_contains($ft,'jpg')||str_contains($ft,'jpeg')||str_contains($ft,'png')){ $badgeClass='badge-image'; $icon='fa-file-image';}
            else                                                       { $badgeClass='badge-other'; $icon='fa-file-alt';  }
          ?>
            <tr class="doc-row" style="animation-delay:<?= $i*40 ?>ms">
              <td>
                <div class="file-cell">
                  <div class="file-thumb"><i class="fas <?= $icon ?>"></i></div>
                  <div>
                    <div class="file-name"><?= htmlspecialchars($row['file_name']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(strtoupper($row['file_type'])) ?></span></td>
              <td><span class="doc-type-tag"><i class="fas fa-tag"></i><?= htmlspecialchars($row['document_type']) ?></span></td>
              <td class="date-col"><?= date("M d, Y", strtotime($row['uploaded_at'])) ?><br><small><?= date("h:i A", strtotime($row['uploaded_at'])) ?></small></td>
              <td>
                <a class="btn-view" href="view.php?id=<?= $row['id'] ?>" target="_blank">
                  <i class="fas fa-eye"></i> View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr class="no-results-row" id="noResults" style="display:none">
              <td colspan="5"><i class="fas fa-search"></i>&nbsp; No documents match your search.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MOBILE CARDS -->
    <div class="mobile-section" id="mobileSection">
      <?php foreach ($docs as $i => $row):
        $ft = strtolower($row['file_type']);
        if (str_contains($ft,'pdf'))                              { $badgeClass='badge-pdf';   $icon='fa-file-pdf';  }
        elseif (str_contains($ft,'doc')||str_contains($ft,'word')){ $badgeClass='badge-doc';   $icon='fa-file-word'; }
        elseif (str_contains($ft,'jpg')||str_contains($ft,'jpeg')||str_contains($ft,'png')){ $badgeClass='badge-image'; $icon='fa-file-image';}
        else                                                       { $badgeClass='badge-other'; $icon='fa-file-alt';  }
      ?>
      <div class="m-card searchItem" style="animation-delay:<?= $i*50 ?>ms">
        <div class="m-card-top">
          <div class="m-card-icon"><i class="fas <?= $icon ?>"></i></div>
          <div>
            <div class="m-card-title"><?= htmlspecialchars($row['file_name']) ?></div>
            <div class="m-card-meta"><?= date("M d, Y h:i A", strtotime($row['uploaded_at'])) ?></div>
          </div>
        </div>
        <div class="m-row">
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(strtoupper($row['file_type'])) ?></span>
            <span class="doc-type-tag"><i class="fas fa-tag"></i><?= htmlspecialchars($row['document_type']) ?></span>
          </div>
          <a class="btn-view" href="view.php?id=<?= $row['id'] ?>" target="_blank">
            <i class="fas fa-eye"></i> View
          </a>
        </div>
      </div>
      <?php endforeach; ?>
      <div id="mobileNoResults" style="display:none; text-align:center; padding:40px 20px; color:var(--clay); font-style:italic;">
        <i class="fas fa-search"></i>&nbsp; No documents match your search.
      </div>
    </div>

  <?php else: ?>

    <div class="table-card">
      <div class="empty-state">
        <i class="fas fa-leaf leaf-ico"></i>
        <h3>No Documents Yet</h3>
        <p>You currently have no uploaded files in your account. Contact HR for assistance.</p>
      </div>
    </div>

  <?php endif; ?>

  <div class="page-footer">
    CENRO-DENR <i class="fas fa-leaf"></i> Manolo Fortich, Bukidnon
  </div>

</div>

<script>
document.getElementById('searchInput').addEventListener('input', function(){
  const q = this.value.toLowerCase().trim();

  // Desktop
  let visible = 0;
  document.querySelectorAll('#docTable tbody .doc-row').forEach(row => {
    const match = row.innerText.toLowerCase().includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  const noRes = document.getElementById('noResults');
  if (noRes) noRes.style.display = visible === 0 ? '' : 'none';

  // Mobile
  let mVisible = 0;
  document.querySelectorAll('.searchItem').forEach(card => {
    const match = card.innerText.toLowerCase().includes(q);
    card.style.display = match ? '' : 'none';
    if (match) mVisible++;
  });
  const mNo = document.getElementById('mobileNoResults');
  if (mNo) mNo.style.display = mVisible === 0 ? '' : 'none';
});
</script>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>