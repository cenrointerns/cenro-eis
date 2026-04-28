<?php
include "config.php";

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Document not found.");
}

$doc = $result->fetch_assoc();

if (isset($_POST['update'])) {

    $new_type = $_POST['document_type'];

    $stmt = $conn->prepare("UPDATE documents SET document_type = ? WHERE id = ?");
    $stmt->bind_param("si", $new_type, $id);

    if ($stmt->execute()) {
        header("Location: documents.php?updated=1");
        exit;
    } else {
        echo "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Document</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .sub {
            text-align: center;
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #34495e;
        }

        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            margin-bottom: 20px;
        }

        select:focus {
            border-color: #4e73df;
            outline: none;
        }

        .info-box {
            background: #f8f9fc;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #2e59d9;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            background: #e9ecef;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>✏️ Edit Document</h2>
    <div class="sub">Update document classification</div>

    <div class="info-box">
        <strong>File:</strong> <?= htmlspecialchars($doc['file_name']) ?><br>
        <strong>Employee ID:</strong> <?= $doc['employee_id'] ?><br>
        <span class="badge"><?= htmlspecialchars($doc['file_type']) ?></span>
    </div>

    <form method="POST">

        <label>Document Type</label>

        <select name="document_type" required>
            <option value="PDS" <?= $doc['document_type']=="PDS"?'selected':'' ?>>PDS</option>
            <option value="SALN" <?= $doc['document_type']=="SALN"?'selected':'' ?>>SALN</option>
            <option value="IPC" <?= $doc['document_type']=="IPC"?'selected':'' ?>>IPC</option>
            <option value="OPC" <?= $doc['document_type']=="OPC"?'selected':'' ?>>OPC</option>
            <option value="IPCR" <?= $doc['document_type']=="IPCR"?'selected':'' ?>>IPCR</option>
            <option value="OPCR" <?= $doc['document_type']=="OPCR"?'selected':'' ?>>OPCR</option>
            <option value="Special Order" <?= $doc['document_type']=="Special Order"?'selected':'' ?>>Special Order</option>
            <option value="Reporting for Duty" <?= $doc['document_type']=="Reporting for Duty"?'selected':'' ?>>Reporting for Duty</option>
            <option value="Memorandum" <?= $doc['document_type']=="Memorandum"?'selected':'' ?>>Memorandum</option>
            <option value="IDP" <?= $doc['document_type']=="IDP"?'selected':'' ?>>IDP</option>
            <option value="Appointment" <?= $doc['document_type']=="Appointment"?'selected':'' ?>>Appointment</option>
            <option value="Office Clearance" <?= $doc['document_type']=="Office Clearance"?'selected':'' ?>>Office Clearance</option>
        </select>

        <button type="submit" name="update">💾 Save Changes</button>

    </form>

    <a class="back" href="documents.php">← Back to Documents</a>

</div>

</body>
</html>