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

/* GROUP BY DOCUMENT TYPE */
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
<html>
<head>
<title>Documents - <?= htmlspecialchars($employee['name']); ?></title>

<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    margin: 0;
    padding: 20px;
}

/* HEADER */
.header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

/* BACK BUTTON */
.back-btn {
    display: inline-block;
    padding: 8px 14px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 13px;
    transition: 0.2s;
}

.back-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* FILE MANAGER */
.file-manager {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* FOLDER */
.folder {
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    overflow: hidden;
}

/* FOLDER HEADER */
.folder-header {
    padding: 14px;
    font-weight: 600;
    cursor: pointer;
    background: #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.folder-header:hover {
    background: #e2e8f0;
}

/* FOLDER CONTENT */
.folder-content {
    display: none;
    padding: 10px;
}

/* FILE ITEM */
.file-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.file-item:last-child {
    border-bottom: none;
}

/* FILE NAME (UPDATED FIX) */
.file-name {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}

.file-link {
    color: #1a73e8;
    text-decoration: none;
    font-weight: 600;
}

.file-link:hover {
    text-decoration: underline;
}

/* META */
.file-meta {
    font-size: 12px;
    color: #666;
}

/* ACTIONS */
.file-actions {
    margin-top: 8px;
    display: flex;
    gap: 10px;
    font-size: 13px;
}

.file-actions a,
.file-actions button {
    background: none;
    border: none;
    color: #1d5fd3;
    cursor: pointer;
}

/* EMPTY */
.empty {
    background: white;
    padding: 20px;
    border-radius: 10px;
    color: gray;
}
</style>
</head>

<body>

<div class="header">
    <h2 style="margin:0;">
        Documents of <?= htmlspecialchars($employee['name']); ?>
    </h2>

    <a href="employee_info.php?employee_id=<?= $employee_id; ?>" class="back-btn">
        ⬅ Go Back
    </a>
</div>

<?php if (!empty($documentsByType)): ?>

<div class="file-manager">

    <?php foreach ($documentsByType as $type => $docs): ?>

        <div class="folder">

            <!-- Folder Header -->
            <div class="folder-header" onclick="toggleFolder(this)">
                📁 <?= htmlspecialchars($type); ?>
                <span>(<?= count($docs); ?>)</span>
            </div>

            <!-- Folder Content -->
            <div class="folder-content">

                <?php foreach ($docs as $row): ?>
                    <div class="file-item">

                        <!-- FILE NAME (FIXED + CLICKABLE) -->
                        <div class="file-name">
                            📄
                            <a class="file-link"
                               href="<?= htmlspecialchars($row['file_path']); ?>"
                               target="_blank">

                                <?= htmlspecialchars($row['file_name']); ?>

                            </a>
                        </div>

                        <div class="file-meta">
                            <?= htmlspecialchars($row['file_type'] ?? 'N/A'); ?> •
                            <?= htmlspecialchars($row['uploaded_at']); ?>
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

                                <button type="submit">
                                    Delete
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
    <div class="empty">No documents found for this employee.</div>
<?php endif; ?>

<script>
function toggleFolder(header) {
    const content = header.nextElementSibling;
    content.style.display = (content.style.display === "block") ? "none" : "block";
}
</script>

</body>
</html>