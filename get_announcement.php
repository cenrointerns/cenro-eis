<?php
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT * FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'deadline' => $row['deadline'],
            'urgency' => $row['urgency'],
            'file_path' => $row['file_path']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Announcement not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

$conn->close();
?>