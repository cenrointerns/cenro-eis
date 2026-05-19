<?php include "config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Documents Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --moss:       #3b5e44;
    --fern:       #5a8a67;
    --sage:       #8fb996;
    --mist:       #d6e8d9;
    --parchment:  #f5f0e8;
    --bark:       #3d2e1e;
    --clay:       #8c6a4b;
    --stone:      #c4b8a8;
    --cream:      #faf7f2;
    --amber:      #c68b3a;
    --shadow:     rgba(61, 46, 30, 0.12);
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--parchment);
    min-height: 100vh;
    overflow-x: hidden;
    color: var(--bark);
  }

  /* === ORGANIC BACKGROUND === */
  .bg-layer {
    position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background:
      radial-gradient(ellipse 70% 50% at 10% 20%, rgba(91,138,103,0.13) 0%, transparent 60%),
      radial-gradient(ellipse 60% 60% at 90% 80%, rgba(143,185,150,0.10) 0%, transparent 55%),
      radial-gradient(ellipse 80% 40% at 50% 110%, rgba(59,94,68,0.08) 0%, transparent 50%);
  }

  /* leaf/blob decorations */
  .bg-layer::before {
    content: "";
    position: absolute; inset: 0;
    background-image: url("./assets/images/cenro.jpeg");
    background-size: cover; background-position: center;
    opacity: 0.06;
  }

  .blob {
    position: fixed; border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%;
    filter: blur(48px); opacity: 0.18; pointer-events: none; z-index: 0;
    animation: morph 18s ease-in-out infinite alternate;
  }
  .blob-1 { width: 420px; height: 380px; background: var(--fern); top: -80px; left: -80px; animation-delay: 0s; }
  .blob-2 { width: 320px; height: 280px; background: var(--sage); bottom: 60px; right: -60px; animation-delay: -7s; }
  .blob-3 { width: 260px; height: 300px; background: var(--clay); top: 40%; right: 20%; animation-delay: -12s; opacity: 0.10; }

  @keyframes morph {
    0%   { border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%; transform: scale(1) rotate(0deg); }
    50%  { border-radius: 40% 60% 30% 70% / 60% 40% 70% 30%; transform: scale(1.08) rotate(3deg); }
    100% { border-radius: 70% 30% 50% 50% / 30% 60% 50% 70%; transform: scale(0.96) rotate(-2deg); }
  }

  /* === LAYOUT === */
  .wrapper {
    position: relative; z-index: 1;
    max-width: 1160px; margin: 0 auto; padding: 40px 24px 80px;
  }

  /* === HEADER === */
  .header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 40px; gap: 16px;
  }

  .header-left { display: flex; align-items: center; gap: 16px; }

  .logo-leaf {
    width: 52px; height: 52px; background: var(--moss);
    border-radius: 50% 20% 50% 20%; display: flex; align-items: center; justify-content: center;
    color: var(--mist); font-size: 22px;
    box-shadow: 0 6px 20px rgba(59,94,68,0.28);
    animation: float 4s ease-in-out infinite;
  }
  @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

  .header-title { display: flex; flex-direction: column; }
  .header-title span {
    font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
    color: var(--fern); font-weight: 500;
  }
  .header-title h1 {
    font-family: 'Playfair Display', serif;
    font-size: 28px; font-weight: 600; color: var(--bark); line-height: 1.1;
  }

  .upload-btn {
    display: flex; align-items: center; gap: 8px;
    background: var(--moss); color: var(--mist);
    border: none; padding: 11px 22px; border-radius: 40px;
    font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
    cursor: pointer; letter-spacing: 0.3px;
    box-shadow: 0 4px 16px rgba(59,94,68,0.30);
    transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
  }
  .upload-btn:hover { background: var(--bark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(59,94,68,0.35); }
  .upload-btn:active { transform: translateY(0); }

  /* === SEARCH / SELECT SECTION === */
  .search-row {
    display: flex; align-items: center; gap: 12px; margin-bottom: 12px;
  }

  .select-wrap {
    position: relative; flex: 1; max-width: 420px;
  }
  .select-wrap i {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: var(--fern); font-size: 16px; pointer-events: none;
  }
  .select-wrap select {
    width: 100%; padding: 13px 16px 13px 42px;
    background: var(--cream); border: 1.5px solid var(--stone);
    border-radius: 40px; font-family: 'DM Sans', sans-serif;
    font-size: 14px; color: var(--bark); cursor: pointer;
    appearance: none; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px var(--shadow);
  }
  .select-wrap select:focus {
    border-color: var(--fern); box-shadow: 0 0 0 3px rgba(90,138,103,0.18);
  }

  .search-input-wrap {
    position: relative; flex: 1;
  }
  .search-input-wrap i {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: var(--clay); font-size: 15px; pointer-events: none;
  }
  .search-input-wrap input {
    width: 100%; padding: 13px 16px 13px 42px;
    background: var(--cream); border: 1.5px solid var(--stone);
    border-radius: 40px; font-family: 'DM Sans', sans-serif;
    font-size: 14px; color: var(--bark); outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px var(--shadow);
  }
  .search-input-wrap input:focus {
    border-color: var(--amber); box-shadow: 0 0 0 3px rgba(198,139,58,0.15);
  }
  .search-input-wrap input::placeholder { color: var(--stone); }

  /* === EMPLOYEE BADGE === */
  .emp-badge {
    display: none; align-items: center; gap: 10px;
    background: var(--cream); border: 1.5px solid var(--mist);
    border-radius: 40px; padding: 8px 18px 8px 10px;
    margin-bottom: 28px; width: fit-content;
    animation: slideIn 0.35s ease;
    box-shadow: 0 2px 10px var(--shadow);
  }
  @keyframes slideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
  .emp-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--fern), var(--moss));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;
  }
  .emp-info { display: flex; flex-direction: column; }
  .emp-info strong { font-size: 14px; font-weight: 500; color: var(--bark); }
  .emp-info small { font-size: 11px; color: var(--clay); }

  /* === STATS ROW === */
  .stats-row {
    display: none; gap: 12px; margin-bottom: 32px;
    flex-wrap: wrap;
  }
  .stat-pill {
    display: flex; align-items: center; gap: 8px;
    background: var(--cream); border: 1.5px solid var(--mist);
    border-radius: 40px; padding: 8px 18px;
    font-size: 13px; color: var(--bark);
    box-shadow: 0 2px 8px var(--shadow);
  }
  .stat-pill i { color: var(--fern); }
  .stat-pill strong { font-size: 18px; font-weight: 600; color: var(--moss); }

  /* === SECTION LABEL === */
  .section-label {
    display: none; align-items: center; gap: 10px;
    margin-bottom: 20px;
  }
  .section-label span {
    font-size: 11px; letter-spacing: 2.5px; text-transform: uppercase;
    color: var(--clay); font-weight: 500;
  }
  .section-label::after {
    content: ""; flex: 1; height: 1px; background: var(--stone); opacity: 0.5;
  }

  /* === GRID === */
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
  }

  /* === CARD === */
  .card {
    display: none;
    background: var(--cream);
    border: 1.5px solid rgba(196,184,168,0.6);
    border-radius: 20px;
    padding: 20px;
    position: relative; overflow: hidden;
    cursor: default;
    box-shadow: 0 4px 16px var(--shadow);
    transition: transform 0.28s cubic-bezier(.34,1.56,.64,1), box-shadow 0.28s;
    animation: cardIn 0.4s ease both;
  }
  @keyframes cardIn {
    from { opacity: 0; transform: translateY(14px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }
  .card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(61,46,30,0.18); }

  /* organic accent blob per card */
  .card::before {
    content: ""; position: absolute; top: -30px; right: -30px;
    width: 90px; height: 90px;
    background: var(--mist); border-radius: 40% 60% 70% 30% / 50%;
    opacity: 0.55; transition: opacity 0.3s, transform 0.3s;
    pointer-events: none;
  }
  .card:hover::before { opacity: 0.85; transform: scale(1.15) rotate(12deg); }

  .card-top {
    display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;
  }

  .card-icon {
    width: 44px; height: 44px; flex-shrink: 0;
    border-radius: 14px; background: var(--mist);
    display: flex; align-items: center; justify-content: center;
    color: var(--moss); font-size: 19px;
    transition: background 0.25s, color 0.25s;
  }
  .card:hover .card-icon { background: var(--moss); color: #fff; }

  .card-meta { flex: 1; min-width: 0; }
  .card-meta h3 {
    font-size: 14px; font-weight: 500; color: var(--bark);
    line-height: 1.3; word-break: break-word;
    margin-bottom: 4px;
  }
  .card-meta .tag {
    display: inline-block; font-size: 10px; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--fern); font-weight: 500;
  }

  .card-divider {
    height: 1px; background: var(--stone); opacity: 0.35; margin-bottom: 14px;
  }

  /* === ACTION BUTTONS === */
  .btn-group { display: flex; gap: 6px; }

  .btn {
    flex: 1; padding: 8px 4px; border: none; border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    gap: 5px; transition: all 0.2s;
  }

  .btn-view {
    background: var(--mist); color: var(--moss); border: 1.5px solid rgba(90,138,103,0.3);
  }
  .btn-view:hover { background: var(--moss); color: #fff; border-color: var(--moss); }

  .btn-edit {
    background: rgba(198,139,58,0.12); color: var(--amber);
    border: 1.5px solid rgba(198,139,58,0.25);
  }
  .btn-edit:hover { background: var(--amber); color: #fff; border-color: var(--amber); }

  .btn-delete {
    background: rgba(176,53,43,0.08); color: #b0352b;
    border: 1.5px solid rgba(176,53,43,0.2);
  }
  .btn-delete:hover { background: #b0352b; color: #fff; border-color: #b0352b; }

  .btn i { font-size: 12px; }

  /* === EMPTY STATE === */
  .empty-state {
    display: none; flex-direction: column; align-items: center; justify-content: center;
    padding: 80px 20px; text-align: center; gap: 14px;
  }
  .empty-state .leaf-icon {
    font-size: 48px; color: var(--sage); opacity: 0.6;
    animation: float 3s ease-in-out infinite;
  }
  .empty-state h3 {
    font-family: 'Playfair Display', serif; font-size: 22px;
    color: var(--bark); font-weight: 400;
  }
  .empty-state p { font-size: 14px; color: var(--clay); max-width: 280px; line-height: 1.6; }

  /* === TOAST === */
  #toast {
    position: fixed; bottom: 30px; right: 30px; z-index: 999;
    background: var(--bark); color: var(--cream);
    padding: 12px 22px; border-radius: 40px;
    font-size: 14px; font-family: 'DM Sans', sans-serif;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    display: flex; align-items: center; gap: 10px;
    transform: translateY(80px); opacity: 0;
    transition: all 0.35s cubic-bezier(.34,1.56,.64,1);
    pointer-events: none;
  }
  #toast.show { transform: translateY(0); opacity: 1; }
  #toast.success i { color: var(--sage); }
  #toast.error i { color: #f08080; }

  /* === DELETE MODAL === */
  .modal-bg {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(61,46,30,0.45); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity 0.25s;
  }
  .modal-bg.open { opacity: 1; pointer-events: all; }
  .modal {
    background: var(--cream); border-radius: 24px; padding: 36px;
    max-width: 380px; width: 90%; text-align: center;
    box-shadow: 0 24px 60px rgba(0,0,0,0.25);
    transform: scale(0.9); transition: transform 0.3s cubic-bezier(.34,1.56,.64,1);
  }
  .modal-bg.open .modal { transform: scale(1); }
  .modal-icon { font-size: 40px; color: #b0352b; margin-bottom: 16px; }
  .modal h3 {
    font-family: 'Playfair Display', serif; font-size: 20px; color: var(--bark);
    margin-bottom: 8px;
  }
  .modal p { font-size: 14px; color: var(--clay); line-height: 1.6; margin-bottom: 24px; }
  .modal-btns { display: flex; gap: 10px; justify-content: center; }
  .modal-btns button {
    padding: 11px 28px; border-radius: 40px; border: none;
    font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
    cursor: pointer; transition: all 0.2s;
  }
  .modal-cancel { background: var(--mist); color: var(--moss); }
  .modal-cancel:hover { background: var(--stone); }
  .modal-confirm { background: #b0352b; color: #fff; }
  .modal-confirm:hover { background: #8a2620; transform: scale(1.03); }

  /* RESPONSIVE */
  @media(max-width: 600px) {
    .header { flex-direction: column; align-items: flex-start; }
    .search-row { flex-direction: column; }
  }
</style>
</head>
<body>

<div class="bg-layer"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<div class="wrapper">

  <!-- HEADER -->
  <div class="header">
    <div class="header-left">
      <div class="logo-leaf"><i class="fas fa-leaf"></i></div>
      <div class="header-title">
        <span>Human Resources</span>
        <h1>Documents Portal</h1>
      </div>
    </div>
    <button class="upload-btn" onclick="goToUpload()">
      <i class="fas fa-cloud-upload-alt"></i> Upload Document
    </button>
  </div>

  <!-- SELECT + SEARCH ROW -->
  <div class="search-row">
    <div class="select-wrap">
      <i class="fas fa-user"></i>
      <select id="employeeSelect">
        <option value="">— Select an Employee —</option>
        <?php
          $sql = "SELECT employee_id, name FROM employees ORDER BY name ASC";
          $result = $conn->query($sql);
          while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['employee_id']}' data-name='{$row['name']}'>{$row['name']}</option>";
          }
        ?>
      </select>
    </div>
    <div class="search-input-wrap">
      <i class="fas fa-search"></i>
      <input type="text" id="docSearch" placeholder="Filter documents…" oninput="filterCards()">
    </div>
  </div>

  <!-- EMPLOYEE BADGE -->
  <div class="emp-badge" id="empBadge">
    <div class="emp-avatar" id="empAvatar">—</div>
    <div class="emp-info">
      <strong id="empName">—</strong>
      <small>Employee Documents</small>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats-row" id="statsRow">
    <div class="stat-pill">
      <i class="fas fa-folder-open"></i>
      <strong id="statTotal">0</strong>
      <span>Documents</span>
    </div>
    <div class="stat-pill">
      <i class="fas fa-check-circle"></i>
      <strong id="statVisible">0</strong>
      <span>Showing</span>
    </div>
  </div>

  <!-- SECTION LABEL -->
  <div class="section-label" id="sectionLabel">
    <span>Document Records</span>
  </div>

  <!-- CARDS GRID -->
  <div class="grid" id="cardGrid">
    <?php
      $docs = $conn->query("SELECT DISTINCT document_type FROM documents ORDER BY document_type ASC");
      $icons = [
        'default'   => 'fa-file-alt',
        'contract'  => 'fa-file-signature',
        'id'        => 'fa-id-card',
        'resume'    => 'fa-file-user',
        'payroll'   => 'fa-file-invoice-dollar',
        'leave'     => 'fa-calendar-minus',
        'appraisal' => 'fa-star',
        'training'  => 'fa-graduation-cap',
        'medical'   => 'fa-notes-medical',
        'memo'      => 'fa-envelope-open-text',
      ];
      while ($row = $docs->fetch_assoc()) {
        $doc = $row['document_type'];
        $id  = str_replace(" ", "_", $doc);
        $lower = strtolower($doc);
        $icon = 'fa-file-alt';
        foreach ($icons as $key => $ico) {
          if (str_contains($lower, $key)) { $icon = $ico; break; }
        }
        $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $doc), 0, 2))));
        echo "
    <div class='card doc-card {$id}' id='card-{$id}' data-type='{$doc}'>
      <div class='card-top'>
        <div class='card-icon'><i class='fas {$icon}'></i></div>
        <div class='card-meta'>
          <h3>{$doc}</h3>
          <span class='tag'>HR Document</span>
        </div>
      </div>
      <div class='card-divider'></div>
      <div class='btn-group'>
        <button class='btn btn-view' onclick=\"openDoc('{$doc}')\"><i class='fas fa-eye'></i> View</button>
        <button class='btn btn-edit' onclick=\"editDoc('{$doc}')\"><i class='fas fa-pen'></i> Edit</button>
        <button class='btn btn-delete' onclick=\"askDelete('{$doc}')\"><i class='fas fa-trash'></i></button>
      </div>
    </div>";
      }
    ?>
  </div>

  <!-- EMPTY STATE -->
  <div class="empty-state" id="emptyState">
    <i class="fas fa-leaf leaf-icon"></i>
    <h3>No documents found</h3>
    <p>Select an employee to view their documents, or try a different search term.</p>
  </div>

</div><!-- /wrapper -->

<!-- DELETE MODAL -->
<div class="modal-bg" id="deleteModal">
  <div class="modal">
    <div class="modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <h3>Remove Document</h3>
    <p id="deleteMsg">Are you sure you want to permanently delete this document?</p>
    <div class="modal-btns">
      <button class="modal-cancel" onclick="closeModal()">Cancel</button>
      <button class="modal-confirm" onclick="confirmDelete()">Yes, Delete</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast"><i class="fas fa-check-circle"></i> <span id="toastMsg"></span></div>

<script>
let pendingDeleteType = null;

function goToUpload() { window.location.href = "upload_form.php"; }

function resetCards() {
  document.querySelectorAll(".doc-card").forEach(c => c.style.display = "none");
}

function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");
  toast.className = type;
  document.getElementById("toastMsg").textContent = msg;
  toast.querySelector("i").className = type === "success"
    ? "fas fa-check-circle" : "fas fa-times-circle";
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 3200);
}

function getInitials(name) {
  return name.split(" ").map(w => w[0]).slice(0, 2).join("").toUpperCase();
}

let activeTypes = [];

document.getElementById("employeeSelect").addEventListener("change", function () {
  const empId = this.value;
  const selOpt = this.options[this.selectedIndex];
  const empName = selOpt.getAttribute("data-name") || "";

  resetCards();
  document.getElementById("empBadge").style.display = "none";
  document.getElementById("statsRow").style.display = "none";
  document.getElementById("sectionLabel").style.display = "none";
  document.getElementById("emptyState").style.display = "none";
  document.getElementById("docSearch").value = "";
  activeTypes = [];

  if (!empId) return;

  document.getElementById("empAvatar").textContent = getInitials(empName);
  document.getElementById("empName").textContent = empName;
  document.getElementById("empBadge").style.display = "flex";

  fetch(`get_document_types.php?employee_id=${empId}`)
    .then(res => res.json())
    .then(types => {
      activeTypes = types;
      let count = 0;
      types.forEach((type, i) => {
        const id = type.replaceAll(" ", "_");
        const card = document.getElementById("card-" + id);
        if (card) {
          card.style.display = "flex";
          card.style.animationDelay = (i * 60) + "ms";
          count++;
        }
      });
      document.getElementById("statTotal").textContent = count;
      document.getElementById("statVisible").textContent = count;
      document.getElementById("statsRow").style.display = "flex";
      document.getElementById("sectionLabel").style.display = "flex";
      if (count === 0) document.getElementById("emptyState").style.display = "flex";
    });
});

function filterCards() {
  const q = document.getElementById("docSearch").value.toLowerCase().trim();
  let visible = 0;
  document.querySelectorAll(".doc-card").forEach(card => {
    if (card.style.display === "none" && !activeTypes.includes(card.dataset.type)) return;
    if (!activeTypes.includes(card.dataset.type)) return;
    const match = card.dataset.type.toLowerCase().includes(q);
    card.style.display = match ? "flex" : "none";
    if (match) visible++;
  });
  document.getElementById("statVisible").textContent = visible;
  const empty = document.getElementById("emptyState");
  empty.style.display = visible === 0 && activeTypes.length > 0 ? "flex" : "none";
}

function openDoc(type) {
  const empId = document.getElementById("employeeSelect").value;
  if (!empId) return showToast("Please select an employee first.", "error");
  fetch(`get_document.php?employee_id=${empId}&type=${encodeURIComponent(type)}`)
    .then(res => res.json())
    .then(data => {
      if (!data.file_path) return showToast("No file found for this document.", "error");
      window.open(data.file_path, "_blank");
    });
}

function editDoc(type) {
  const empId = document.getElementById("employeeSelect").value;
  if (!empId) return showToast("Please select an employee first.", "error");
  window.location.href = `document_edit.php?employee_id=${empId}&type=${encodeURIComponent(type)}`;
}

function askDelete(type) {
  const empId = document.getElementById("employeeSelect").value;
  if (!empId) return showToast("Please select an employee first.", "error");
  pendingDeleteType = type;
  document.getElementById("deleteMsg").textContent =
    `Are you sure you want to permanently delete "${type}"?`;
  document.getElementById("deleteModal").classList.add("open");
}

function closeModal() {
  document.getElementById("deleteModal").classList.remove("open");
  pendingDeleteType = null;
}

function confirmDelete() {
  if (!pendingDeleteType) return;
  const empId = document.getElementById("employeeSelect").value;
  const type = pendingDeleteType;
  closeModal();
  fetch("delete_document.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: `employee_id=${empId}&type=${encodeURIComponent(type)}`
  })
  .then(res => res.text())
  .then(() => {
    showToast(`"${type}" has been removed.`, "success");
    const id = type.replaceAll(" ", "_");
    const card = document.getElementById("card-" + id);
    if (card) {
      card.style.transition = "opacity 0.4s, transform 0.4s";
      card.style.opacity = "0"; card.style.transform = "scale(0.9)";
      setTimeout(() => { card.style.display = "none"; filterCards(); }, 420);
    }
    activeTypes = activeTypes.filter(t => t !== type);
    const total = activeTypes.length;
    document.getElementById("statTotal").textContent = total;
  });
}

// Close modal on backdrop click
document.getElementById("deleteModal").addEventListener("click", function(e) {
  if (e.target === this) closeModal();
});

resetCards();
</script>
</body>
</html>