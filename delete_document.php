<?php
include "config.php";

$employee_id = $_POST['employee_id'];
$type = $_POST['type'];

// get file path first
$sql = "SELECT file_path FROM documents 
        WHERE employee_id=? AND document_type=? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $employee_id, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $file = $row['file_path'];

    // delete file from server
    if (file_exists($file)) {
        unlink($file);
    }

    // delete DB record
    $del = $conn->prepare("DELETE FROM documents WHERE employee_id=? AND document_type=?");
    $del->bind_param("is", $employee_id, $type);
    $del->execute();

    echo "Document deleted successfully.";
} else {
    echo "Document not found.";
}
?>