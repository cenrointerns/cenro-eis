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

$stmt->close();
$stmt2->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Documents - <?= htmlspecialchars($employee['name']); ?></title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

/* =========================
   GLOBAL
========================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    background:
        linear-gradient(rgba(240,244,240,0.92), rgba(240,244,240,0.92)),
        url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png');

    background-size:cover;
    background-position:center;
    background-attachment:fixed;
    min-height:100vh;
    padding:20px;
}

/* =========================
   HEADER
========================= */
.header{
    background:white;
    padding:20px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    margin-bottom:20px;
    animation:fadeIn 0.5s ease;
}

.header h2{
    font-size:22px;
    color:#145a24;
}

/* =========================
   BACK BUTTON
========================= */
.back-btn{
    padding:10px 16px;
    background:#1b5e20;
    color:white;
    text-decoration:none;
    border-radius:12px;
    font-size:14px;
    transition:0.3s ease;
}

.back-btn:hover{
    background:#2e7d32;
    transform:translateY(-3px);
}

/* =========================
   FILE MANAGER
========================= */
.file-manager{
    display:flex;
    flex-direction:column;
    gap:15px;
}

/* =========================
   FOLDER CARD
========================= */
.folder{
    background:white;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    transition:0.3s ease;
}

.folder:hover{
    transform:translateY(-3px);
}

/* =========================
   FOLDER HEADER
========================= */
.folder-header{
    padding:16px;
    background:linear-gradient(135deg,#145a24,#1b5e20);
    color:white;
    font-weight:600;
    cursor:pointer;
    display:flex;
    justify-content:space-between;
    align-items:center;
    transition:0.3s ease;
}

.folder-header:hover{
    background:linear-gradient(135deg,#1b5e20,#2e7d32);
}

/* =========================
   CONTENT (ANIMATED)
========================= */
.folder-content{
    max-height:0;
    overflow:hidden;
    transition:max-height 0.4s ease;
    background:#f9fbf9;
}

/* ACTIVE OPEN */
.folder.active .folder-content{
    max-height:800px;
    overflow-y:auto;
}

/* =========================
   FILE ITEM
========================= */
.file-item{
    padding:15px;
    border-bottom:1px solid #eaeaea;
    transition:0.3s ease;
}

.file-item:hover{
    background:#eef7ee;
}

.file-item:last-child{
    border-bottom:none;
}

.file-name{
    font-weight:600;
    margin-bottom:6px;
    display:flex;
    align-items:center;
    gap:8px;
    word-break:break-word;
}

.file-link{
    color:#1a73e8;
    text-decoration:none;
}

.file-link:hover{
    text-decoration:underline;
}

/* =========================
   META
========================= */
.file-meta{
    font-size:12px;
    color:#666;
}

/* =========================
   ACTIONS
========================= */
.file-actions{
    margin-top:10px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.file-actions a,
.file-actions button{
    font-size:13px;
    text-decoration:none;
    background:none;
    border:none;
    cursor:pointer;
    color:#1b5e20;
    font-weight:500;
}

.file-actions a:hover,
.file-actions button:hover{
    color:#2e7d32;
}

/* =========================
   EMPTY
========================= */
.empty{
    background:white;
    padding:30px;
    text-align:center;
    border-radius:15px;
    color:#777;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

/* =========================
   ANIMATION
========================= */
@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(15px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* =========================
   MOBILE
========================= */
@media(max-width:768px){

    .header{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }

    .folder-header{
        font-size:14px;
    }

    .file-name{
        font-size:14px;
    }
}

</style>
</head>

<body>

<div class="header">
    <h2>📁 Documents of <?= htmlspecialchars($employee['name']); ?></h2>

    <a href="employee_info.php?employee_id=<?= $employee_id; ?>" class="back-btn">
        ⬅ Back
    </a>
</div>

<?php if (!empty($documentsByType)): ?>

<div class="file-manager">

    <?php foreach ($documentsByType as $type => $docs): ?>

        <div class="folder">

            <div class="folder-header" onclick="toggleFolder(this)">
                📂 <?= htmlspecialchars($type); ?>
                <span><?= count($docs); ?> files</span>
            </div>

            <div class="folder-content">

                <?php foreach ($docs as $row): ?>

                <div class="file-item">

                    <div class="file-name">
                        📄
                        <a class="file-link"
                           href="<?= htmlspecialchars($row['file_path']); ?>"
                           target="_blank">
                            <?= htmlspecialchars($row['file_name']); ?>
                        </a>
                    </div>

                    <div class="file-meta">
                        <?= htmlspecialchars($row['file_type']); ?> •
                        <?= date("F d, Y h:i A", strtotime($row['uploaded_at'])); ?>
                    </div>

                    <div class="file-actions">

                        <a href="view.php?id=<?= $row['id']; ?>" target="_blank">
                            View
                        </a>

                        <a href="edit.php?id=<?= $row['id']; ?>">
                            Edit
                        </a>

                        <form method="POST"
                              action="delete.php"
                              onsubmit="return confirm('Delete this file?');"
                              style="display:inline;">

                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="employee_id" value="<?= $employee_id ?>">

                            <button type="submit">Delete</button>

                        </form>

                    </div>

                </div>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<?php else: ?>

<div class="empty">
    No documents found for this employee.
</div>

<?php endif; ?>

<script>

/* =========================
   ACCORDION TOGGLE
========================= */
function toggleFolder(header){
    const folder = header.parentElement;
    folder.classList.toggle("active");
}

</script>

</body>
</html>