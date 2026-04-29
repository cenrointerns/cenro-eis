<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$stmt->close();
$conn->close();

if (!$row) {
    die("File not found.");
}

$file = $row['file_path'];

if (!file_exists($file)) {
    die("File missing on server.");
}

// Detect MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file);
finfo_close($finfo);

// Allowed inline types
$inlineTypes = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];

// Set headers
header("Content-Type: " . $mime);
header("Content-Length: " . filesize($file));

if (in_array($mime, $inlineTypes)) {
    // View in browser
    header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
} else {
    // Force download
    header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
}

// Output file
readfile($file);
exit;
?>