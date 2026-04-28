<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Employee Document</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            max-width: 650px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .subtext {
            text-align: center;
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        select, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .btn {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background: #2e59d9;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <h2><i class="fas fa-upload"></i> Upload Employee Document</h2>
    <div class="subtext">Select employee and upload HR document</div>

    <form action="upload.php" method="POST" enctype="multipart/form-data">

        <!-- Employee -->
        <label>Select Employee</label>
        <select name="employee_id" required>
            <option value="">-- Select Employee --</option>

            <?php
            $sql = "SELECT employee_id, name FROM employees ORDER BY name ASC";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['employee_id']}'>
                        {$row['employee_id']} - {$row['name']}
                      </option>";
            }
            ?>
        </select>

        <!-- Document Type -->
        <label>Document Type</label>
        <select name="document_type" required>
            <option value="">-- Select Document Type --</option>
            <option value="PDS">PDS</option>
            <option value="SALN">SALN</option>
            <option value="IPC">IPC</option>
            <option value="OPC">OPC</option>
            <option value="IPCR">IPCR</option>
            <option value="OPCR">OPCR</option>
            <option value="Special Order">Special Order</option>
            <option value="Reporting for Duty">Reporting for Duty</option>
            <option value="Memorandum">Memorandum</option>
            <option value="IDP">IDP</option>
            <option value="Appointment">Appointment</option>
            <option value="Office Clearance">Office Clearance</option>
        </select>

        <!-- File -->
        <label>Choose File</label>
        <input type="file" name="document" accept=".pdf,.doc,.docx" required>

        <button type="submit" class="btn">
            <i class="fas fa-upload"></i> Upload Document
        </button>

    </form>

    <a href="document_page.php" class="back">← Back to Documents</a>

</div>

</body>
</html>