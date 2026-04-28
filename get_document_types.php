<?php
include "config.php";

$employee_id = $_GET['employee_id'] ?? '';

if (!$employee_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT DISTINCT document_type 
    FROM documents 
    WHERE employee_id = ?
");

$stmt->bind_param("i", $employee_id);
$stmt->execute();

$result = $stmt->get_result();

$types = [];

while ($row = $result->fetch_assoc()) {
    $types[] = $row['document_type'];
}

echo json_encode($types);

$stmt->close();
$conn->close();
?>