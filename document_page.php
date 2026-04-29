<?php include "config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Documents Portal</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Logo_of_the_Department_of_Environment_and_Natural_Resources.svg/1280px-Logo_of_the_Department_of_Environment_and_Natural_Resources.svg.png');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
}

/* ✅ NEW DASHBOARD HEADER */
.header {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    margin-bottom: 20px;
}

.header h1 {
    grid-column: 2;
    text-align: center;
    color: #333;
    margin: 0;
}

.header .upload-btn {
    grid-column: 3;
    justify-self: end;
}

select {
    display: block;
    margin: 0 auto 20px auto;
    padding: 10px;
    width: 300px;
    border-radius: 8px;
}

.grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.card {
    width: 260px;
    background: rgba(0,0,0,0.78);
    color: white;
    padding: 12px 14px;
    border-radius: 12px;
    display: none;
    flex-direction: column;
    gap: 10px;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.doc-title {
    font-size: 13px;
    font-weight: 600;
}

.btn-group {
    display: flex;
    gap: 6px;
}

.card button {
    flex: 1;
    padding: 6px;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    cursor: pointer;
}

.view { background:#333; color:white; }
.edit { background:#f6c23e; }
.delete { background:red; color:white; }

.upload-btn {
    background:#4e73df;
    color:#fff;
    border:none;
    padding:10px 14px;
    border-radius:8px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="container">

<!-- ✅ UPDATED HEADER -->
<div class="header">
    <h1>HR Documents Dashboard</h1>

    <button class="upload-btn" onclick="goToUpload()">
        <i class="fas fa-upload"></i> Upload
    </button>
</div>

<select id="employeeSelect">
    <option value="">-- Select Employee --</option>
    <?php
    $sql = "SELECT employee_id, name FROM employees ORDER BY name ASC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['employee_id']}'>{$row['name']}</option>";
    }
    ?>
</select>

<div class="grid">

<?php
$docs = $conn->query("SELECT DISTINCT document_type FROM documents");

while ($row = $docs->fetch_assoc()) {

    $doc = $row['document_type'];
    $id = str_replace(" ", "_", $doc);

    echo "
    <div class='card {$id}' id='card-{$id}'>

        <div class='card-header'>
            <i class='fas fa-file-alt'></i>
            <div class='doc-title'>{$doc}</div>
        </div>

        <div class='btn-group'>
            <button class='view' onclick=\"openDoc('{$doc}')\">View</button>
            <button class='edit' onclick=\"editDoc('{$doc}')\">Edit</button>
            <button class='delete' onclick=\"deleteDoc('{$doc}')\">Delete</button>
        </div>

    </div>";
}
?>

</div>
</div>

<script>

function goToUpload() {
    window.location.href = "upload_form.php";
}

// RESET
function resetCards() {
    document.querySelectorAll(".card").forEach(c => c.style.display = "none");
}

// EMPLOYEE SELECT
document.getElementById("employeeSelect").addEventListener("change", function () {

    const empId = this.value;

    resetCards();

    if (!empId) return;

    fetch(`get_document_types.php?employee_id=${empId}`)
        .then(res => res.json())
        .then(types => {

            types.forEach(type => {
                const id = type.replaceAll(" ", "_");
                const card = document.getElementById("card-" + id);
                if (card) card.style.display = "flex";
            });

        });
});

// VIEW
function openDoc(type) {

    const empId = document.getElementById("employeeSelect").value;

    if (!empId) return alert("Select employee first");

    fetch(`get_document.php?employee_id=${empId}&type=${encodeURIComponent(type)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.file_path) return alert("No document found");
            window.open(data.file_path, "_blank");
        });
}

// EDIT
function editDoc(type) {

    const empId = document.getElementById("employeeSelect").value;

    if (!empId) return alert("Select employee first");

    window.location.href =
        `document_edit.php?employee_id=${empId}&type=${encodeURIComponent(type)}`;
}

// DELETE
function deleteDoc(type) {

    const empId = document.getElementById("employeeSelect").value;

    if (!empId) return alert("Select employee first");

    if (!confirm("Delete this document?")) return;

    fetch("delete_document.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: `employee_id=${empId}&type=${encodeURIComponent(type)}`
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        location.reload();
    });
}

resetCards();

</script>

</body>
</html>