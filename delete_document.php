<?php
include "config.php";

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = $_GET['id'];

/* Get file path first */
$stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Document not found.");
}

$row = $result->fetch_assoc();
$filePath = $row['file_path'];

/* Delete from DB */
$stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    // Delete physical file
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    header("Location: documents.php?deleted=1");
    exit;

} else {
    echo "Error deleting record.";
}
?>