<?php
include "config.php";

$employee_id = $_GET['employee_id'] ?? '';
$type = $_GET['type'] ?? '';

if ($employee_id === '' || $type === '') {
    die("Invalid request");
}

$type = urldecode($type);

$stmt = $conn->prepare("
    SELECT * FROM documents 
    WHERE employee_id = ? AND document_type = ? 
    LIMIT 1
");

$stmt->bind_param("is", $employee_id, $type);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    die("Document not found");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Document</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: linear-gradient(135deg, #eef2f7, #dfe9f3);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* CARD */
.card {
    width: 480px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
}

/* HEADER */
.card-header {
    background: #4e73df;
    color: white;
    padding: 20px;
    text-align: center;
}

.card-header h2 {
    margin: 0;
    font-size: 20px;
}

.card-header small {
    opacity: 0.8;
}

/* BODY */
.card-body {
    padding: 25px;
}

label {
    font-weight: 600;
    font-size: 13px;
    color: #333;
    display: block;
    margin-top: 15px;
}

input[type="text"],
input[type="file"] {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    border-radius: 8px;
    border: 1px solid #ddd;
    outline: none;
    transition: 0.2s;
}

input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 5px rgba(78,115,223,0.3);
}

/* FILE BOX */
.file-box {
    margin-top: 15px;
    padding: 12px;
    background: #f8f9fc;
    border-radius: 10px;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.file-box a {
    color: #4e73df;
    text-decoration: none;
    font-weight: 600;
}

.file-box i {
    margin-right: 6px;
}

/* BUTTON */
button {
    width: 100%;
    margin-top: 20px;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(78,115,223,0.3);
}

/* FOOTER */
.footer {
    text-align: center;
    font-size: 12px;
    color: #888;
    margin-top: 10px;
}
</style>
</head>

<body>

<div class="card">

    <div class="card-header">
        <h2><i class="fas fa-file-pen"></i> Edit Document</h2>
        <small><?= htmlspecialchars($type) ?></small>
    </div>

    <div class="card-body">

        <form action="document_update.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id" value="<?= $doc['id'] ?>">

            <label><i class="fas fa-font"></i> File Name</label>
            <input type="text" name="file_name"
                   value="<?= htmlspecialchars($doc['file_name']) ?>" required>

            <label><i class="fas fa-upload"></i> Replace File</label>
            <input type="file" name="file">

            <div class="file-box">
                <span>
                    <i class="fas fa-paperclip"></i>
                    Current File
                </span>
                <a href="<?= $doc['file_path'] ?>" target="_blank">
                    View File
                </a>
            </div>

            <button type="submit">
                <i class="fas fa-save"></i> Update Document
            </button>

        </form>

        <div class="footer">
            CENRO-DENR MANOLO FORTICH, BUKIDNON
        </div>

    </div>
</div>

</body>
</html>