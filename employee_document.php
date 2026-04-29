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
    ORDER BY uploaded_at DESC
");
$stmt2->bind_param("i", $employee_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

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

/* GO BACK BUTTON */
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

/* GRID */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

/* CARD */
.card {
    background: white;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    transition: 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card:hover {
    transform: translateY(-4px);
}

/* FILE NAME */
.filename {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 8px;
}

/* BADGES */
.badge {
    display: inline-block;
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 20px;
    background: #e7f1ff;
    color: #1d5fd3;
    margin-bottom: 6px;
}

.badge.alt {
    background: #fff3cd;
    color: #856404;
}

/* META */
.meta {
    font-size: 12px;
    color: #666;
    margin-top: 8px;
}

/* ACTIONS */
.actions {
    margin-top: 15px;
    display: flex;
    gap: 8px;
}

.btn {
    flex: 1;
    text-align: center;
    padding: 8px;
    border-radius: 8px;
    font-size: 13px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.view { background: #4facfe; color: white; }
.edit { background: #ffc107; color: #000; }
.delete { background: #dc3545; color: white; }

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

    <!-- GO BACK BUTTON -->
    <a href="employee_info.php?employee_id=<?= $employee_id; ?>" class="back-btn">
        ⬅ Go Back
    </a>
</div>

<?php if ($result2->num_rows > 0): ?>
<div class="grid">

    <?php while ($row = $result2->fetch_assoc()): ?>
        <div class="card">

            <div>
                <div class="filename">📄 <?= htmlspecialchars($row['file_name']); ?></div>

                <div class="badge">
                    Document: <?= htmlspecialchars($row['document_type'] ?? 'N/A'); ?>
                </div>

                <div class="badge alt">
                    File Type: <?= htmlspecialchars($row['file_type'] ?? 'N/A'); ?>
                </div>

                <div class="meta">
                    Uploaded: <?= htmlspecialchars($row['uploaded_at']); ?>
                </div>
            </div>

            <div class="actions">

                <a class="btn view"
                   href="view.php?id=<?= $row['id']; ?>"
                   target="_blank">
                    View
                </a>

                <a class="btn edit"
                   href="edit.php?id=<?= $row['id']; ?>">
                    Edit
                </a>

                <form method="POST"
                      action="delete.php"
                      onsubmit="return confirm('Delete this document?');"
                      style="flex:1;">

                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <input type="hidden" name="employee_id" value="<?= $employee_id ?>">

                    <button class="btn delete" type="submit">
                        Delete
                    </button>
                </form>

            </div>

        </div>
    <?php endwhile; ?>

</div>

<?php else: ?>
    <div class="empty">No documents found for this employee.</div>
<?php endif; ?>

</body>
</html>