<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate inputs
    $errors = [];
    
    if (empty($_POST['employee_id'])) {
        $errors[] = "Employee ID is required";
    }
    
    $document_type = $_POST['document_type'];
    if (empty($document_type)) {
        $errors[] = "Document type is required";
    }
    
    // Handle OTHERS document type
    if ($document_type == 'OTHERS' && !empty($_POST['custom_document_type'])) {
        $document_type = 'OTHERS: ' . $_POST['custom_document_type'];
    } elseif ($document_type == 'OTHERS') {
        $errors[] = "Please specify the document type";
    }
    
    if (!isset($_FILES['document']) || $_FILES['document']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please select a file to upload";
    }
    
    if (!empty($errors)) {
        $error_msg = implode(", ", $errors);
        header("Location: upload_form.php?error=1&message=" . urlencode($error_msg));
        exit();
    }
    
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $document_type = mysqli_real_escape_string($conn, $document_type);

    // Get employee name
    $sql = "SELECT name FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: upload_form.php?error=1&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }
    
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        header("Location: upload_form.php?error=1&message=" . urlencode("Employee not found"));
        exit();
    }

    $employee_name = $row['name'];

    // Clean folder names
    $employee_folder = preg_replace("/[^a-zA-Z0-9_-]/", "_", $employee_name);
    $doc_folder = preg_replace("/[^a-zA-Z0-9_-]/", "_", $document_type);

    // Build full path: uploads/Name/DocumentType/
    $upload_dir = "uploads/" . $employee_folder . "/" . $doc_folder . "/";

    // Create directories if not exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            header("Location: upload_form.php?error=1&message=" . urlencode("Failed to create directory. Check permissions."));
            exit();
        }
    }

    // Check file upload error
    if ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => "File too large (server limit)",
            UPLOAD_ERR_FORM_SIZE => "File too large (form limit)",
            UPLOAD_ERR_PARTIAL => "File only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "File upload stopped by extension"
        ];
        $error_msg = $upload_errors[$_FILES['document']['error']] ?? "Unknown upload error";
        header("Location: upload_form.php?error=1&message=" . urlencode($error_msg));
        exit();
    }

    // File handling
    $file_tmp = $_FILES["document"]["tmp_name"];
    $file_size = $_FILES["document"]["size"];
    $file_type = $_FILES["document"]["type"];
    $original_filename = basename($_FILES["document"]["name"]);
    
    // Check file size (10MB max)
    if ($file_size > 10 * 1024 * 1024) {
        header("Location: upload_form.php?error=1&message=" . urlencode("File too large (max 10MB)"));
        exit();
    }
    
    // Check file extension
    $file_ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $allowed_exts = ['pdf', 'doc', 'docx'];
    if (!in_array($file_ext, $allowed_exts)) {
        header("Location: upload_form.php?error=1&message=" . urlencode("Only PDF, DOC, and DOCX files are allowed"));
        exit();
    }

    // Generate new file name
    $new_file_name = strtolower(str_replace(" ", "_", $document_type)) 
                     . "_" . time() . "." . $file_ext;

    $target_path = $upload_dir . $new_file_name;

    // Move file
    if (move_uploaded_file($file_tmp, $target_path)) {

        // Insert into database
        $insert = "INSERT INTO documents (employee_id, file_name, document_type, file_path, file_type, uploaded_at) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insert);
        if ($stmt) {
            $stmt->bind_param("issss", $employee_id, $new_file_name, $document_type, $target_path, $file_type);
            
            if ($stmt->execute()) {
                header("Location: upload_form.php?success=1&message=" . urlencode("Document uploaded successfully!"));
                exit();
            } else {
                // File uploaded but database error
                unlink($target_path); // Delete the uploaded file
                header("Location: upload_form.php?error=1&message=" . urlencode("Database error: " . $stmt->error));
                exit();
            }
            $stmt->close();
        } else {
            // File uploaded but database error
            unlink($target_path); // Delete the uploaded file
            header("Location: upload_form.php?error=1&message=" . urlencode("Database error: " . $conn->error));
            exit();
        }

    } else {
        header("Location: upload_form.php?error=1&message=" . urlencode("Failed to upload file"));
        exit();
    }
} else {
    header("Location: upload_form.php");
    exit();
}
?>