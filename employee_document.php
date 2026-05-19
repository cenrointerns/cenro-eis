<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

$stmt = $conn->prepare("SELECT name FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
if (!$employee) die("Employee not found.");

$stmt2 = $conn->prepare("
    SELECT id, file_name, file_path, document_type, file_type, uploaded_at
    FROM documents
    WHERE employee_id = ?
    ORDER BY document_type, uploaded_at DESC
");
$stmt2->bind_param("i", $employee_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$documentsByType = [];
while ($row = $result2->fetch_assoc()) {
    $type = $row['document_type'] ?? 'Uncategorized';
    $documentsByType[$type][] = $row;
}

$stmt->close();
$stmt2->close();
$conn->close();

// Icon map per document type keyword
function docIcon(string $type): string {
    $t = strtolower($type);
    if (str_contains($t,'appointment'))  return 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
    if (str_contains($t,'eligib'))       return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    if (str_contains($t,'leave'))        return 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z';
    if (str_contains($t,'service'))      return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
    if (str_contains($t,'pds'))          return 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z';
    if (str_contains($t,'contract'))     return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
    return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
}

function fileIcon(string $type): string {
    $t = strtolower($type);
    if (str_contains($t,'pdf'))   return 'pdf';
    if (str_contains($t,'word') || str_contains($t,'doc')) return 'doc';
    if (str_contains($t,'sheet') || str_contains($t,'xls')) return 'xls';
    if (str_contains($t,'image') || str_contains($t,'jpg') || str_contains($t,'png')) return 'img';
    return 'file';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documents — <?= htmlspecialchars($employee['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Nunito:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── RESET ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── TOKENS ── */
:root {
  --forest:    #234d1e;
  --moss:      #3d6b35;
  --sage:      #6a9e5e;
  --mint:      #aed4a2;
  --mist:      #d8ead4;
  --sky:       #edf5eb;
  --cream:     #f4efe6;
  --parchment: #ece3d0;
  --bark:      #5c3d1e;
  --soil:      #2e1f0e;
  --stone:     #7a8f74;
  --gold:      #b8902e;

  --font-display: 'Lora', Georgia, serif;
  --font-body:    'Nunito', sans-serif;

  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 22px;
}

body {
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 400;
  color: var(--soil);
  background-color: var(--cream);
  background-image:
    radial-gradient(ellipse at 15% 20%, rgba(61,107,53,0.10) 0%, transparent 55%),
    radial-gradient(ellipse at 85% 80%, rgba(35,77,30,0.08) 0%, transparent 55%),
    url("data:image/svg+xml,%3Csvg width='52' height='52' viewBox='0 0 52 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%233d6b35' fill-opacity='0.04' fill-rule='evenodd'%3E%3Cpath d='M26 0C11.641 0 0 11.641 0 26s11.641 26 26 26 26-11.641 26-26S40.359 0 26 0zm0 4c12.15 0 22 9.85 22 22S38.15 48 26 48 4 38.15 4 26 13.85 4 26 4z'/%3E%3C/g%3E%3C/svg%3E");
  min-height: 100vh;
  padding: 2rem 1.25rem 3rem;
}

/* ── LAYOUT ── */
.page { max-width: 960px; margin: 0 auto; animation: fadeUp .6s ease both; }

@keyframes fadeUp {
  from { opacity:0; transform:translateY(20px); }
  to   { opacity:1; transform:translateY(0); }
}
@keyframes slideDown {
  from { opacity:0; transform:translateY(-8px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ── TOP NAV ── */
.topnav { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; }

.btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:9px 20px; border-radius:40px;
  font-family:var(--font-body); font-size:13px; font-weight:600;
  text-decoration:none; cursor:pointer; border:none;
  transition:background .2s, transform .15s, color .2s;
}
.btn svg { width:14px; height:14px; flex-shrink:0; }
.btn-primary  { background:var(--forest); color:#fff; }
.btn-primary:hover  { background:var(--moss); transform:translateX(-2px); }
.btn-outline  { background:var(--parchment); color:var(--bark); border:1.5px solid var(--bark); }
.btn-outline:hover  { background:var(--bark); color:#fff; }

/* ── HEADER CARD ── */
.header-card {
  background:#fff; border-radius:var(--radius-lg);
  border:1.5px solid var(--mist); padding:1.5rem 2rem;
  margin-bottom:1.4rem;
  display:flex; align-items:center; gap:14px;
  position:relative; overflow:hidden;
}
.header-card::before {
  content:''; position:absolute; inset:0 0 auto 0;
  height:5px; background:linear-gradient(90deg,var(--forest),var(--sage),var(--gold));
  border-radius:var(--radius-lg) var(--radius-lg) 0 0;
}
.header-icon {
  width:48px; height:48px; border-radius:14px;
  background:var(--sky); border:1.5px solid var(--mist);
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.header-icon svg { width:22px; height:22px; color:var(--moss); }
.header-title { font-family:var(--font-display); font-size:1.45rem; font-weight:700; color:var(--forest); }
.header-sub   { font-family:var(--font-body); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.7px; color:var(--stone); margin-top:2px; }

/* ── STATS ROW ── */
.stats-row { display:flex; gap:10px; margin-bottom:1.4rem; flex-wrap:wrap; }
.stat-chip {
  background:#fff; border:1.5px solid var(--mist);
  border-radius:40px; padding:7px 16px;
  display:flex; align-items:center; gap:7px;
  font-family:var(--font-body); font-size:12px; font-weight:600; color:var(--stone);
}
.stat-chip strong { color:var(--forest); }

/* ── SEARCH ── */
.search-wrap { margin-bottom:1.4rem; position:relative; }
.search-wrap svg {
  position:absolute; left:14px; top:50%; transform:translateY(-50%);
  width:16px; height:16px; color:var(--stone); pointer-events:none;
}
#searchInput {
  width:100%; padding:10px 14px 10px 40px;
  font-family:var(--font-body); font-size:13px; font-weight:500; color:var(--soil);
  background:#fff; border:1.5px solid var(--mist);
  border-radius:40px; outline:none;
  transition:border-color .2s, box-shadow .2s;
}
#searchInput:focus { border-color:var(--sage); box-shadow:0 0 0 3px rgba(106,158,94,.12); }
#searchInput::placeholder { color:var(--stone); }

/* ── FOLDER ── */
.folder { margin-bottom:12px; animation:fadeUp .5s ease both; }
.folder:nth-child(1) { animation-delay:.04s }
.folder:nth-child(2) { animation-delay:.08s }
.folder:nth-child(3) { animation-delay:.12s }
.folder:nth-child(4) { animation-delay:.16s }
.folder:nth-child(5) { animation-delay:.20s }

.folder-header {
  background:#fff; border:1.5px solid var(--mist);
  border-radius:var(--radius-md);
  padding:14px 18px;
  display:flex; align-items:center; gap:12px;
  cursor:pointer; user-select:none;
  transition:border-color .2s, background .2s, transform .15s;
  position:relative; overflow:hidden;
}
.folder-header::before {
  content:''; position:absolute; left:0; top:0; bottom:0; width:4px;
  background:var(--mint); border-radius:4px 0 0 4px;
  transition:background .2s;
}
.folder.open .folder-header { border-color:var(--sage); }
.folder.open .folder-header::before { background:var(--moss); }
.folder-header:hover { border-color:var(--sage); transform:translateY(-2px); }

.folder-icon {
  width:38px; height:38px; border-radius:10px;
  background:var(--sky); display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.folder-icon svg { width:18px; height:18px; color:var(--moss); }

.folder-title { font-family:var(--font-display); font-size:15px; font-weight:600; color:var(--forest); flex:1; }
.folder-count {
  background:var(--mist); color:var(--forest);
  font-family:var(--font-body); font-size:11px; font-weight:700;
  padding:3px 10px; border-radius:40px;
}
.folder-arrow {
  width:20px; height:20px; color:var(--stone);
  transition:transform .3s ease;
  flex-shrink:0;
}
.folder.open .folder-arrow { transform:rotate(180deg); }

/* ── FOLDER BODY ── */
.folder-body {
  max-height:0; overflow:hidden;
  transition:max-height .45s cubic-bezier(.4,0,.2,1);
  background:#fff;
  border:1.5px solid var(--mist); border-top:none;
  border-radius:0 0 var(--radius-md) var(--radius-md);
  margin-top:-6px; padding-top:6px;
}
.folder.open .folder-body { max-height:1200px; overflow-y:auto; }

/* ── FILE ITEM ── */
.file-item {
  padding:14px 18px; border-bottom:1px dashed var(--mist);
  display:flex; align-items:flex-start; gap:12px;
  transition:background .18s;
}
.file-item:hover { background:var(--sky); }
.file-item:last-child { border-bottom:none; }

.file-type-badge {
  flex-shrink:0; padding:4px 9px;
  border-radius:6px; font-family:var(--font-body);
  font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
  margin-top:2px;
}
.ft-pdf  { background:#fde8e8; color:#a32d2d; }
.ft-doc  { background:#e0eaff; color:#1a3a8f; }
.ft-xls  { background:#e2f5e2; color:#1a5e1a; }
.ft-img  { background:#fff3e0; color:#7a4000; }
.ft-file { background:var(--mist); color:var(--forest); }

.file-info { flex:1; min-width:0; }
.file-name-link {
  font-family:var(--font-body); font-size:14px; font-weight:600;
  color:var(--forest); text-decoration:none; word-break:break-word;
  transition:color .15s;
  display:block; margin-bottom:4px;
}
.file-name-link:hover { color:var(--moss); text-decoration:underline; }
.file-meta {
  font-family:var(--font-body); font-size:11px; font-weight:500; color:var(--stone);
}

.file-actions { display:flex; gap:6px; flex-shrink:0; margin-top:2px; }
.act-btn {
  padding:5px 13px; border-radius:20px;
  font-family:var(--font-body); font-size:12px; font-weight:600;
  text-decoration:none; cursor:pointer; border:none;
  transition:background .18s, color .18s;
}
.act-view   { background:var(--sky); color:var(--moss); border:1px solid var(--mint); }
.act-view:hover   { background:var(--mist); }
.act-edit   { background:var(--gold-lt, #f5e8c0); color:#6b4e0a; border:1px solid #dfc06a; }
.act-edit:hover   { background:#e8d495; }
.act-delete { background:#fde8e8; color:#a32d2d; border:1px solid #f09595; }
.act-delete:hover { background:#f7c1c1; }

/* ── EMPTY STATE ── */
.empty-state {
  background:#fff; border:1.5px solid var(--mist);
  border-radius:var(--radius-lg); padding:3rem 2rem;
  text-align:center; color:var(--stone);
}
.empty-state svg { width:48px; height:48px; color:var(--mint); margin-bottom:1rem; }
.empty-state h3 { font-family:var(--font-display); font-size:1.1rem; font-weight:600; color:var(--forest); margin-bottom:6px; }
.empty-state p  { font-family:var(--font-body); font-size:13px; font-weight:500; }

/* ── NO RESULTS (search) ── */
#noResults { display:none; }
#noResults.visible { display:block; }

/* ── RESPONSIVE ── */
@media (max-width:600px) {
  .file-item { flex-wrap:wrap; }
  .file-actions { width:100%; margin-top:8px; }
  .header-card { flex-direction:column; align-items:flex-start; gap:10px; }
}
</style>
</head>
<body>
<div class="page">

  <!-- TOP NAV -->
  <div class="topnav">
    <a href="employee_info.php?employee_id=<?= $employee_id ?>" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Profile
    </a>
  </div>

  <!-- HEADER CARD -->
  <div class="header-card">
    <div class="header-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
      </svg>
    </div>
    <div>
      <div class="header-sub">Document Repository</div>
      <div class="header-title"><?= htmlspecialchars($employee['name']) ?></div>
    </div>
  </div>

  <!-- STATS ROW -->
  <div class="stats-row">
    <div class="stat-chip">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
      <strong><?= count($documentsByType) ?></strong> folders
    </div>
    <div class="stat-chip">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <strong><?= array_sum(array_map('count', $documentsByType)) ?></strong> total files
    </div>
  </div>

  <!-- SEARCH -->
  <div class="search-wrap">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="searchInput" placeholder="Search documents…" oninput="filterDocs(this.value)">
  </div>

  <!-- FILE MANAGER -->
  <?php if (!empty($documentsByType)): ?>
  <div id="fileManager">

    <?php foreach ($documentsByType as $type => $docs): ?>
    <div class="folder" data-folder="<?= htmlspecialchars(strtolower($type)) ?>">

      <div class="folder-header" onclick="toggleFolder(this)">
        <div class="folder-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="<?= docIcon($type) ?>"/>
          </svg>
        </div>
        <div class="folder-title"><?= htmlspecialchars($type) ?></div>
        <span class="folder-count"><?= count($docs) ?> file<?= count($docs) !== 1 ? 's' : '' ?></span>
        <svg class="folder-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>

      <div class="folder-body">
        <?php foreach ($docs as $row): ?>
        <?php
          $ftag = fileIcon($row['file_type']);
          $ftLabel = strtoupper($row['file_type'] ?: 'FILE');
          $ftLabel = strlen($ftLabel) > 4 ? substr($ftLabel, 0, 4) : $ftLabel;
        ?>
        <div class="file-item" data-filename="<?= htmlspecialchars(strtolower($row['file_name'])) ?>">

          <span class="file-type-badge ft-<?= $ftag ?>"><?= htmlspecialchars($ftLabel) ?></span>

          <div class="file-info">
            <a class="file-name-link" href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
              <?= htmlspecialchars($row['file_name']) ?>
            </a>
            <div class="file-meta">
              <?= htmlspecialchars($row['file_type']) ?>
              &nbsp;·&nbsp;
              <?= date("M d, Y  h:i A", strtotime($row['uploaded_at'])) ?>
            </div>
          </div>

          <div class="file-actions">
            <a href="view.php?id=<?= $row['id'] ?>" target="_blank" class="act-btn act-view">View</a>
            <a href="edit.php?id=<?= $row['id'] ?>" class="act-btn act-edit">Edit</a>
            <form method="POST" action="delete.php" onsubmit="return confirm('Delete this file?');" style="display:inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
              <button type="submit" class="act-btn act-delete">Delete</button>
            </form>
          </div>

        </div>
        <?php endforeach; ?>
      </div>

    </div>
    <?php endforeach; ?>

    <!-- NO RESULTS -->
    <div id="noResults">
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <h3>No documents found</h3>
        <p>Try a different search term.</p>
      </div>
    </div>

  </div>

  <?php else: ?>
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
    </svg>
    <h3>No documents yet</h3>
    <p>No documents have been uploaded for this employee.</p>
  </div>
  <?php endif; ?>

</div><!-- .page -->

<script>
function toggleFolder(header) {
  const folder = header.closest('.folder');
  const isOpen = folder.classList.contains('open');
  // Close all
  document.querySelectorAll('.folder.open').forEach(f => f.classList.remove('open'));
  // Open clicked (unless it was already open)
  if (!isOpen) folder.classList.add('open');
}

function filterDocs(q) {
  q = q.trim().toLowerCase();
  let anyVisible = false;

  document.querySelectorAll('.folder').forEach(folder => {
    if (folder.id === 'noResults') return;
    const folderName = folder.dataset.folder || '';
    let folderMatch = false;

    folder.querySelectorAll('.file-item').forEach(item => {
      const name = item.dataset.filename || '';
      const match = !q || name.includes(q) || folderName.includes(q);
      item.style.display = match ? '' : 'none';
      if (match) folderMatch = true;
    });

    folder.style.display = folderMatch ? '' : 'none';
    if (folderMatch) {
      anyVisible = true;
      if (q) folder.classList.add('open');
    }
  });

  const nr = document.getElementById('noResults');
  if (nr) nr.classList.toggle('visible', !anyVisible && !!q);
}
</script>
</body>
</html>