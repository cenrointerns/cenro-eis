<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_id = $_POST['employee_id'];
    $document_type = $_POST['document_type'];

    // Get employee name
    $sql = "SELECT name FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        die("Employee not found.");
    }

    $employee_name = $row['name'];

    // Clean folder names
    $employee_folder = preg_replace("/[^a-zA-Z0-9_-]/", "_", $employee_name);
    $doc_folder = preg_replace("/[^a-zA-Z0-9_-]/", "_", $document_type);

    // Build full path: uploads/Name/DocumentType/
    $upload_dir = "uploads/" . $employee_folder . "/" . $doc_folder . "/";

    // Create directories if not exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // File handling
    $file_name = basename($_FILES["document"]["name"]);
    $file_tmp = $_FILES["document"]["tmp_name"];

    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

    $new_file_name = strtolower(str_replace(" ", "_", $document_type)) 
                     . "_" . time() . "." . $file_ext;

    $target_path = $upload_dir . $new_file_name;

    // Move file
    if (move_uploaded_file($file_tmp, $target_path)) {

        // Save to DB (optional but recommended)
        $insert = "INSERT INTO documents (employee_id, document_type, file_path)
                   VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("sss", $employee_id, $document_type, $target_path);
        $stmt->execute();

        echo "Upload successful!";
        echo "<br><a href='upload_form.php'>Back</a>";

    } else {
        echo "Upload failed.";
    }
}
?>