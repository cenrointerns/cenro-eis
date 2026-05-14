<?php
session_start();
include 'config.php';

/* =========================
   SECURITY CHECK
========================= */
if (!isset($_SESSION['employee_id'])) {
    die("Access denied.");
}

$employee_id = $_SESSION['employee_id'];

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);

/* =========================
   DB CONNECTION
========================= */
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed.");
}

/* =========================
   GET DOCUMENT (SECURE)
========================= */
$stmt = $conn->prepare("
    SELECT file_path, file_name, file_type
    FROM documents
    WHERE id = ? AND employee_id = ?
");

$stmt->bind_param("ii", $id, $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("File not found or access denied.");
}

$file = $result->fetch_assoc();

/* =========================
   FIX FILE PATH (IMPORTANT PART)
========================= */
$raw = $file['file_path'];

/* Try direct path first */
if (file_exists($raw)) {
    $path = $raw;
}
/* Try uploads folder */
else if (file_exists("uploads/" . $raw)) {
    $path = "uploads/" . $raw;
}
else {
    die("File missing on server: " . htmlspecialchars($raw));
}

/* =========================
   OUTPUT FILE TO BROWSER
========================= */
$mime = mime_content_type($path);

header("Content-Type: " . $mime);
header("Content-Disposition: inline; filename=" . basename($path));

readfile($path);
exit();
?>