<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id']);
    $employee_id = intval($_POST['employee_id']);

    // Get file path first
    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $file = $row['file_path'];

        // Delete DB record
        $stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Delete physical file
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $conn->close();

    header("Location: employee_document.php?employee_id=" . $employee_id);
    exit;
}
?>