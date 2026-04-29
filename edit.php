<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_name = $_POST['file_name'];
    $file_type = $_POST['file_type'];

    $stmt = $conn->prepare("UPDATE documents SET file_name = ?, file_type = ? WHERE id = ?");
    $stmt->bind_param("ssi", $file_name, $file_type, $id);
    $stmt->execute();

    $stmt->close();

    header("Location: employee_document.php?employee_id=" . intval($_POST['employee_id']));
    exit;
}

/* FETCH DATA */
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$stmt->close();
$conn->close();

if (!$data) {
    die("Document not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Document</title>

<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: #f4f6f9;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Card */
.card {
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    width: 350px;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

label {
    font-weight: 600;
    font-size: 14px;
}

input[type="text"] {
    width: 100%;
    padding: 10px;
    margin: 6px 0 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

button {
    width: 100%;
    padding: 12px;
    background: #007bff;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    margin: 15% auto;
    text-align: center;
}

.modal-actions button {
    margin: 5px;
    padding: 10px;
}

.cancel {
    background: #ccc;
}

/* Spinner */
.spinner {
    width: 40px;
    height: 40px;
    margin: 15px auto;
    border-radius: 50%;
    background: #007bff;
    animation: bubble 0.6s infinite alternate;
}

@keyframes bubble {
    from {
        transform: scale(1);
        opacity: 0.7;
    }
    to {
        transform: scale(1.5);
        opacity: 1;
    }
}
</style>
</head>

<body>

<div class="card">
    <h2>Edit Document</h2>

    <form method="POST" id="updateForm">
        <input type="hidden" name="employee_id" value="<?= $data['employee_id']; ?>">

        <label>File Name:</label>
        <input type="text" name="file_name" value="<?= htmlspecialchars($data['file_name']); ?>" required>

        <label>File Type:</label>
        <input type="text" name="file_type" value="<?= htmlspecialchars($data['file_type']); ?>" required>

        <!-- IMPORTANT: button is NOT submit -->
        <button type="button" onclick="openModal()">Update</button>
    </form>
</div>

<!-- Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">

        <!-- Confirm -->
        <div id="confirmContent">
            <p>Are you sure you want to update this document?</p>
            <div class="modal-actions">
                <button onclick="startUpdate()">Yes, Update</button>
                <button onclick="closeModal()" class="cancel">Cancel</button>
            </div>
        </div>

        <!-- Loading -->
        <div id="loadingContent" style="display:none;">
            <div class="spinner"></div>
            <p>Updating...</p>
        </div>

    </div>
</div>

<script>
function openModal() {
    document.getElementById('updateModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('updateModal').style.display = 'none';

    // reset state if closed
    document.getElementById('confirmContent').style.display = 'block';
    document.getElementById('loadingContent').style.display = 'none';
}

function startUpdate() {
    document.getElementById('confirmContent').style.display = 'none';
    document.getElementById('loadingContent').style.display = 'block';

    setTimeout(() => {
        document.getElementById('updateForm').submit();
    }, 2000);
}
</script>

</body>
</html>