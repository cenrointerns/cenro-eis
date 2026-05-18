<?php
include 'config.php';

// Update employee_id
if (isset($_POST['update_employee_id'])) {
    $employee_login_id = $_POST['employee_login_id'];
    $employee_id = $_POST['employee_id'];

    $stmt = $conn->prepare("UPDATE employees_login SET employee_id=? WHERE employee_login_id=?");
    $stmt->bind_param("ii", $employee_id, $employee_login_id);

    if ($stmt->execute()) {
        $msg = "Employee ID updated successfully!";
    } else {
        $msg = "Failed to update Employee ID.";
    }
}

// Fetch employees
$result = $conn->query("SELECT * FROM employees_login ORDER BY employee_login_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Employees Management</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    background: linear-gradient(120deg, #e8f5e9, #f1f8e9);
    min-height:100vh;
}

/* HEADER */
.header{
    background: linear-gradient(135deg, #2e7d32, #66bb6a);
    padding:20px;
    color:white;
    text-align:center;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

.header h2{
    letter-spacing:1px;
}

/* CONTAINER */
.container{
    width:92%;
    margin:30px auto;
}

/* MESSAGE */
.msg{
    text-align:center;
    margin-bottom:15px;
    color:#2e7d32;
    font-weight:600;
}

/* TABLE CARD */
.table-card{
    background:white;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    transition:0.3s;
}

.table-card:hover{
    transform:translateY(-5px);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#43a047;
    color:white;
    padding:15px;
    text-transform:uppercase;
    font-size:14px;
    letter-spacing:1px;
}

td{
    padding:14px;
    text-align:center;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#f1f8e9;
    transition:0.3s;
}

/* BUTTONS */
button{
    padding:8px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:0.3s;
    font-weight:500;
}

.edit-btn{
    background:#4caf50;
    color:white;
}

.edit-btn:hover{
    background:#2e7d32;
    transform:scale(1.05);
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
}

/* MODAL BOX */
.modal-content{
    background:white;
    width:380px;
    margin:10% auto;
    padding:25px;
    border-radius:15px;
    animation:pop 0.3s ease;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

@keyframes pop{
    from{transform:scale(0.7); opacity:0;}
    to{transform:scale(1); opacity:1;}
}

.modal-content h3{
    text-align:center;
    margin-bottom:15px;
    color:#2e7d32;
}

/* INPUT */
input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
    transition:0.3s;
}

input:focus{
    border-color:#4caf50;
    box-shadow:0 0 8px rgba(76,175,80,0.3);
}

/* SAVE BUTTON */
.save-btn{
    width:100%;
    background:#43a047;
    color:white;
}

.save-btn:hover{
    background:#2e7d32;
}

/* CANCEL */
.cancel-btn{
    width:100%;
    margin-top:10px;
    background:#e53935;
    color:white;
}

.cancel-btn:hover{
    background:#b71c1c;
}

/* RESPONSIVE */
@media(max-width:600px){
    .modal-content{
        width:90%;
    }

    td, th{
        font-size:12px;
    }
}
</style>

</head>
<body>

<div class="header">
    <h2>🌿 Employees Management Dashboard</h2>
</div>

<div class="container">

<?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

<div class="table-card">
<table>
    <tr>
        <th>ID</th>
        <th>Employee ID</th>
        <th>Fullname</th>
        <th>Email</th>
        <th>Action</th>
    </tr>

    <?php while($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['employee_login_id']; ?></td>
        <td><?php echo $row['employee_id']; ?></td>
        <td><?php echo $row['fullname']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td>
            <button class="edit-btn"
                onclick="openModal(
                    '<?php echo $row['employee_login_id']; ?>',
                    '<?php echo $row['employee_id']; ?>'
                )">
                Edit ID
            </button>
        </td>
    </tr>
    <?php } ?>

</table>
</div>

</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h3>🌱 Update Employee ID</h3>

        <form method="POST">
            <input type="hidden" name="employee_login_id" id="employee_login_id">

            <label>Employee ID</label>
            <input type="number" name="employee_id" id="employee_id" required>

            <button type="submit" name="update_employee_id" class="save-btn">
                Save Changes
            </button>

            <button type="button" class="cancel-btn" onclick="closeModal()">
                Cancel
            </button>
        </form>
    </div>
</div>

<script>
function openModal(id, emp_id){
    document.getElementById('modal').style.display = 'block';
    document.getElementById('employee_login_id').value = id;
    document.getElementById('employee_id').value = emp_id;
}

function closeModal(){
    document.getElementById('modal').style.display = 'none';
}

window.onclick = function(event){
    let modal = document.getElementById('modal');
    if(event.target == modal){
        modal.style.display = "none";
    }
}
</script>

</body>
</html>