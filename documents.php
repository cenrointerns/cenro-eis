<?php
$conn = new mysqli("localhost", "root", "", "cenro");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_filter = $_GET['filter'] ?? 'ALL';
$search_name = $_GET['search'] ?? '';

/* Get document types */
$sections = [];
$result = $conn->query("SELECT DISTINCT document_type FROM documents ORDER BY document_type ASC");

while ($row = $result->fetch_assoc()) {
    $sections[] = $row['document_type'];
}

function safe_class($text) {
    return str_replace(' ', '-', $text);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Employee Documents</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;

    /* 🔥 Background image */
    background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png'); /* change path here */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    margin: 0;
    padding: 20px;
}

.container {
  max-width:1100px;
  margin:auto;
  background:white;
  padding:25px;
  border-radius:12px;
  box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:20px;
  gap:10px;
}

form {
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

input, select, button {
  padding:8px;
  border-radius:6px;
}

input {
  border:1px solid #ccc;
}

button {
  background:#4e73df;
  color:white;
  border:none;
  cursor:pointer;
}

.section {
  margin-bottom:30px;
}

h3 {
  margin: 0;
  padding: 12px;
  border-radius: 6px 6px 0 0;
  color: white;
  text-align: center;
}

th {
  color:white;
  padding:12px;
  text-align:left;
}

td {
  padding:10px;
  border-bottom:1px solid #ddd;
}

table {
  width: 100%;
  border-collapse: collapse;
}

tr:nth-child(even) {
  background:#fafafa;
}

tr:hover {
  background:#f0f6ff;
}

a {
  text-decoration:none;
  color:#2980b9;
  margin:0 3px;
}

/* MODAL */
.modal {
  display:none;
  position:fixed;
  top:0;
  left:0;
  width:100%;
  height:100%;
  background:rgba(0,0,0,0.7);
  z-index:999;
}

.modal-content {
  margin:3% auto;
  width:85%;
  height:85%;
  background:white;
  border-radius:10px;
  overflow:hidden;
}

.modal-header {
  display:flex;
  justify-content:space-between;
  padding:10px;
  background:#2c3e50;
  color:white;
}

.modal-header button {
  background:#4e73df;
  border:none;
  color:white;
  padding:5px 10px;
  border-radius:5px;
  cursor:pointer;
}

iframe {
  width:100%;
  height:calc(100% - 50px);
  border:none;
}

/* COLORS */
.PDS, .PDS th { background:#4e73df; }
.SALN, .SALN th { background:#1cc88a; }
.IPC, .IPC th { background:#36b9cc; }
.OPC, .OPC th { background:#f6c23e; color:black; }
.IPCR, .IPCR th { background:#e74a3b; }
.OPCR, .OPCR th { background:#6f42c1; }
.Special-Order, .Special-Order th { background:#fd7e14; }
.Reporting-for-Duty, .Reporting-for-Duty th { background:#20c997; }
.Memorandum, .Memorandum th { background:#858796; }
.IDP, .IDP th { background:#17a2b8; }
.Appointment, .Appointment th { background:#6610f2; }
.Office-Clearance, .Office-Clearance th { background:#28a745; }
</style>
</head>

<body>

<div class="container">

<div class="header">
  <h2>📁 Employee Documents</h2>

  <!-- ✅ SEARCH + FILTER -->
  <form method="GET">

    <input 
      type="text" 
      name="search" 
      placeholder="Search employee name..."
      value="<?= htmlspecialchars($search_name) ?>"
    >

    <select name="filter" onchange="this.form.submit()">
      <option value="ALL">All</option>

      <?php foreach ($sections as $sec) { ?>
        <option value="<?= htmlspecialchars($sec) ?>" <?= $selected_filter == $sec ? 'selected' : '' ?>>
          <?= htmlspecialchars($sec) ?>
        </option>
      <?php } ?>

    </select>

    <button type="submit">Search</button>
  </form>
</div>

<?php foreach ($sections as $section) {

if ($selected_filter != 'ALL' && $selected_filter != $section) continue;

$class = safe_class($section);
?>

<div class="section">

<h3 class="<?= $class ?>">
  <i class="fas fa-folder"></i> <?= htmlspecialchars($section) ?>
</h3>

<table>
<tr class="<?= $class ?>">
<th>Employee</th>
<th>File</th>
<th>Uploaded</th>
<th>Actions</th>
</tr>

<?php
/* ✅ SEARCH + FILTER QUERY */
$sql = "
    SELECT d.*, e.name 
    FROM documents d
    LEFT JOIN employees e ON d.employee_id = e.employee_id
    WHERE d.document_type = ?
";

if (!empty($search_name)) {
    $sql .= " AND e.name LIKE ?";
}

$sql .= " ORDER BY d.uploaded_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($search_name)) {
    $like = "%$search_name%";
    $stmt->bind_param("ss", $section, $like);
} else {
    $stmt->bind_param("s", $section);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  echo "<tr><td colspan='4'>No documents</td></tr>";
}

while ($row = $result->fetch_assoc()) {

$fileType = strtolower(pathinfo($row["file_name"], PATHINFO_EXTENSION));

if ($fileType == "pdf") {
  $icon = "fa-file-pdf"; $color="red";
} elseif (in_array($fileType, ["doc","docx"])) {
  $icon = "fa-file-word"; $color="blue";
} else {
  $icon = "fa-file"; $color="gray";
}
?>

<tr>

<!-- ✅ CLEAN NAME ONLY -->
<td>
  <strong><?= htmlspecialchars($row["name"] ?? 'Unknown Employee') ?></strong>
</td>

<td>
  <i class="fas <?= $icon ?>" style="color:<?= $color ?>"></i>
  <?= htmlspecialchars($row["file_name"]) ?>
</td>

<td><?= $row["uploaded_at"] ?></td>

<td>
  <a href="#" onclick="openModal('<?= $row['file_path'] ?>')">View</a> |
  <a href="<?= $row['file_path'] ?>" download>Download</a> |
  <a href="edit_document.php?id=<?= $row['id'] ?>">Edit</a> |
  <a href="delete_document.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
</td>

</tr>

<?php } ?>

</table>
</div>

<?php } ?>

</div>

<!-- MODAL -->
<div id="fileModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span onclick="closeModal()" style="cursor:pointer;">✖</span>

      <div>
        <button onclick="downloadFile()">Download</button>
        <button onclick="openFull()">Full View</button>
      </div>
    </div>

    <iframe id="fileFrame"></iframe>
  </div>
</div>

<script>
let currentFile = "";

function openModal(file) {
  currentFile = file;
  document.getElementById("fileFrame").src = file;
  document.getElementById("fileModal").style.display = "block";
}

function closeModal() {
  document.getElementById("fileModal").style.display = "none";
  document.getElementById("fileFrame").src = "";
}

function openFull() {
  window.open("view_file.php?file=" + encodeURIComponent(currentFile));
}

function downloadFile() {
  let a = document.createElement("a");
  a.href = currentFile;
  a.download = "";
  a.click();
}
</script>

</body>
</html>