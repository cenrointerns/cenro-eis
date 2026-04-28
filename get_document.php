<?php
include "config.php";

$employee_id = $_GET['employee_id'] ?? '';
$type = $_GET['type'] ?? '';

if (!$employee_id || !$type) {
    echo json_encode(["file_path" => null]);
    exit;
}

$stmt = $conn->prepare("
    SELECT file_path 
    FROM documents 
    WHERE employee_id = ? AND document_type = ?
    ORDER BY uploaded_at DESC 
    LIMIT 1
");

$stmt->bind_param("is", $employee_id, $type);
$stmt->execute();

$result = $stmt->get_result();

echo json_encode($result->fetch_assoc() ?: ["file_path" => null]);

$stmt->close();
$conn->close();
?>