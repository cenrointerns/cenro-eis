<?php
$conn = new mysqli("localhost","root","","cenro");
if ($conn->connect_error) die("Connection failed");

$image_folder = "assets/image/employee/";
if (!file_exists($image_folder)) mkdir($image_folder,0777,true);

$successMessage = "";

/* FILTER */
$statusFilter = $_GET['status'] ?? "";

/* CREATE */
if(isset($_POST['add'])){
    $data=$_POST;

    $image_name=null;
    if(!empty($_FILES['image']['name'])){
        $ext=pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
        $image_name=uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'],$image_folder.$image_name);
    }

    $stmt=$conn->prepare("INSERT INTO employees 
    (name,status,image,gender,date_of_birth,nosca_item_number,place_of_assignment,
    position_title,salary_grade,civil_service_eligibility,education,date_of_appointment)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param("ssssssssssss",
    $data['name'],$data['status'],$image_name,
    $data['gender'],$data['date_of_birth'],$data['nosca_item_number'],
    $data['place_of_assignment'],$data['position_title'],$data['salary_grade'],
    $data['civil_service_eligibility'],$data['education'],$data['date_of_appointment']);

    $stmt->execute();

    $successMessage = "Employee successfully added!";
}

/* DELETE */
if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];

    $img=$conn->query("SELECT image FROM employees WHERE employee_id=$id")->fetch_assoc();
    if($img && $img['image'] && file_exists($image_folder.$img['image'])){
        unlink($image_folder.$img['image']);
    }

    $conn->query("DELETE FROM employees WHERE employee_id=$id");
}

/* UPDATE */
if(isset($_POST['update'])){
    $data=$_POST;
    $id=$data['id'];

    $image_name=$data['old_image'];

    if(!empty($_FILES['image']['name'])){
        if($image_name && file_exists($image_folder.$image_name)){
            unlink($image_folder.$image_name);
        }
        $ext=pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
        $image_name=uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'],$image_folder.$image_name);
    }

    $stmt=$conn->prepare("UPDATE employees SET 
    name=?,status=?,image=?,gender=?,date_of_birth=?,nosca_item_number=?,
    place_of_assignment=?,position_title=?,salary_grade=?,
    civil_service_eligibility=?,education=?,date_of_appointment=?
    WHERE employee_id=?");

    $stmt->bind_param("ssssssssssssi",
    $data['name'],$data['status'],$image_name,
    $data['gender'],$data['date_of_birth'],$data['nosca_item_number'],
    $data['place_of_assignment'],$data['position_title'],$data['salary_grade'],
    $data['civil_service_eligibility'],$data['education'],$data['date_of_appointment'],
    $id);

    $stmt->execute();

    $successMessage = "Employee successfully updated!";
}

/* PAGINATION */
$limit=10;
$page=max(1,(int)($_GET['page']??1));
$offset=($page-1)*$limit;

$where="";
if($statusFilter){
    $where="WHERE status='".$conn->real_escape_string($statusFilter)."'";
}

$total=$conn->query("SELECT COUNT(*) total FROM employees $where")->fetch_assoc()['total'];
$totalPages=ceil($total/$limit);

$result=$conn->query("SELECT * FROM employees $where ORDER BY employee_id DESC LIMIT $limit OFFSET $offset");

/* EDIT */
$edit=false;
if(isset($_GET['edit'])){
    $id=(int)$_GET['edit'];
    $editData=$conn->query("SELECT * FROM employees WHERE employee_id=$id")->fetch_assoc();
    $edit=true;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Employee Dashboard</title>

<style>
body{font-family:Segoe UI;background:#eef2f7}
.container{max-width:1100px;margin:auto;background:#fff;padding:20px;border-radius:10px}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-top:10px}
.full{grid-column:span 2}

.form-group{display:flex;flex-direction:column}
.form-group label{
    font-size:13px;
    margin-bottom:4px;
    color:#555;
    font-weight:600;
    padding-left:5px;
}

input,select{
    width:100%;
    padding:10px 14px;
    border-radius:25px;
    border:1px solid #ccc;
    box-sizing:border-box;
}

button{
    padding:10px;
    border:none;
    border-radius:25px;
    background:#4facfe;
    color:#fff;
    cursor:pointer;
}

table{width:100%;margin-top:20px}
th,td{padding:10px;text-align:center}
tr:hover{background:#f1f5f9;cursor:pointer}

img{width:50px;height:50px;border-radius:50%}

.modal{
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,.5);
}

.modal-content{
    background:#fff;
    margin:5% auto;
    padding:20px;
    width:700px;
    max-width:95%;
    border-radius:10px;
}

.pagination{text-align:center;margin-top:20px}
.pagination a{
    display:inline-block;
    margin:4px;
    padding:8px 14px;
    border-radius:20px;
    background:#f1f5f9;
    text-decoration:none;
    color:#333;
}
.pagination a.active{
    background:#4facfe;
    color:#fff;
    font-weight:bold;
}

/* SUCCESS MODAL */
#successModal .modal-content{
    width:300px;
    text-align:center;
    padding:25px;
}
</style>
</head>

<body>
<div class="container">

<h2>Employee Management</h2>

<input type="text" id="search" placeholder="🔍 Search...">

<button onclick="openModal()">+ Add Employee</button>

<form method="GET">
<select name="status" onchange="this.form.submit()">
<option value="">All</option>
<option value="Permanent" <?=($statusFilter=="Permanent")?"selected":""?>>Permanent</option>
<option value="Contract of Service" <?=($statusFilter=="Contract of Service")?"selected":""?>>COS</option>
</select>
</form>

<div id="table-data">
<table>
<tr><th>ID</th><th>Image</th><th>Name</th><th>Status</th><th>Actions</th></tr>

<?php while($row=$result->fetch_assoc()): ?>
<tr onclick='showProfile(<?= json_encode($row) ?>)'>
<td><?= $row['employee_id'] ?></td>

<td>
<?php if($row['image'] && file_exists($image_folder.$row['image'])): ?>
<img src="<?= $image_folder.$row['image'] ?>">
<?php else: ?>
<img src="<?= $image_folder ?>default.png">
<?php endif; ?>
</td>

<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= $row['status'] ?></td>

<td>
<a href="?edit=<?= $row['employee_id'] ?>&status=<?= $statusFilter ?>">Edit</a>
<a href="?delete=<?= $row['employee_id'] ?>&status=<?= $statusFilter ?>">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<div class="pagination">
<?php for($i=1;$i<=$totalPages;$i++): ?>
<a href="?page=<?=$i?>&status=<?=$statusFilter?>" class="<?=($i==$page)?'active':''?>">
<?=$i?>
</a>
<?php endfor; ?>
</div>

</div>

<!-- MODAL FORM -->
<div id="formModal" class="modal">
<div class="modal-content">

<span onclick="closeModal()" style="float:right;cursor:pointer;">&times;</span>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $edit?$editData['employee_id']:'' ?>">
<input type="hidden" name="old_image" value="<?= $edit?$editData['image']:'' ?>">

<div class="form-grid">

<div class="form-group full">
<label>Name</label>
<input name="name" value="<?= $edit?$editData['name']:'' ?>" required>
</div>

<div class="form-group">
<label>Status</label>
<select name="status">
<option value="Permanent" <?=($edit && $editData['status']=="Permanent")?"selected":""?>>Permanent</option>
<option value="Contract of Service" <?=($edit && $editData['status']=="Contract of Service")?"selected":""?>>COS</option>
</select>
</div>

<div class="form-group">
<label>Gender</label>
<input name="gender" value="<?= $edit?$editData['gender']:'' ?>">
</div>

<div class="form-group">
<label>Date of Birth</label>
<input type="date" name="date_of_birth" value="<?= $edit?$editData['date_of_birth']:'' ?>">
</div>

<div class="form-group">
<label>NOSCA Item Number</label>
<input name="nosca_item_number" value="<?= $edit?$editData['nosca_item_number']:'' ?>">
</div>

<div class="form-group">
<label>Place of Assignment</label>
<input name="place_of_assignment" value="<?= $edit?$editData['place_of_assignment']:'' ?>">
</div>

<div class="form-group">
<label>Position Title</label>
<input name="position_title" value="<?= $edit?$editData['position_title']:'' ?>">
</div>

<div class="form-group">
<label>Salary Grade</label>
<input name="salary_grade" value="<?= $edit?$editData['salary_grade']:'' ?>">
</div>

<div class="form-group">
<label>Civil Service Eligibility</label>
<input name="civil_service_eligibility" value="<?= $edit?$editData['civil_service_eligibility']:'' ?>">
</div>

<div class="form-group">
<label>Education</label>
<input name="education" value="<?= $edit?$editData['education']:'' ?>">
</div>

<div class="form-group">
<label>Date of Appointment</label>
<input type="date" name="date_of_appointment" value="<?= $edit?$editData['date_of_appointment']:'' ?>">
</div>

<div class="form-group full">
<label>Employee Image</label>
<input type="file" name="image">
</div>

<?php if($edit): ?>
<button name="update" class="full">Update</button>
<?php else: ?>
<button name="add" class="full">Add</button>
<?php endif; ?>

</div>
</form>

</div>
</div>

<!-- SUCCESS MODAL -->
<div id="successModal" class="modal">
<div class="modal-content">
<h3 id="successText"></h3>
<button onclick="closeSuccess()">OK</button>
</div>
</div>

<script>
function openModal(){formModal.style.display="block"}
function closeModal(){formModal.style.display="none"}

function showSuccess(msg){
document.getElementById("successText").innerText=msg;
document.getElementById("successModal").style.display="block";
}
function closeSuccess(){
document.getElementById("successModal").style.display="none";
}

<?php if($edit): ?>
document.getElementById("formModal").style.display="block";
<?php endif; ?>

<?php if(!empty($successMessage)): ?>
showSuccess("<?= $successMessage ?>");
<?php endif; ?>
</script>

</body>
</html>