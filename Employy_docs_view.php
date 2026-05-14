<?php
session_start();
include 'config.php';

/* =========================
   SECURITY CHECK
========================= */
if (!isset($_SESSION['employee_id'])) {
    header("Location: Employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

/* =========================
   DB CONNECTION
========================= */
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =========================
   FETCH DOCUMENTS
========================= */
$stmt = $conn->prepare("
    SELECT id, file_name, document_type, file_path, file_type, uploaded_at
    FROM documents
    WHERE employee_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Documents</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
        }

        .container {
            width: 90%;
            margin: 40px auto;
        }

        .header {
            background: #1f4e79;
            color: white;
            padding: 20px;
            border-radius: 10px;
        }

        .card {
            background: white;
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background: #f0f0f0;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }

        .view { background: #28a745; }
        .download { background: #007bff; }

        .empty {
            text-align: center;
            padding: 20px;
            color: gray;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>📁 My Documents</h2>
        <p>Only your uploaded files are visible here</p>
    </div>

    <div class="card">

        <?php if ($result->num_rows > 0): ?>

            <table>
                <tr>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Document Type</th>
                    <th>Uploaded</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo htmlspecialchars($row['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['file_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                        <td><?php echo $row['uploaded_at']; ?></td>
                        <td>

                            <a class="btn view"
                               href="view.php?id=<?php echo $row['id']; ?>"
                               target="_blank">
                                View
                            </a>

                            <a class="btn download"
                               href="view_document.php?id=<?php echo $row['id']; ?>">
                                Download
                            </a>

                        </td>
                    </tr>

                <?php endwhile; ?>

            </table>

        <?php else: ?>
            <div class="empty">No documents found.</div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>