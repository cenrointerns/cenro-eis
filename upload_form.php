<?php 
include "config.php"; 

// Check for success/error messages
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Employee Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --forest:    #1e3a2f;
            --forest-mid:#2d5240;
            --forest-lt: #3a6b50;
            --sage:      #6a9e7f;
            --mint:      #a8d5b5;
            --lime:      #c5e8a0;
            --cream:     #f4f0e6;
            --bark:      #5c4a2a;
            --text:      #1e3a2f;
            --muted:     #5a7a65;
            --card-bg:   rgba(255,253,245,0.92);
            --border:    rgba(106,158,127,0.35);
            --accent:    #3a8c5c;
            --accent-glow: rgba(58,140,92,0.2);
            --success:   #2e7d4f;
            --error:     #c0392b;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--forest);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed; inset: 0;
            background-image: url('./assets/images/cenro.jpeg');
            background-size: cover;
            background-position: center;
            opacity: 0.12;
            z-index: 0;
        }
        body::after {
            content: "";
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 10% 10%, rgba(168,213,181,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 90% 90%, rgba(58,107,80,0.25) 0%, transparent 60%);
            z-index: 0;
        }

        .page-wrap { position: relative; z-index: 1; width: 100%; max-width: 640px; }

        .org-badge { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; animation: fadeDown 0.5s ease both; }
        .org-badge .leaf { color: var(--lime); font-size: 14px; }
        .org-badge span { font-size: 11px; font-family: 'JetBrains Mono', monospace; color: var(--mint); letter-spacing: 0.15em; text-transform: uppercase; }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 38px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.6);
            animation: fadeUp 0.5s ease 0.1s both;
        }

        .card-header { margin-bottom: 28px; }
        .card-header h1 { font-size: 21px; font-weight: 800; color: var(--forest); display: flex; align-items: center; gap: 12px; }
        .icon-wrap { width: 42px; height: 42px; background: linear-gradient(135deg, var(--accent), var(--forest-lt)); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 17px; color: #fff; box-shadow: 0 4px 14px var(--accent-glow); flex-shrink: 0; }
        .card-header p { margin-top: 6px; margin-left: 54px; font-size: 13px; color: var(--muted); }

        .steps { display: flex; align-items: center; margin-bottom: 28px; }
        .step { display: flex; align-items: center; gap: 7px; flex: 1; }
        .step-num { width: 28px; height: 28px; border-radius: 50%; background: #e8f0eb; border: 2px solid #c5dac9; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: var(--muted); transition: all 0.3s ease; flex-shrink: 0; }
        .step-label { font-size: 11px; color: var(--muted); font-weight: 700; white-space: nowrap; }
        .step-line { flex: 1; height: 2px; background: #d4e6d9; margin: 0 6px; border-radius: 99px; transition: background 0.3s; }
        .step.active .step-num { background: var(--accent); border-color: var(--accent); color: #fff; box-shadow: 0 0 10px var(--accent-glow); }
        .step.active .step-label { color: var(--accent); }
        .step.done .step-num { background: var(--forest); border-color: var(--forest); color: #fff; }
        .step.done .step-label { color: var(--forest); }
        .step.done + .step-line { background: var(--forest); }

        .field { margin-bottom: 20px; }
        label { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; color: var(--forest-mid); text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 8px; }

        select { width: 100%; padding: 11px 15px; background: #fff; border: 1.5px solid #c5dac9; border-radius: 10px; color: var(--text); font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 600; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236a9e7f' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s; }
        select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }

        #employee-preview { margin-top: 10px; padding: 11px 15px; background: rgba(58,140,92,0.07); border: 1.5px solid rgba(58,140,92,0.2); border-radius: 10px; display: none; align-items: center; gap: 12px; animation: fadeIn 0.3s ease; }
        #employee-preview .avatar { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), var(--forest)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0; }
        #employee-preview .info { flex: 1; }
        #employee-preview .emp-name { font-size: 13px; font-weight: 700; color: var(--forest); }
        #employee-preview .emp-id { font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }
        #employee-preview .check { color: var(--success); font-size: 15px; }

        .doc-pill-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); gap: 8px; margin-top: 8px; }
        .doc-pill { padding: 8px 10px; border: 1.5px solid #c5dac9; border-radius: 8px; background: #fff; color: var(--muted); font-size: 12px; font-weight: 700; cursor: pointer; text-align: center; transition: all 0.2s ease; user-select: none; }
        .doc-pill:hover { border-color: var(--accent); color: var(--accent); background: rgba(58,140,92,0.06); }
        .doc-pill.selected { border-color: var(--accent); background: var(--accent); color: #fff; box-shadow: 0 3px 10px var(--accent-glow); }
        
        .others-input-group {
            margin-top: 12px;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .others-input-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1.5px solid var(--accent);
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            background: #fff;
            transition: all 0.2s ease;
        }
        .others-input-group input:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .drop-zone { border: 2px dashed #b8d4be; border-radius: 12px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.25s ease; position: relative; background: #fafff8; }
        .drop-zone:hover, .drop-zone.dragover { border-color: var(--accent); background: rgba(58,140,92,0.04); }
        .drop-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        .drop-zone .dz-icon { font-size: 30px; color: var(--sage); margin-bottom: 10px; transition: color 0.2s, transform 0.2s; }
        .drop-zone:hover .dz-icon { color: var(--accent); transform: translateY(-3px); }
        .drop-zone .dz-title { font-size: 14px; font-weight: 700; color: var(--forest); margin-bottom: 4px; }
        .drop-zone .dz-sub { font-size: 12px; color: var(--muted); }
        .drop-zone .dz-sub span { font-family: 'JetBrains Mono', monospace; color: var(--accent); }

        #file-preview { display: none; margin-top: 10px; padding: 11px 15px; background: rgba(58,140,92,0.07); border: 1.5px solid rgba(58,140,92,0.2); border-radius: 10px; align-items: center; gap: 12px; animation: fadeIn 0.3s ease; }
        #file-preview .file-icon { font-size: 22px; }
        #file-preview .file-info { flex: 1; }
        #file-preview .file-name { font-size: 13px; font-weight: 700; color: var(--forest); word-break: break-all; }
        #file-preview .file-size { font-size: 11px; color: var(--muted); margin-top: 2px; }
        #file-preview .remove-file { color: var(--error); cursor: pointer; font-size: 14px; padding: 4px; border-radius: 4px; transition: background 0.2s; }
        #file-preview .remove-file:hover { background: rgba(192,57,43,0.1); }

        #upload-progress { display: none; margin-top: 14px; }
        .progress-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-bottom: 5px; font-weight: 600; }
        .progress-bar { height: 6px; background: #d4e6d9; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, var(--accent), var(--lime)); border-radius: 99px; width: 0%; transition: width 0.3s ease; }

        .btn-submit { width: 100%; margin-top: 24px; padding: 15px; background: linear-gradient(135deg, var(--accent) 0%, var(--forest) 100%); color: #fff; border: none; border-radius: 12px; font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 800; cursor: pointer; position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 6px 20px var(--accent-glow); }
        .btn-submit:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
        .btn-inner { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .spinner { display: none; width: 17px; height: 17px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; }

        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0 0; }
        .divider hr { flex: 1; border: none; border-top: 1px solid #d4e6d9; }
        .divider span { font-size: 11px; color: #aac4b0; }
        .back-link { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 14px; color: var(--muted); text-decoration: none; font-size: 13px; font-weight: 600; transition: color 0.2s; }
        .back-link:hover { color: var(--accent); }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            animation: fadeIn 0.3s ease;
        }
        .alert-success {
            background: rgba(46,125,79,0.15);
            border: 1px solid var(--success);
            color: var(--success);
        }
        .alert-error {
            background: rgba(192,57,43,0.15);
            border: 1px solid var(--error);
            color: var(--error);
        }

        @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeUp   { from { opacity:0; transform:translateY(14px);  } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeIn   { from { opacity:0; } to { opacity:1; } }
        @keyframes spin     { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="page-wrap">

    <div class="org-badge">
        <i class="fas fa-leaf leaf"></i>
        <span>CENRO &nbsp;·&nbsp; HR Document System</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h1>
                <div class="icon-wrap"><i class="fas fa-file-arrow-up"></i></div>
                Upload Employee Document
            </h1>
            <p>Select an employee and attach an HR document for records.</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="steps">
            <div class="step active" id="step1"><div class="step-num">1</div><div class="step-label">Employee</div></div>
            <div class="step-line" id="line1"></div>
            <div class="step" id="step2"><div class="step-num">2</div><div class="step-label">Document</div></div>
            <div class="step-line" id="line2"></div>
            <div class="step" id="step3"><div class="step-num">3</div><div class="step-label">File</div></div>
        </div>

        <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">

            <div class="field">
                <label><i class="fas fa-user"></i> Select Employee</label>
                <select name="employee_id" id="employee_id" required onchange="handleEmployeeChange(this)">
                    <option value="">-- Select Employee --</option>
                    <?php
                    $sql = "SELECT employee_id, name FROM employees ORDER BY name ASC";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $eid  = htmlspecialchars($row['employee_id']);
                        $name = htmlspecialchars($row['name']);
                        echo "<option value='$eid' data-name='$name'>$eid - $name</option>";
                    }
                    ?>
                </select>
                <div id="employee-preview">
                    <div class="avatar" id="emp-avatar">?</div>
                    <div class="info">
                        <div class="emp-name" id="emp-name-display">—</div>
                        <div class="emp-id"   id="emp-id-display">—</div>
                    </div>
                    <i class="fas fa-circle-check check"></i>
                </div>
            </div>

            <div class="field">
                <label><i class="fas fa-tag"></i> Document Type</label>
                <div class="doc-pill-grid" id="doc-pills">
                    <?php
                    $docTypes = ["PDS","SALN","IPC","OPC","IPCR","OPCR","Special Order",
                                 "Reporting for Duty","Memorandum","IDP","Appointment","Office Clearance","ITR"];
                    foreach ($docTypes as $dt) {
                        $safe = htmlspecialchars($dt);
                        echo "<div class='doc-pill' onclick='selectDocType(this, \"$safe\")'>$safe</div>";
                    }
                    ?>
                    <div class='doc-pill' onclick='selectOthers()'>OTHERS: ___</div>
                </div>
                
                <div class="others-input-group" id="othersInputGroup">
                    <input type="text" id="customDocType" placeholder="Enter document type..." oninput="updateCustomDocType(this.value)">
                </div>
                
                <select name="document_type" id="document_type" required style="display:none;">
                    <option value="">-- Select Document Type --</option>
                    <?php foreach ($docTypes as $dt): $safe = htmlspecialchars($dt); ?>
                        <option value="<?= $safe ?>"><?= $safe ?></option>
                    <?php endforeach; ?>
                    <option value="OTHERS">OTHERS</option>
                </select>
            </div>

            <div class="field">
                <label><i class="fas fa-paperclip"></i> Choose File</label>
                <div class="drop-zone" id="dropZone">
                    <input type="file" name="document" id="fileInput" accept=".pdf,.doc,.docx" required onchange="handleFile(this)">
                    <div class="dz-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                    <div class="dz-title">Drag & drop or click to browse</div>
                    <div class="dz-sub">Accepted: <span>.pdf .doc .docx</span> &nbsp;·&nbsp; Max <span>10 MB</span></div>
                </div>
                <div id="file-preview">
                    <div class="file-icon" id="file-icon-display">📄</div>
                    <div class="file-info">
                        <div class="file-name" id="file-name-display">—</div>
                        <div class="file-size" id="file-size-display">—</div>
                    </div>
                    <div class="remove-file" onclick="removeFile()"><i class="fas fa-xmark"></i></div>
                </div>
                <div id="upload-progress">
                    <div class="progress-label"><span>Uploading…</span><span id="progress-pct">0%</span></div>
                    <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="btn-inner">
                    <i class="fas fa-upload"></i>
                    <span id="btn-text">Upload Document</span>
                    <div class="spinner" id="spinner"></div>
                </span>
            </button>
        </form>

        <div class="divider"><hr><span>or</span><hr></div>
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Documents</a>
    </div>
</div>

<script>
    let customDocTypeValue = '';
    
    function handleEmployeeChange(select) {
        const preview = document.getElementById('employee-preview');
        if (!select.value) { preview.style.display = 'none'; updateSteps(); return; }
        const opt      = select.options[select.selectedIndex];
        const name     = opt.dataset.name || opt.text.split(' - ').slice(1).join(' - ');
        const initials = name.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
        document.getElementById('emp-avatar').textContent       = initials;
        document.getElementById('emp-name-display').textContent = name;
        document.getElementById('emp-id-display').textContent   = 'ID: ' + select.value;
        preview.style.display = 'flex';
        updateSteps();
    }

    function selectDocType(pill, value) {
        document.getElementById('othersInputGroup').style.display = 'none';
        document.getElementById('customDocType').value = '';
        customDocTypeValue = '';
        
        document.querySelectorAll('.doc-pill').forEach(p => p.classList.remove('selected'));
        pill.classList.add('selected');
        const sel = document.getElementById('document_type');
        sel.value = value;
        updateSteps();
    }
    
    function selectOthers() {
        document.querySelectorAll('.doc-pill').forEach(p => p.classList.remove('selected'));
        const othersPill = event.target;
        othersPill.classList.add('selected');
        
        document.getElementById('othersInputGroup').style.display = 'block';
        document.getElementById('customDocType').focus();
        
        const sel = document.getElementById('document_type');
        sel.value = 'OTHERS';
        
        customDocTypeValue = '';
        updateSteps();
    }
    
    function updateCustomDocType(value) {
        customDocTypeValue = value.trim();
        updateSteps();
    }

    function handleFile(input) {
        if (!input.files.length) { removeFile(); return; }
        const file = input.files[0];
        if (file.size > 10 * 1024 * 1024) {
            alert('File too large — maximum is 10 MB.');
            input.value = ''; 
            return;
        }
        const ext = file.name.split('.').pop().toLowerCase();
        const icons = { pdf:'📕', doc:'📘', docx:'📘' };
        document.getElementById('file-icon-display').textContent = icons[ext] || '📄';
        document.getElementById('file-name-display').textContent = file.name;
        document.getElementById('file-size-display').textContent = formatSize(file.size);
        document.getElementById('file-preview').style.display    = 'flex';
        document.getElementById('dropZone').style.borderColor    = 'var(--accent)';
        updateSteps();
    }

    function removeFile() {
        document.getElementById('fileInput').value            = '';
        document.getElementById('file-preview').style.display = 'none';
        document.getElementById('dropZone').style.borderColor = '';
        updateSteps();
    }

    function formatSize(b) {
        if (b < 1024)    return b + ' B';
        if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
        return (b/1048576).toFixed(1) + ' MB';
    }

    function updateSteps() {
        const empDone  = !!document.getElementById('employee_id').value;
        let docDone  = !!document.getElementById('document_type').value;
        
        const docTypeSelect = document.getElementById('document_type');
        if (docTypeSelect.value === 'OTHERS') {
            docDone = !!customDocTypeValue;
        }
        
        const fileDone = !!document.getElementById('fileInput').files.length;
        setStep('step1', empDone  ? 'done' : 'active');
        setStep('step2', docDone  ? 'done' : (empDone  ? 'active' : ''));
        setStep('step3', fileDone ? 'done' : (docDone  ? 'active' : ''));
        document.getElementById('line1').style.background = empDone ? 'var(--forest)' : '';
        document.getElementById('line2').style.background = docDone ? 'var(--forest)' : '';
    }

    function setStep(id, state) {
        const el = document.getElementById(id);
        el.classList.remove('active','done');
        if (state) el.classList.add(state);
        if (state !== 'done') {
            el.querySelector('.step-num').innerHTML = id.replace('step','');
        } else {
            el.querySelector('.step-num').innerHTML = '<i class="fas fa-check" style="font-size:10px"></i>';
        }
    }

    const dz = document.getElementById('dropZone');
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dragover'); });
    dz.addEventListener('dragleave', ()  => dz.classList.remove('dragover'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            document.getElementById('fileInput').files = e.dataTransfer.files;
            handleFile(document.getElementById('fileInput'));
        }
    });

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const emp  = document.getElementById('employee_id').value;
        let doc  = document.getElementById('document_type').value;
        const file = document.getElementById('fileInput').files.length;
        
        if (doc === 'OTHERS' && !customDocTypeValue) {
            e.preventDefault();
            alert('Please enter a custom document type.');
            return;
        }
        
        if (!emp || !doc || !file) {
            e.preventDefault();
            alert('Please complete all three fields before uploading.');
            return;
        }
        
        // Add custom document type as hidden field if needed
        if (doc === 'OTHERS' && customDocTypeValue) {
            let customInput = document.getElementById('custom_document_type');
            if (!customInput) {
                customInput = document.createElement('input');
                customInput.type = 'hidden';
                customInput.name = 'custom_document_type';
                customInput.id = 'custom_document_type';
                this.appendChild(customInput);
            }
            customInput.value = customDocTypeValue;
        }
        
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('btn-text').textContent = 'Uploading…';
        document.getElementById('spinner').style.display = 'block';
        document.getElementById('upload-progress').style.display = 'block';
        
        let pct = 0;
        const fill = document.getElementById('progress-fill');
        const lbl  = document.getElementById('progress-pct');
        const iv = setInterval(() => {
            pct = Math.min(pct + Math.random() * 18, 90);
            fill.style.width = pct + '%';
            lbl.textContent  = Math.round(pct) + '%';
            if (pct >= 90) clearInterval(iv);
        }, 200);
    });
</script>
</body>
</html>