<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

/* EMPLOYEE */
$stmt = $conn->prepare("SELECT name FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    die("Employee not found.");
}

/* DOCUMENTS */
$stmt2 = $conn->prepare("
    SELECT id, file_name, file_path, document_type, file_type, uploaded_at
    FROM documents
    WHERE employee_id = ?
    ORDER BY document_type, uploaded_at DESC
");
$stmt2->bind_param("i", $employee_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

/* GROUP */
$documentsByType = [];
while ($row = $result2->fetch_assoc()) {
    $type = $row['document_type'] ?? 'Uncategorized';
    $documentsByType[$type][] = $row;
}

/* STATS */
$totalFiles = 0;
$latestDate = '';
foreach ($documentsByType as $docs) {
    $totalFiles += count($docs);
    foreach ($docs as $d) {
        if (!$latestDate || strtotime($d['uploaded_at']) > strtotime($latestDate)) {
            $latestDate = $d['uploaded_at'];
        }
    }
}

$stmt->close();
$stmt2->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documents — <?= htmlspecialchars($employee['name']); ?></title>

<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>

/* ==============================
   TOKENS
============================== */
:root {
    --green-50:  #f2f8f0;
    --green-100: #d9edd3;
    --green-200: #b5d9a8;
    --green-400: #6aab58;
    --green-600: #3d7a2c;
    --green-800: #1e4716;
    --green-900: #0f2a0a;

    --bark:      #5c4a35;
    --bark-lt:   #e8dfd2;
    --sand:      #f7f3ed;
    --fog:       #eef2eb;

    --text-main: #1a2416;
    --text-mid:  #4a5e42;
    --text-mute: #7a8f72;

    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 18px;
    --radius-xl: 24px;

    --shadow-soft: 0 2px 12px rgba(30,71,22,0.08);
    --shadow-card: 0 4px 24px rgba(30,71,22,0.10);
}

/* ==============================
   RESET & BASE
============================== */
*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background-color: var(--fog);
    background-image:
        radial-gradient(ellipse at 20% 10%, rgba(106,171,88,0.12) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 90%, rgba(61,122,44,0.10) 0%, transparent 55%),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%233d7a2c' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    min-height: 100vh;
    color: var(--text-main);
    padding: 28px 20px 60px;
}

/* ==============================
   LAYOUT
============================== */
.container {
    max-width: 860px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
    animation: fadeUp 0.45s ease both;
}

/* ==============================
   HEADER CARD
============================== */
.header-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: 22px 26px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: var(--shadow-card);
    border: 1px solid rgba(106,171,88,0.15);
    position: relative;
    overflow: hidden;
}

.header-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--green-400), var(--green-200), var(--green-400));
}

.header-identity {
    display: flex;
    align-items: center;
    gap: 14px;
}

.avatar {
    width: 48px;
    height: 48px;
    background: var(--green-100);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid var(--green-200);
}

.avatar i { font-size: 22px; color: var(--green-600); }

.header-title {
    font-family: 'DM Serif Display', serif;
    font-size: 22px;
    color: var(--text-main);
    line-height: 1.2;
}

.header-sub {
    font-size: 13px;
    color: var(--text-mute);
    margin-top: 2px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 500;
    color: var(--green-600);
    background: var(--green-50);
    border: 1px solid var(--green-200);
    border-radius: var(--radius-md);
    padding: 9px 16px;
    text-decoration: none;
    transition: all 0.2s;
}

.back-btn:hover {
    background: var(--green-100);
    border-color: var(--green-400);
    transform: translateY(-1px);
}

/* ==============================
   STATS ROW
============================== */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.stat-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(106,171,88,0.12);
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    background: var(--green-50);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon i { font-size: 18px; color: var(--green-600); }

.stat-label {
    font-size: 11px;
    color: var(--text-mute);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 2px;
}

.stat-value {
    font-size: 19px;
    font-weight: 600;
    color: var(--green-800);
    line-height: 1;
}

.stat-value.small { font-size: 13px; font-weight: 500; line-height: 1.3; }

/* ==============================
   FOLDER SECTION
============================== */
.folder-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.folder {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(106,171,88,0.14);
    overflow: hidden;
    transition: box-shadow 0.2s, transform 0.2s;
}

.folder:hover {
    box-shadow: var(--shadow-card);
    transform: translateY(-1px);
}

/* ---- Folder Header ---- */
.folder-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    cursor: pointer;
    user-select: none;
    transition: background 0.15s;
}

.folder-header:hover { background: var(--green-50); }

.folder-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.folder-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--green-400);
    flex-shrink: 0;
    transition: background 0.2s;
}

.folder.open .folder-dot { background: var(--green-600); }

.folder-header i.ti-folder-open,
.folder-header i.ti-folder {
    font-size: 20px;
    color: var(--green-600);
}

.folder-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-main);
}

.folder-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-count-badge {
    font-size: 11px;
    font-weight: 600;
    color: var(--green-800);
    background: var(--green-100);
    border: 1px solid var(--green-200);
    border-radius: 20px;
    padding: 3px 10px;
}

.chevron {
    font-size: 15px;
    color: var(--text-mute);
    transition: transform 0.28s ease;
}

.folder.open .chevron { transform: rotate(180deg); }

/* ---- Folder Body ---- */
.folder-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.38s ease;
}

.folder.open .folder-body { max-height: 3000px; }

/* ---- File Item ---- */
.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 18px;
    border-top: 1px solid var(--fog);
    gap: 14px;
    transition: background 0.15s;
}

.file-item:hover { background: #f8fbf6; }

.file-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.file-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--green-50);
    border: 1px solid var(--green-100);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.file-icon i { font-size: 17px; color: var(--green-600); }

.file-name {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 340px;
}

.file-name a {
    color: inherit;
    text-decoration: none;
}

.file-name a:hover {
    color: var(--green-600);
    text-decoration: underline;
}

.file-meta {
    font-size: 11.5px;
    color: var(--text-mute);
    margin-top: 3px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.meta-dot {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: var(--text-mute);
    display: inline-block;
}

/* ---- File Actions ---- */
.file-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 11px;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(0,0,0,0.10);
    background: transparent;
    cursor: pointer;
    text-decoration: none;
    color: var(--text-mid);
    font-family: 'DM Sans', sans-serif;
    transition: all 0.17s;
    line-height: 1;
}

.action-btn i { font-size: 13px; }

.action-btn:hover {
    background: var(--green-50);
    border-color: var(--green-200);
    color: var(--green-800);
}

.action-btn.danger:hover {
    background: #fff0f0;
    border-color: #f9a8a8;
    color: #a32d2d;
}

/* ==============================
   EMPTY STATE
============================== */
.empty-state {
    background: white;
    border-radius: var(--radius-xl);
    padding: 56px 30px;
    text-align: center;
    box-shadow: var(--shadow-soft);
    border: 1px dashed var(--green-200);
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: var(--green-50);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.empty-icon i { font-size: 30px; color: var(--green-400); }

.empty-title {
    font-family: 'DM Serif Display', serif;
    font-size: 20px;
    color: var(--text-main);
    margin-bottom: 6px;
}

.empty-sub { font-size: 14px; color: var(--text-mute); }

/* ==============================
   ANIMATION
============================== */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ==============================
   MOBILE
============================== */
@media (max-width: 640px) {
    body { padding: 16px 12px 48px; }

    .header-card { padding: 16px 18px; }
    .header-title { font-size: 18px; }

    .stats-row { grid-template-columns: 1fr 1fr; }
    .stats-row .stat-card:last-child { grid-column: span 2; }

    .file-item { flex-wrap: wrap; gap: 10px; }
    .file-actions { padding-left: 48px; }

    .file-name { max-width: 220px; }
}

</style>
</head>
<body>

<div class="container">

    <!-- ===== HEADER ===== -->
    <div class="header-card">
        <div class="header-identity">
            <div class="avatar">
                <i class="ti ti-user"></i>
            </div>
            <div>
                <div class="header-title"><?= htmlspecialchars($employee['name']); ?></div>
                <div class="header-sub">Employee Document Vault</div>
            </div>
        </div>
        <a href="employee_info.php?employee_id=<?= $employee_id; ?>" class="back-btn">
            <i class="ti ti-arrow-left"></i> Back to Profile
        </a>
    </div>

    <!-- ===== STATS ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-files"></i></div>
            <div>
                <div class="stat-label">Total Files</div>
                <div class="stat-value"><?= $totalFiles; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-folders"></i></div>
            <div>
                <div class="stat-label">Categories</div>
                <div class="stat-value"><?= count($documentsByType); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-calendar-event"></i></div>
            <div>
                <div class="stat-label">Last Upload</div>
                <div class="stat-value small">
                    <?= $latestDate ? date("M d, Y", strtotime($latestDate)) : '—'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FOLDERS ===== -->
    <?php if (!empty($documentsByType)): ?>
    <div class="folder-list">

        <?php foreach ($documentsByType as $type => $docs): ?>
        <div class="folder" id="folder-<?= md5($type); ?>">

            <div class="folder-header" onclick="toggleFolder(this)">
                <div class="folder-header-left">
                    <span class="folder-dot"></span>
                    <i class="ti ti-folder"></i>
                    <span class="folder-name"><?= htmlspecialchars($type); ?></span>
                </div>
                <div class="folder-header-right">
                    <span class="file-count-badge"><?= count($docs); ?> <?= count($docs) === 1 ? 'file' : 'files'; ?></span>
                    <i class="ti ti-chevron-down chevron"></i>
                </div>
            </div>

            <div class="folder-body">
                <?php foreach ($docs as $row): ?>
                <?php
                    $ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
                    $icons = [
                        'pdf'  => 'ti-file-type-pdf',
                        'doc'  => 'ti-file-word',
                        'docx' => 'ti-file-word',
                        'xls'  => 'ti-table',
                        'xlsx' => 'ti-table',
                        'jpg'  => 'ti-photo',
                        'jpeg' => 'ti-photo',
                        'png'  => 'ti-photo',
                        'txt'  => 'ti-file-text',
                        'zip'  => 'ti-file-zip',
                    ];
                    $icon = $icons[$ext] ?? 'ti-file';
                ?>
                <div class="file-item">

                    <div class="file-left">
                        <div class="file-icon">
                            <i class="ti <?= $icon; ?>"></i>
                        </div>
                        <div>
                            <div class="file-name">
                                <a href="<?= htmlspecialchars($row['file_path']); ?>" target="_blank">
                                    <?= htmlspecialchars($row['file_name']); ?>
                                </a>
                            </div>
                            <div class="file-meta">
                                <span><?= strtoupper(htmlspecialchars($row['file_type'])); ?></span>
                                <span class="meta-dot"></span>
                                <span><?= date("M d, Y · g:i A", strtotime($row['uploaded_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="file-actions">
                        <a href="view.php?id=<?= $row['id']; ?>" target="_blank" class="action-btn">
                            <i class="ti ti-eye"></i> View
                        </a>
                        <a href="edit.php?id=<?= $row['id']; ?>" class="action-btn">
                            <i class="ti ti-edit"></i> Edit
                        </a>
                        <form method="POST" action="delete.php"
                              onsubmit="return confirm('Delete this file?');"
                              style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="employee_id" value="<?= $employee_id; ?>">
                            <button type="submit" class="action-btn danger">
                                <i class="ti ti-trash"></i> Delete
                            </button>
                        </form>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="ti ti-leaf"></i></div>
        <div class="empty-title">No documents found</div>
        <div class="empty-sub">This employee has no uploaded documents yet.</div>
    </div>
    <?php endif; ?>

</div><!-- /.container -->

<script>
function toggleFolder(header) {
    const folder = header.parentElement;
    folder.classList.toggle('open');
}
</script>

</body>
</html>