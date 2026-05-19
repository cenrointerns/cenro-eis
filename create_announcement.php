<?php
include 'config.php';

$success = "";
$error   = "";

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title    = $_POST['title'];
    $message  = $_POST['message'];
    $deadline = $_POST['deadline'];
    $urgency  = $_POST['urgency'];
    $file_path = NULL;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != "") {
        $fileName   = time() . "_" . basename($_FILES["attachment"]["name"]);
        $targetFile = $uploadDir . $fileName;
        $fileType   = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['pdf','doc','docx','jpg','jpeg','png'];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFile)) {
                $file_path = $targetFile;
            } else { $error = "Failed to upload file."; }
        } else { $error = "Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG."; }
    }

    if (!$error) {
        $sql  = "INSERT INTO announcements (title, message, deadline, urgency, file_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $title, $message, $deadline, $urgency, $file_path);
        if ($stmt->execute()) { $success = "Announcement published successfully!"; }
        else { $error = "Something went wrong: " . $stmt->error; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Announcement · DENR Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --forest:     #0b5d3b;
    --forest-dk:  #084529;
    --forest-lt:  #e8f5ee;
    --leaf:       #22c55e;
    --cream:      #faf9f6;
    --paper:      #ffffff;
    --mist:       #f0f4f2;
    --stone:      #8a9a90;
    --pebble:     #c8d6cd;
    --ink:        #1a2820;
    --charcoal:   #3d4f45;
    --amber:      #d97706;
    --amber-lt:   #fef3c7;
    --red:        #dc2626;
    --red-lt:     #fee2e2;
    --green:      #16a34a;
    --green-lt:   #dcfce7;
    --radius-sm:  10px;
    --radius-md:  16px;
    --radius-lg:  24px;
    --transition: 0.25s cubic-bezier(0.4,0,0.2,1);
}

*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--ink);
    min-height: 100vh;
    padding: 48px 20px 80px;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        radial-gradient(ellipse 70% 50% at 0% 30%, rgba(11,93,59,0.06), transparent),
        radial-gradient(ellipse 50% 70% at 100% 70%, rgba(34,197,94,0.04), transparent);
    pointer-events: none;
    z-index: 0;
}

.page-wrap {
    position: relative;
    z-index: 1;
    max-width: 680px;
    margin: 0 auto;
}

/* ── BACK ── */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--stone);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    padding: 8px 0;
    margin-bottom: 28px;
    transition: color var(--transition);
}
.back-link:hover { color: var(--forest); }
.back-link svg { flex-shrink: 0; }

/* ── PAGE TITLE ── */
.page-title {
    margin-bottom: 32px;
}
.page-title .eyebrow {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--stone);
    margin-bottom: 8px;
}
.page-title h1 {
    font-family: 'Fraunces', serif;
    font-size: 38px;
    font-weight: 600;
    color: var(--forest);
    line-height: 1.1;
}
.page-title p {
    margin-top: 8px;
    font-size: 15px;
    color: var(--stone);
}

/* ── FORM CARD ── */
.form-card {
    background: var(--paper);
    border: 1px solid var(--pebble);
    border-radius: var(--radius-lg);
    overflow: hidden;
    animation: slideUp 0.45s cubic-bezier(0.4,0,0.2,1) both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: none; }
}

/* ── ALERT BANNER ── */
.alert {
    padding: 16px 28px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 14px;
    border-bottom: 1px solid transparent;
}
.alert-icon {
    font-size: 18px;
    flex-shrink: 0;
    margin-top: 1px;
}
.alert-text strong { display: block; font-weight: 600; margin-bottom: 2px; }
.alert-text span { opacity: 0.8; font-size: 13px; }
.alert.success {
    background: var(--green-lt);
    color: var(--green);
    border-color: #bbf7d0;
}
.alert.error {
    background: var(--red-lt);
    color: var(--red);
    border-color: #fecaca;
}

/* ── FORM BODY ── */
form {
    padding: 36px;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.field-group {
    padding: 24px 0;
    border-bottom: 1px solid var(--mist);
}
.field-group:first-child { padding-top: 0; }
.field-group:last-child  { padding-bottom: 0; border-bottom: none; }

.field-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--stone);
    margin-bottom: 10px;
}

.field-label .lbl-icon {
    width: 22px; height: 22px;
    border-radius: 6px;
    background: var(--mist);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
}

.field-optional {
    font-size: 10px;
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    color: var(--pebble);
    margin-left: 4px;
}

/* ── INPUTS ── */
input[type="text"],
input[type="date"],
textarea {
    width: 100%;
    padding: 14px 16px;
    background: var(--mist);
    border: 1.5px solid transparent;
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    color: var(--ink);
    transition: all var(--transition);
    outline: none;
    -webkit-appearance: none;
}

input[type="text"]:hover,
input[type="date"]:hover,
textarea:hover {
    background: #e8efeb;
}

input[type="text"]:focus,
input[type="date"]:focus,
textarea:focus {
    background: var(--paper);
    border-color: var(--forest);
    box-shadow: 0 0 0 4px rgba(11,93,59,0.08);
}

input[type="text"]::placeholder,
textarea::placeholder { color: var(--stone); }

textarea { resize: none; line-height: 1.65; }

/* ── CHAR COUNTER ── */
.char-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
}
.char-track {
    flex: 1;
    height: 3px;
    background: var(--mist);
    border-radius: 99px;
    overflow: hidden;
}
.char-fill {
    height: 100%;
    border-radius: 99px;
    background: var(--forest);
    transition: width 0.2s, background 0.3s;
}
.char-num {
    font-size: 12px;
    color: var(--stone);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.char-num b { color: var(--charcoal); font-weight: 600; }

/* ── URGENCY CARDS ── */
.urgency-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.urg-input { display: none; }

.urg-card {
    padding: 16px 12px;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--mist);
    background: var(--mist);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    transition: all var(--transition);
    user-select: none;
}

.urg-card:hover {
    border-color: var(--pebble);
    background: #e8efeb;
}

.urg-card .urg-emoji { font-size: 24px; }
.urg-card .urg-label { font-size: 13px; font-weight: 600; color: var(--charcoal); }
.urg-card .urg-desc  { font-size: 11px; color: var(--stone); }

#urg-light:checked  ~ .urgency-row .card-light,
#urg-medium:checked ~ .urgency-row .card-medium,
#urg-urgent:checked ~ .urgency-row .card-urgent { display: none; } /* handled by JS */

.urg-card.selected-light {
    border-color: #16a34a;
    background: var(--green-lt);
    box-shadow: 0 0 0 4px rgba(22,163,74,0.1);
}
.urg-card.selected-light .urg-label { color: #14532d; }

.urg-card.selected-medium {
    border-color: var(--amber);
    background: var(--amber-lt);
    box-shadow: 0 0 0 4px rgba(217,119,6,0.1);
}
.urg-card.selected-medium .urg-label { color: #92400e; }

.urg-card.selected-urgent {
    border-color: var(--red);
    background: var(--red-lt);
    box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
}
.urg-card.selected-urgent .urg-label { color: #7f1d1d; }

/* ── DROP ZONE ── */
.drop-zone {
    border: 2px dashed var(--pebble);
    border-radius: var(--radius-sm);
    padding: 32px 20px;
    text-align: center;
    cursor: pointer;
    transition: all var(--transition);
    position: relative;
    background: var(--mist);
}

.drop-zone:hover,
.drop-zone.dragging {
    border-color: var(--forest);
    background: var(--forest-lt);
}

.drop-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.drop-zone .dz-icon { font-size: 32px; margin-bottom: 10px; }
.drop-zone .dz-title { font-size: 14px; color: var(--charcoal); font-weight: 500; margin-bottom: 4px; }
.drop-zone .dz-sub   { font-size: 12px; color: var(--stone); }
.drop-zone .dz-sub span { color: var(--forest); font-weight: 600; }

.file-chip {
    display: none;
    margin-top: 12px;
    padding: 12px 16px;
    background: var(--forest-lt);
    border: 1px solid #a8d4bc;
    border-radius: var(--radius-sm);
    align-items: center;
    gap: 12px;
}
.file-chip.show { display: flex; }
.file-chip .chip-icon { font-size: 20px; }
.file-chip .chip-name { flex: 1; font-size: 13px; font-weight: 500; color: var(--forest); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.file-chip .chip-remove {
    background: none; border: none; cursor: pointer;
    color: var(--stone); font-size: 20px; line-height: 1;
    transition: color var(--transition); padding: 0 4px;
}
.file-chip .chip-remove:hover { color: var(--red); }

/* ── SUBMIT ── */
.submit-area {
    padding-top: 28px;
}

.submit-btn {
    width: 100%;
    padding: 18px 32px;
    background: var(--forest);
    color: white;
    border: none;
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all var(--transition);
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}
.submit-btn:hover::before { transform: translateX(100%); }
.submit-btn:hover { background: var(--forest-dk); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(11,93,59,0.3); }
.submit-btn:active { transform: none; box-shadow: none; }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

.submit-hint {
    text-align: center;
    margin-top: 12px;
    font-size: 12px;
    color: var(--stone);
}

/* ── SPIN ── */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; }

/* ── RESPONSIVE ── */
@media (max-width: 600px) {
    body { padding: 32px 16px 60px; }
    form { padding: 24px 20px; }
    .page-title h1 { font-size: 28px; }
    .urgency-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="page-wrap">

    <a href="dashboard.php" class="back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Back to Dashboard
    </a>

    <a href="manage_announcements.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--forest); font-size: 13px; font-weight: 500; text-decoration: none; margin-left: 20px;">
    📋 Manage Announcements
</a>

    <div class="page-title">
        <div class="eyebrow">Communications</div>
        <h1>New Announcement</h1>
        <p>Publish updates instantly across the organization</p>
    </div>

    <div class="form-card">

        <?php if ($success): ?>
        <div class="alert success">
            <div class="alert-icon">✅</div>
            <div class="alert-text">
                <strong>Published!</strong>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert error">
            <div class="alert-icon">⚠️</div>
            <div class="alert-text">
                <strong>Something went wrong</strong>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="annForm" novalidate>

            <!-- TITLE -->
            <div class="field-group">
                <div class="field-label">
                    <div class="lbl-icon">✏️</div>
                    Title
                </div>
                <input type="text" name="title" id="titleInput"
                       placeholder="e.g. Holiday Schedule Update"
                       maxlength="120" required
                       value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                <div class="char-row">
                    <div class="char-track"><div class="char-fill" id="titleFill" style="width:0%"></div></div>
                    <div class="char-num"><b id="titleCount">0</b> / 120</div>
                </div>
            </div>

            <!-- MESSAGE -->
            <div class="field-group">
                <div class="field-label">
                    <div class="lbl-icon">💬</div>
                    Message
                </div>
                <textarea name="message" id="msgInput" rows="7"
                          maxlength="5000" required
                          placeholder="Write the full announcement body here…"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                <div class="char-row">
                    <div class="char-track"><div class="char-fill" id="msgFill" style="width:0%"></div></div>
                    <div class="char-num"><b id="msgCount">0</b> / 5000</div>
                </div>
            </div>

            <!-- DEADLINE -->
            <div class="field-group">
                <div class="field-label">
                    <div class="lbl-icon">📅</div>
                    Deadline
                </div>
                <input type="date" name="deadline" required
                       value="<?= isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : '' ?>">
            </div>

            <!-- URGENCY -->
            <div class="field-group">
                <div class="field-label">
                    <div class="lbl-icon">🚦</div>
                    Urgency Level
                </div>

                <input type="radio" name="urgency" id="urg-light"  value="light"  class="urg-input" checked>
                <input type="radio" name="urgency" id="urg-medium" value="medium" class="urg-input">
                <input type="radio" name="urgency" id="urg-urgent" value="urgent" class="urg-input">

                <div class="urgency-row">
                    <label for="urg-light" class="urg-card card-light selected-light">
                        <div class="urg-emoji">🟢</div>
                        <div class="urg-label">Light</div>
                        <div class="urg-desc">General info</div>
                    </label>
                    <label for="urg-medium" class="urg-card card-medium">
                        <div class="urg-emoji">🟠</div>
                        <div class="urg-label">Medium</div>
                        <div class="urg-desc">Needs attention</div>
                    </label>
                    <label for="urg-urgent" class="urg-card card-urgent">
                        <div class="urg-emoji">🔴</div>
                        <div class="urg-label">Urgent</div>
                        <div class="urg-desc">Immediate action</div>
                    </label>
                </div>
            </div>

            <!-- ATTACHMENT -->
            <div class="field-group">
                <div class="field-label">
                    <div class="lbl-icon">📎</div>
                    Attachment <span class="field-optional">Optional</span>
                </div>

                <div class="drop-zone" id="dropZone">
                    <input type="file" name="attachment" id="fileInput"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <div class="dz-icon">🗂️</div>
                    <div class="dz-title">Drop your file here</div>
                    <div class="dz-sub"><span>Click to browse</span> · PDF, DOC, DOCX, JPG, PNG</div>
                </div>

                <div class="file-chip" id="fileChip">
                    <span class="chip-icon">📄</span>
                    <span class="chip-name" id="chipName">—</span>
                    <button type="button" class="chip-remove" id="removeFile" title="Remove file">×</button>
                </div>
            </div>

            <!-- SUBMIT -->
            <div class="submit-area">
                <button type="submit" class="submit-btn" id="submitBtn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22 11 13 2 9l20-7z"/></svg>
                    Publish Announcement
                </button>
                <p class="submit-hint">Visible to all employees immediately after publishing</p>
            </div>

        </form>
    </div>
</div>

<script>
/* ── CHAR COUNTERS ── */
function charCounter(inputId, countId, fillId, max) {
    const el   = document.getElementById(inputId);
    const cnt  = document.getElementById(countId);
    const fill = document.getElementById(fillId);
    if (!el) return;
    const update = () => {
        const len = el.value.length;
        cnt.textContent = len;
        const pct = (len / max) * 100;
        fill.style.width = pct + '%';
        fill.style.background = pct > 90 ? '#dc2626' : '#0b5d3b';
    };
    el.addEventListener('input', update);
    update();
}
charCounter('titleInput', 'titleCount', 'titleFill', 120);
charCounter('msgInput',   'msgCount',   'msgFill',   5000);

/* ── URGENCY SELECTION ── */
const urgCards = document.querySelectorAll('.urg-card');
const urgInputs = document.querySelectorAll('.urg-input');

urgInputs.forEach((inp, i) => {
    inp.addEventListener('change', () => {
        urgCards.forEach(c => {
            c.className = c.className.replace(/selected-\w+/g, '').trim();
        });
        const level = inp.value;
        urgCards[i].classList.add('selected-' + level);
    });
});

/* ── FILE UPLOAD ── */
const fileInput = document.getElementById('fileInput');
const dropZone  = document.getElementById('dropZone');
const fileChip  = document.getElementById('fileChip');
const chipName  = document.getElementById('chipName');
const removeBtn = document.getElementById('removeFile');

function showFile(file) {
    chipName.textContent = file.name;
    fileChip.classList.add('show');
    dropZone.style.display = 'none';
}
function clearFile() {
    fileInput.value = '';
    fileChip.classList.remove('show');
    dropZone.style.display = '';
}

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) showFile(fileInput.files[0]);
});
removeBtn.addEventListener('click', clearFile);

['dragenter','dragover'].forEach(e =>
    dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.add('dragging'); })
);
['dragleave','drop'].forEach(e =>
    dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.remove('dragging'); })
);
dropZone.addEventListener('drop', ev => {
    if (ev.dataTransfer.files.length) {
        fileInput.files = ev.dataTransfer.files;
        showFile(ev.dataTransfer.files[0]);
    }
});

/* ── SUBMIT FEEDBACK ── */
document.getElementById('annForm').addEventListener('submit', () => {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = `<svg class="spin" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Publishing…`;
    btn.disabled = true;
});
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
</body>
</html>