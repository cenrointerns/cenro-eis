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

<style>
body{
    font-family: Arial;
    background: #f4f6f9;
    margin:0;
    padding:0;
}

.container{
    width: 90%;
    margin: 30px auto;
}

h2{
    text-align:center;
    color:#333;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

th{
    background:#2c3e50;
    color:#fff;
    padding:12px;
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

button{
    padding:6px 12px;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

.edit-btn{
    background:#3498db;
    color:white;
}

.save-btn{
    background:#27ae60;
    color:white;
}

.modal{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
}

.modal-content{
    background:#fff;
    width:350px;
    margin:10% auto;
    padding:20px;
    border-radius:10px;
}

input{
    width:100%;
    padding:10px;
    margin:8px 0;
}
</style>

</head>
<body>

<div class="container">

<h2>Employees Management</h2>

<?php if (!empty($msg)) echo "<p style='text-align:center;color:green;'>$msg</p>"; ?>

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

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h3>Update Employee ID</h3>

        <form method="POST">
            <input type="hidden" name="employee_login_id" id="employee_login_id">

            <label>Employee ID</label>
            <input type="number" name="employee_id" id="employee_id" required>

            <button type="submit" name="update_employee_id" class="save-btn">
                Save Changes
            </button>

            <button type="button" onclick="closeModal()" style="margin-top:10px;">
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