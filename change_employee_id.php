<?php
include 'config.php';

$msg = "";

/* UPDATE employee_id */
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

/* DELETE employee */
if (isset($_POST['delete_employee'])) {
    $employee_login_id = $_POST['employee_login_id'];

    $stmt = $conn->prepare("DELETE FROM employees_login WHERE employee_login_id=?");
    $stmt->bind_param("i", $employee_login_id);

    if ($stmt->execute()) {
        $msg = "Employee deleted successfully!";
    } else {
        $msg = "Failed to delete employee.";
    }
}

/* FETCH employees */
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
}

td{
    padding:14px;
    text-align:center;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#f1f8e9;
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
}

.delete-btn{
    background:#e53935;
    color:white;
}

.delete-btn:hover{
    background:#b71c1c;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
}

.modal-content{
    background:white;
    width:380px;
    margin:10% auto;
    padding:25px;
    border-radius:15px;
}

/* INPUT */
input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
}

/* SAVE */
.save-btn{
    width:100%;
    background:#43a047;
    color:white;
}

.cancel-btn{
    width:100%;
    margin-top:10px;
    background:#e53935;
    color:white;
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

            <!-- EDIT -->
            <button class="edit-btn"
                onclick="openModal(
                    '<?php echo $row['employee_login_id']; ?>',
                    '<?php echo $row['employee_id']; ?>'
                )">
                Edit ID
            </button>

            <!-- DELETE -->
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Are you sure you want to delete this employee?');">
                <input type="hidden" name="employee_login_id" value="<?php echo $row['employee_login_id']; ?>">
                <button type="submit" name="delete_employee" class="delete-btn">
                    Delete
                </button>
            </form>

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