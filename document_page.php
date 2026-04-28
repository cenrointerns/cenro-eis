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

h1 {
    text-align: center;
    color: #333;
}

select {
    display: block;
    margin: 0 auto 25px auto;
    padding: 10px;
    width: 300px;
    border-radius: 8px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}

.card {
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    color: white;
    cursor: pointer;
    transition: 0.3s;
    display: none;
}

.card:hover {
    transform: translateY(-5px);
}

.pds { background:#4e73df; }
.saln { background:#1cc88a; }
.ipc { background:#36b9cc; }
.opc { background:#f6c23e; }
.ipcr { background:#e74a3b; }
.opcr { background:#6f42c1; }
.special { background:#fd7e14; }
.duty { background:#20c997; }
.memo { background:#858796; }
.idp { background:#17a2b8; }
.appointment { background:#6610f2; }
.clearance { background:#28a745; }

/* Upload button */
.upload-btn {
    background:#4e73df;
    color:#fff;
    border:none;
    padding:10px 14px;
    border-radius:8px;
    cursor:pointer;
    font-size:14px;
}

.upload-btn:hover {
    opacity:0.85;
    transform: translateY(-2px);
    transition: 0.2s;
}
</style>
</head>

<body>

<div class="container">

<!-- HEADER WITH UPLOAD BUTTON -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1 style="margin:0;">HR Documents Dashboard</h1>

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

    <div class="card pds" id="card-PDS" onclick="openDoc('PDS')"><i class="fas fa-id-card"></i><br>PDS</div>
    <div class="card saln" id="card-SALN" onclick="openDoc('SALN')"><i class="fas fa-file-invoice-dollar"></i><br>SALN</div>
    <div class="card ipc" id="card-IPC" onclick="openDoc('IPC')"><i class="fas fa-user-check"></i><br>IPC</div>
    <div class="card opc" id="card-OPC" onclick="openDoc('OPC')"><i class="fas fa-building"></i><br>OPC</div>
    <div class="card ipcr" id="card-IPCR" onclick="openDoc('IPCR')"><i class="fas fa-chart-line"></i><br>IPCR</div>
    <div class="card opcr" id="card-OPCR" onclick="openDoc('OPCR')"><i class="fas fa-chart-bar"></i><br>OPCR</div>
    <div class="card special" id="card-Special Order" onclick="openDoc('Special Order')"><i class="fas fa-gavel"></i><br>Special Order</div>
    <div class="card duty" id="card-Reporting for Duty" onclick="openDoc('Reporting for Duty')"><i class="fas fa-briefcase"></i><br>Duty</div>
    <div class="card memo" id="card-Memorandum" onclick="openDoc('Memorandum')"><i class="fas fa-envelope"></i><br>Memo</div>
    <div class="card idp" id="card-IDP" onclick="openDoc('IDP')"><i class="fas fa-lightbulb"></i><br>IDP</div>
    <div class="card appointment" id="card-Appointment" onclick="openDoc('Appointment')"><i class="fas fa-user-tie"></i><br>Appointment</div>
    <div class="card clearance" id="card-Office Clearance" onclick="openDoc('Office Clearance')"><i class="fas fa-check-circle"></i><br>Clearance</div>

</div>
</div>

<script>

// Upload redirect
function goToUpload() {
    window.location.href = "upload_form.php";
}

// Load document types per employee
document.getElementById("employeeSelect").addEventListener("change", function () {

    const empId = this.value;

    resetCards();

    if (!empId) return;

    fetch(`get_document_types.php?employee_id=${empId}`)
        .then(res => res.json())
        .then(types => {

            types.forEach(type => {
                const card = document.getElementById("card-" + type);
                if (card) {
                    card.style.display = "block";
                }
            });

        });
});

function resetCards() {
    document.querySelectorAll(".card").forEach(card => {
        card.style.display = "none";
    });
}

// Open document
function openDoc(type) {

    const empId = document.getElementById("employeeSelect").value;

    if (!empId) {
        alert("Please select an employee first.");
        return;
    }

    fetch(`get_document.php?employee_id=${empId}&type=${encodeURIComponent(type)}`)
        .then(res => res.json())
        .then(data => {

            if (!data.file_path) {
                alert("No document found.");
                return;
            }

            window.open(data.file_path, "_blank");

        });
}

// initial state
resetCards();

</script>

</body>
</html>