<?php
include "config.php";

$id = $_POST['id'] ?? '';
$file_name = $_POST['file_name'] ?? '';

if ($id === '' || $file_name === '') {
    die("Missing data");
}

$file_path = null;

// upload file
if (!empty($_FILES['file']['name'])) {

    $dir = "uploads/";

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $file = time() . "_" . basename($_FILES["file"]["name"]);
    $path = $dir . $file;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $path)) {
        $file_path = $path;
    }
}

// update DB
if ($file_path) {

    $stmt = $conn->prepare("
        UPDATE documents 
        SET file_name=?, file_path=? 
        WHERE id=?
    ");
    $stmt->bind_param("ssi", $file_name, $file_path, $id);

} else {

    $stmt = $conn->prepare("
        UPDATE documents 
        SET file_name=? 
        WHERE id=?
    ");
    $stmt->bind_param("si", $file_name, $id);
}

$stmt->execute();

echo "<script>
alert('Updated successfully');
window.location.href='document_page.php';
</script>";
?>