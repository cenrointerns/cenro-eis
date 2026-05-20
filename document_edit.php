<?php
include "config.php";
$employee_id = $_GET['employee_id'] ?? '';
$type = $_GET['type'] ?? '';
if ($employee_id === '' || $type === '') {
    die("Invalid request");
}
$type = urldecode($type);
$stmt = $conn->prepare("
    SELECT * FROM documents 
    WHERE employee_id = ? AND document_type = ? 
    LIMIT 1
");
$stmt->bind_param("is", $employee_id, $type);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
if (!$doc) {
    die("Document not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Document — <?= htmlspecialchars($type) ?></title>
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
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
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
.blob-1 { width: 380px; height: 340px; background: var(--fern); top: -80px; left: -80px; animation-delay: 0s; }
.blob-2 { width: 280px; height: 260px; background: var(--sage); bottom: 60px; right: -60px; animation-delay: -7s; }

@keyframes morph {
  0%   { border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%; transform: scale(1); }
  50%  { border-radius: 40% 60% 30% 70% / 60% 40% 70% 30%; transform: scale(1.06); }
  100% { border-radius: 70% 30% 50% 50% / 30% 60% 50% 70%; transform: scale(0.97); }
}

/* === CARD === */
.card {
  position: relative; z-index: 1;
  width: 100%; max-width: 500px;
  background: var(--cream);
  border: 1.5px solid rgba(122,172,133,0.40);
  border-radius: 28px;
  box-shadow: 0 20px 60px rgba(46,92,58,0.15), 0 4px 16px var(--shadow);
  overflow: hidden;
  animation: cardIn 0.5s cubic-bezier(.34,1.56,.64,1) both;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(24px) scale(0.96); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* === CARD HEADER === */
.card-header {
  background: var(--moss);
  padding: 28px 30px 24px;
  position: relative;
  overflow: hidden;
}

.card-header::before {
  content: "";
  position: absolute; top: -40px; right: -40px;
  width: 140px; height: 140px;
  background: rgba(255,255,255,0.07);
  border-radius: 40% 60% 70% 30% / 50%;
  pointer-events: none;
}

.card-header::after {
  content: "";
  position: absolute; bottom: -30px; left: -20px;
  width: 100px; height: 100px;
  background: rgba(255,255,255,0.05);
  border-radius: 60% 40% 50% 50%;
  pointer-events: none;
}

.header-top {
  display: flex; align-items: center; gap: 14px;
  position: relative; z-index: 1;
}

.header-icon {
  width: 48px; height: 48px;
  background: rgba(255,255,255,0.15);
  border: 1.5px solid rgba(255,255,255,0.25);
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 20px;
  animation: float 4s ease-in-out infinite;
}

@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }

.header-text { flex: 1; }
.header-text span {
  font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
  color: rgba(255,255,255,0.65); font-weight: 500; display: block; margin-bottom: 2px;
}
.header-text h2 {
  font-family: 'Playfair Display', serif;
  font-size: 22px; font-weight: 600; color: #fff; line-height: 1.1;
}

.doc-type-pill {
  margin-top: 14px; position: relative; z-index: 1;
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.22);
  border-radius: 40px; padding: 6px 14px;
  color: rgba(255,255,255,0.90); font-size: 13px; font-weight: 400;
}
.doc-type-pill i { font-size: 12px; color: var(--mist); }

/* === CARD BODY === */
.card-body {
  padding: 28px 30px 30px;
}

/* === FIELD GROUP === */
.field-group {
  margin-bottom: 20px;
}

.field-label {
  display: flex; align-items: center; gap: 7px;
  font-size: 12px; font-weight: 500;
  text-transform: uppercase; letter-spacing: 1.5px;
  color: var(--fern); margin-bottom: 8px;
}
.field-label i { font-size: 12px; }

.field-input {
  width: 100%; padding: 13px 16px;
  background: #fff;
  border: 1.5px solid var(--stone);
  border-radius: 14px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px; color: var(--bark); outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 8px var(--shadow);
}
.field-input:focus {
  border-color: var(--fern);
  box-shadow: 0 0 0 3px rgba(74,124,89,0.18);
}

/* === FILE UPLOAD ZONE === */
.upload-zone {
  border: 2px dashed var(--stone);
  border-radius: 14px;
  padding: 20px 16px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.25s, background 0.25s;
  background: rgba(200,230,204,0.10);
  position: relative;
}
.upload-zone:hover, .upload-zone.drag-over {
  border-color: var(--fern);
  background: rgba(74,124,89,0.07);
}
.upload-zone input[type="file"] {
  position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-icon {
  width: 44px; height: 44px; margin: 0 auto 10px;
  background: var(--mist); border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: var(--moss); font-size: 18px;
  transition: background 0.2s;
}
.upload-zone:hover .upload-icon { background: var(--sage); color: #fff; }
.upload-zone p {
  font-size: 13px; color: var(--clay); line-height: 1.6;
}
.upload-zone p strong { color: var(--fern); }
.file-chosen {
  display: none; margin-top: 10px;
  font-size: 13px; color: var(--moss); font-weight: 500;
}
.file-chosen i { margin-right: 5px; }

/* === CURRENT FILE BOX === */
.current-file {
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px;
  background: rgba(200,230,204,0.25);
  border: 1.5px solid rgba(122,172,133,0.35);
  border-radius: 14px; padding: 12px 16px; margin-top: 10px;
}
.current-file-left {
  display: flex; align-items: center; gap: 10px;
}
.file-icon-wrap {
  width: 36px; height: 36px; border-radius: 10px;
  background: var(--mist); display: flex; align-items: center;
  justify-content: center; color: var(--moss); font-size: 15px; flex-shrink: 0;
}
.current-file-left span {
  font-size: 13px; color: var(--bark); font-weight: 500; word-break: break-all;
}
.view-link {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--moss); color: #fff;
  padding: 7px 14px; border-radius: 10px;
  font-size: 12px; font-weight: 500; text-decoration: none;
  white-space: nowrap; flex-shrink: 0;
  transition: background 0.2s, transform 0.2s;
}
.view-link:hover { background: var(--fern); transform: translateY(-1px); }

/* === DIVIDER === */
.divider {
  height: 1px; background: var(--stone); opacity: 0.35;
  margin: 22px 0;
}

/* === SUBMIT BUTTON === */
.submit-btn {
  width: 100%; padding: 14px;
  background: var(--moss); color: var(--mist);
  border: none; border-radius: 14px;
  font-family: 'DM Sans', sans-serif;
  font-size: 15px; font-weight: 500;
  cursor: pointer; letter-spacing: 0.3px;
  display: flex; align-items: center; justify-content: center; gap: 9px;
  box-shadow: 0 4px 18px rgba(46,92,58,0.32);
  transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
}
.submit-btn:hover {
  background: var(--bark); transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(46,92,58,0.38);
}
.submit-btn:active { transform: translateY(0); }
.submit-btn i { font-size: 15px; }

/* === BACK LINK === */
.back-row {
  text-align: center; margin-top: 16px;
}
.back-link {
  font-size: 13px; color: var(--clay); text-decoration: none;
  display: inline-flex; align-items: center; gap: 6px;
  transition: color 0.2s;
}
.back-link:hover { color: var(--moss); }

/* === FOOTER === */
.card-footer {
  text-align: center;
  padding: 14px 30px 20px;
  font-size: 11px; letter-spacing: 1.5px;
  text-transform: uppercase; color: var(--clay);
  border-top: 1px solid rgba(196,184,168,0.30);
}
.card-footer i { color: var(--sage); margin: 0 4px; font-size: 10px; }

/* === SUCCESS OVERLAY === */
.success-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 200;
  background: rgba(46,92,58,0.55); backdrop-filter: blur(6px);
  align-items: center; justify-content: center;
}
.success-overlay.show { display: flex; }
.success-box {
  background: var(--cream); border-radius: 24px;
  padding: 40px 36px; text-align: center;
  box-shadow: 0 24px 60px rgba(0,0,0,0.22);
  animation: cardIn 0.4s cubic-bezier(.34,1.56,.64,1) both;
  max-width: 340px; width: 90%;
}
.success-icon {
  width: 68px; height: 68px; margin: 0 auto 18px;
  background: var(--mist); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 28px; color: var(--moss);
  animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(74,124,89,0.3)} 50%{box-shadow:0 0 0 12px rgba(74,124,89,0)} }
.success-box h3 {
  font-family: 'Playfair Display', serif;
  font-size: 20px; color: var(--bark); margin-bottom: 8px;
}
.success-box p { font-size: 13px; color: var(--clay); line-height: 1.6; }
</style>
</head>
<body>

<div class="bg-layer"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="card">

  <!-- HEADER -->
  <div class="card-header">
    <div class="header-top">
      <div class="header-icon"><i class="fas fa-file-pen"></i></div>
      <div class="header-text">
        <span>Human Resources</span>
        <h2>Edit Document</h2>
      </div>
    </div>
    <div class="doc-type-pill">
      <i class="fas fa-tag"></i>
      <?= htmlspecialchars($type) ?>
    </div>
  </div>

  <!-- BODY -->
  <div class="card-body">
    <form action="document_update.php" method="POST" enctype="multipart/form-data" id="editForm">
      <input type="hidden" name="id" value="<?= $doc['id'] ?>">

      <!-- File Name -->
      <div class="field-group">
        <div class="field-label"><i class="fas fa-font"></i> File Name</div>
        <input
          class="field-input"
          type="text"
          name="file_name"
          value="<?= htmlspecialchars($doc['file_name']) ?>"
          required
          placeholder="Enter document name…"
        >
      </div>

      <!-- Current File -->
      <div class="field-group">
        <div class="field-label"><i class="fas fa-paperclip"></i> Current File</div>
        <div class="current-file">
          <div class="current-file-left">
            <div class="file-icon-wrap"><i class="fas fa-file-alt"></i></div>
            <span><?= htmlspecialchars(basename($doc['file_path'])) ?></span>
          </div>
          <a class="view-link" href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">
            <i class="fas fa-eye"></i> View
          </a>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Replace File -->
      <div class="field-group">
        <div class="field-label"><i class="fas fa-cloud-upload-alt"></i> Replace File</div>
        <div class="upload-zone" id="uploadZone">
          <input type="file" name="file" id="fileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          <div class="upload-icon"><i class="fas fa-upload"></i></div>
          <p><strong>Click to upload</strong> or drag & drop<br>PDF, DOC, DOCX, JPG, PNG</p>
          <div class="file-chosen" id="fileChosen">
            <i class="fas fa-check-circle"></i> <span id="chosenName"></span>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="submit-btn">
        <i class="fas fa-floppy-disk"></i> Update Document
      </button>
    </form>

    <div class="back-row">
      <a href="javascript:history.back()" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Documents
      </a>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="card-footer">
    CENRO-DENR <i class="fas fa-leaf"></i> Manolo Fortich, Bukidnon
  </div>

</div>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="successOverlay">
  <div class="success-box">
    <div class="success-icon"><i class="fas fa-check"></i></div>
    <h3>Document Updated</h3>
    <p>Your changes have been saved successfully. Redirecting…</p>
  </div>
</div>

<script>
// File drag & drop + label
const zone = document.getElementById('uploadZone');
const input = document.getElementById('fileInput');
const chosen = document.getElementById('fileChosen');
const chosenName = document.getElementById('chosenName');

input.addEventListener('change', function() {
  if (this.files.length > 0) {
    chosenName.textContent = this.files[0].name;
    chosen.style.display = 'block';
  }
});

zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.classList.remove('drag-over');
  if (e.dataTransfer.files.length > 0) {
    input.files = e.dataTransfer.files;
    chosenName.textContent = e.dataTransfer.files[0].name;
    chosen.style.display = 'block';
  }
});

// Show success on submit
document.getElementById('editForm').addEventListener('submit', function() {
  document.getElementById('successOverlay').classList.add('show');
});
</script>
</body>
</html>