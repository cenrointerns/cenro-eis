<?php
include 'config.php';
if ($conn->connect_error) die("Connection failed");

$image_folder = "assets/image/employee/";
if (!file_exists($image_folder)) mkdir($image_folder, 0777, true);

$successMessage = "";

/* FILTER */
$statusFilter = $_GET['status'] ?? "";

/* CREATE */
if(isset($_POST['add'])){
    $data = $_POST;
    $image_name = null;
    if(!empty($_FILES['image']['name'])){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_folder.$image_name);
    }
    $length_of_service = null;
    if(!empty($data['date_of_appointment'])){
        $start = new DateTime($data['date_of_appointment']);
        $now = new DateTime();
        $diff = $start->diff($now);
        $length_of_service = $diff->y . " years " . $diff->m . " months";
    }
    $stmt = $conn->prepare("INSERT INTO employees 
    (name,status,image,gender,date_of_birth,nosca_item_number,assigned_section,
    position_title,monthly_salary,civil_service_eligibility,education,date_of_appointment,
    length_of_service,date_of_last_promotion,step_increment,salary_grade)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssssssssssssss",
        $data['name'],$data['status'],$image_name,$data['gender'],
        $data['date_of_birth'],$data['nosca_item_number'],$data['assigned_section'],
        $data['position_title'],$data['monthly_salary'],$data['civil_service_eligibility'],
        $data['education'],$data['date_of_appointment'],$length_of_service,
        $data['date_of_last_promotion'],$data['step_increment'],$data['salary_grade']
    );
    $stmt->execute();
    $successMessage = "Employee successfully added!";
}

/* DELETE */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $img = $conn->query("SELECT image FROM employees WHERE employee_id=$id")->fetch_assoc();
    if($img && $img['image'] && file_exists($image_folder.$img['image'])){
        unlink($image_folder.$img['image']);
    }
    $conn->query("DELETE FROM employees WHERE employee_id=$id");
}

/* UPDATE */
if(isset($_POST['update'])){
    $data = $_POST;
    $id = $data['id'];
    $image_name = $data['old_image'];
    if(!empty($_FILES['image']['name'])){
        if($image_name && file_exists($image_folder.$image_name)) unlink($image_folder.$image_name);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_folder.$image_name);
    }
    $length_of_service = null;
    if(!empty($data['date_of_appointment'])){
        $start = new DateTime($data['date_of_appointment']);
        $now = new DateTime();
        $diff = $start->diff($now);
        $length_of_service = $diff->y . " years " . $diff->m . " months";
    }
    $stmt = $conn->prepare("UPDATE employees SET 
    name=?,status=?,image=?,gender=?,date_of_birth=?,nosca_item_number=?,
    assigned_section=?,position_title=?,monthly_salary=?,
    civil_service_eligibility=?,education=?,date_of_appointment=?,
    length_of_service=?,date_of_last_promotion=?,step_increment=?,salary_grade=?
    WHERE employee_id=?");
    $stmt->bind_param(
        "ssssssssssssssssi",
        $data['name'],$data['status'],$image_name,$data['gender'],
        $data['date_of_birth'],$data['nosca_item_number'],$data['assigned_section'],
        $data['position_title'],$data['monthly_salary'],$data['civil_service_eligibility'],
        $data['education'],$data['date_of_appointment'],$length_of_service,
        $data['date_of_last_promotion'],$data['step_increment'],$data['salary_grade'],$id
    );
    $stmt->execute();
    $successMessage = "Employee successfully updated!";
}

/* PAGINATION */
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where = "";
if($statusFilter){
    $where = "WHERE status='".$conn->real_escape_string($statusFilter)."'";
}

$total = $conn->query("SELECT COUNT(*) total FROM employees $where")->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
$result = $conn->query("SELECT * FROM employees $where ORDER BY name ASC LIMIT $limit OFFSET $offset");

/* EDIT */
$edit = false;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $editData = $conn->query("SELECT * FROM employees WHERE employee_id=$id")->fetch_assoc();
    $edit = true;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Employee Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: #f0f4f8;
    color: #1a2332;
    font-size: 14px;
    min-height: 100vh;
}

/* ── PAGE WRAPPER ── */
.page {
    max-width: 1100px;
    margin: 32px auto;
    padding: 0 16px 60px;
}

/* ── HEADER BAR ── */
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.header h2 {
    font-size: 22px;
    font-weight: 600;
    color: #1a2332;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ── BUTTONS ── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, transform 0.1s;
    white-space: nowrap;
}
.btn:active { transform: scale(0.97); }

.btn-primary {
    background: #3b82f6;
    color: #fff;
}
.btn-primary:hover { background: #2563eb; }

.btn-secondary {
    background: #e2e8f0;
    color: #374151;
}
.btn-secondary:hover { background: #cbd5e1; }

.btn-success {
    background: #10b981;
    color: #fff;
    width: 100%;
    justify-content: center;
    padding: 12px;
    font-size: 15px;
    border-radius: 10px;
    margin-top: 8px;
}
.btn-success:hover { background: #059669; }

.btn-edit {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    padding: 5px 12px;
    font-size: 12px;
    border-radius: 6px;
}
.btn-edit:hover { background: #dbeafe; }

.btn-delete {
    background: #fff5f5;
    color: #dc2626;
    border: 1px solid #fecaca;
    padding: 5px 12px;
    font-size: 12px;
    border-radius: 6px;
}
.btn-delete:hover { background: #fee2e2; }

/* ── FILTER BAR ── */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    border: 1px solid #e2e8f0;
}

.filter-bar label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
    white-space: nowrap;
}

.filter-bar select {
    padding: 7px 12px;
    border-radius: 7px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    color: #374151;
    font-size: 13px;
    cursor: pointer;
}
.filter-bar select:focus { outline: 2px solid #3b82f6; }

/* ── TABLE CARD ── */
.table-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background: #f8fafc;
    padding: 13px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e2e8f0;
}

thead th:first-child,
thead th:nth-child(2) { text-align: center; }

tbody tr {
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.12s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f8fafc; }

tbody td {
    padding: 13px 16px;
    color: #374151;
    vertical-align: middle;
}

tbody td:first-child { text-align: center; color: #9ca3af; font-size: 13px; }
tbody td:nth-child(2) { text-align: center; }
tbody td:nth-child(5) { text-align: center; }

.employee-name { font-weight: 600; color: #1a2332; }

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.badge-permanent { background: #d1fae5; color: #065f46; }
.badge-cos { background: #fef3c7; color: #92400e; }

.avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.action-btns { display: flex; gap: 6px; justify-content: center; }

/* ── PAGINATION ── */
.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 20px;
}

.pagination a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.12s;
}
.pagination a:hover { background: #f1f5f9; }
.pagination a.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }

/* ── SLIDE-IN DRAWER ── */
.drawer-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 100;
    backdrop-filter: blur(2px);
}

.drawer {
    position: fixed;
    top: 0;
    right: -480px;
    width: 460px;
    max-width: 100vw;
    height: 100vh;
    background: #fff;
    z-index: 101;
    display: flex;
    flex-direction: column;
    transition: right 0.3s cubic-bezier(.4,0,.2,1);
    box-shadow: -8px 0 32px rgba(0,0,0,0.12);
}

.drawer.open { right: 0; }

.drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.drawer-header h3 {
    font-size: 17px;
    font-weight: 600;
    color: #1a2332;
}

.drawer-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    cursor: pointer;
    font-size: 18px;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s;
}
.drawer-close:hover { background: #f1f5f9; color: #374151; }

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.drawer-footer {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
    background: #f8fafc;
}

/* ── FORM SECTIONS ── */
.form-section {
    margin-bottom: 24px;
}

.form-section-title {
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

.form-row.single { grid-template-columns: 1fr; }

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select {
    padding: 9px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #1a2332;
    font-size: 14px;
    width: 100%;
    transition: border-color 0.12s, box-shadow 0.12s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.form-group input[type="file"] {
    padding: 7px 10px;
    background: #f9fafb;
    cursor: pointer;
}

/* ── SUCCESS MODAL ── */
.success-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 200;
    align-items: center;
    justify-content: center;
}

.success-overlay.show { display: flex; }

.success-card {
    background: #fff;
    border-radius: 14px;
    padding: 32px 40px;
    text-align: center;
    max-width: 320px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.success-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #d1fae5;
    color: #065f46;
    font-size: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.success-card h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1a2332;
    margin-bottom: 20px;
}

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.empty-state p { font-size: 15px; }
</style>
</head>

<body>
<div class="page">

    <!-- HEADER -->
    <div class="header">
        <h2>&#128101; Employee Management</h2>
        <div class="header-actions">
            <a href="dashboard.php" class="btn btn-secondary">&#8592; Go Back</a>
            <button class="btn btn-primary" onclick="openDrawer()">&#43; Add Employee</button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <label>Filter by status:</label>
        <form method="GET" style="display:flex;align-items:center;gap:8px;">
            <select name="status" onchange="this.form.submit()">
                <option value="">All Employees</option>
                <option value="Permanent" <?=($statusFilter=="Permanent")?"selected":""?>>Permanent</option>
                <option value="Contract of Service" <?=($statusFilter=="Contract of Service")?"selected":""?>>Contract of Service</option>
            </select>
            <?php if($statusFilter): ?>
            <a href="?" class="btn btn-secondary" style="padding:6px 12px;font-size:13px;">&#215; Clear</a>
            <?php endif; ?>
        </form>
        <span style="margin-left:auto;color:#6b7280;font-size:13px;"><?= $total ?> record<?= $total != 1 ? 's' : '' ?></span>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th style="width:70px;">Photo</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th style="width:140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result->num_rows === 0): ?>
                <tr><td colspan="5"><div class="empty-state"><p>No employees found.</p></div></td></tr>
            <?php else: ?>
            <?php while($row=$result->fetch_assoc()): ?>
            <tr onclick='showProfile(<?= json_encode($row) ?>)'>
                <td><?= $row['employee_id'] ?></td>
                <td>
                    <?php if($row['image'] && file_exists($image_folder.$row['image'])): ?>
                    <img class="avatar" src="<?= $image_folder.$row['image'] ?>" alt="Photo">
                    <?php else: ?>
                    <img class="avatar" src="<?= $image_folder ?>default.png" alt="No photo">
                    <?php endif; ?>
                </td>
                <td><span class="employee-name"><?= htmlspecialchars($row['name']) ?></span></td>
                <td>
                    <?php if($row['status'] === 'Permanent'): ?>
                    <span class="status-badge badge-permanent">Permanent</span>
                    <?php else: ?>
                    <span class="status-badge badge-cos">COS</span>
                    <?php endif; ?>
                </td>
                <td onclick="event.stopPropagation()">
                    <div class="action-btns">
                        <a href="?edit=<?= $row['employee_id'] ?>&status=<?= $statusFilter ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?= $row['employee_id'] ?>&status=<?= $statusFilter ?>"
                           onclick="return confirm('Delete <?= htmlspecialchars(addslashes($row['name'])) ?>?')"
                           class="btn btn-delete">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
        <a href="?page=<?=$i?>&status=<?=$statusFilter?>" class="<?=($i==$page)?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<!-- DRAWER OVERLAY -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- SLIDE-IN DRAWER FORM -->
<div class="drawer" id="formDrawer">

    <div class="drawer-header">
        <h3><?= $edit ? '&#9998; Edit Employee' : '&#43; Add New Employee' ?></h3>
        <button class="drawer-close" onclick="closeDrawer()">&#215;</button>
    </div>

    <div class="drawer-body">
        <form method="POST" enctype="multipart/form-data" id="empForm">
            <input type="hidden" name="id" value="<?= $edit?$editData['employee_id']:'' ?>">
            <input type="hidden" name="old_image" value="<?= $edit?$editData['image']:'' ?>">

            <!-- SECTION: Basic Info -->
            <div class="form-section">
                <div class="form-section-title">Basic Information</div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input name="name" value="<?= $edit?htmlspecialchars($editData['name']):'' ?>" required placeholder="e.g. Juan Dela Cruz">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Permanent" <?=($edit && $editData['status']=="Permanent")?"selected":""?>>Permanent</option>
                            <option value="Contract of Service" <?=($edit && $editData['status']=="Contract of Service")?"selected":""?>>Contract of Service</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select gender</option>
                            <option value="Male" <?=($edit && $editData['gender']=="Male")?"selected":""?>>Male</option>
                            <option value="Female" <?=($edit && $editData['gender']=="Female")?"selected":""?>>Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= $edit?$editData['date_of_birth']:'' ?>">
                    </div>
                    <div class="form-group">
                        <label>NOSCA Item No.</label>
                        <input name="nosca_item_number" value="<?= $edit?htmlspecialchars($editData['nosca_item_number']):'' ?>" placeholder="Item number">
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Employee Photo</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- SECTION: Position -->
            <div class="form-section">
                <div class="form-section-title">Position & Assignment</div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Place of Assignment</label>
                        <input name="assigned_section" value="<?= $edit?htmlspecialchars($editData['assigned_section']):'' ?>" placeholder="e.g. Administrative Division">
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Position Title</label>
                        <input name="position_title" value="<?= $edit?htmlspecialchars($editData['position_title']):'' ?>" placeholder="e.g. Administrative Officer II">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Salary Grade</label>
                        <input type="number" name="salary_grade" value="<?= $edit?$editData['salary_grade']:'' ?>" placeholder="e.g. 15">
                    </div>
                    <div class="form-group">
                        <label>Monthly Salary</label>
                        <input name="monthly_salary" value="<?= $edit?htmlspecialchars($editData['monthly_salary']):'' ?>" placeholder="e.g. 32000">
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Step Increment</label>
                        <input name="step_increment" value="<?= $edit?htmlspecialchars($editData['step_increment']):'' ?>" placeholder="e.g. Step 3">
                    </div>
                </div>
            </div>

            <!-- SECTION: Qualifications -->
            <div class="form-section">
                <div class="form-section-title">Qualifications</div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Highest Education</label>
                        <input name="education" value="<?= $edit?htmlspecialchars($editData['education']):'' ?>" placeholder="e.g. Bachelor of Science in...">
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>Civil Service Eligibility</label>
                        <input name="civil_service_eligibility" value="<?= $edit?htmlspecialchars($editData['civil_service_eligibility']):'' ?>" placeholder="e.g. Career Service Professional">
                    </div>
                </div>
            </div>

            <!-- SECTION: Service Dates -->
            <div class="form-section">
                <div class="form-section-title">Service Dates</div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Appointment</label>
                        <input type="date" name="date_of_appointment" value="<?= $edit?$editData['date_of_appointment']:'' ?>">
                    </div>
                    <div class="form-group">
                        <label>Date of Last Promotion</label>
                        <input type="date" name="date_of_last_promotion" value="<?= $edit?$editData['date_of_last_promotion']:'' ?>">
                    </div>
                </div>

                <p style="font-size:12px;color:#9ca3af;margin-top:-6px;">&#9432; Length of service is computed automatically.</p>
            </div>

        </form>
    </div>

    <div class="drawer-footer">
        <?php if($edit): ?>
        <button type="submit" name="update" form="empForm" class="btn btn-success">
            &#10003; Save Changes
        </button>
        <?php else: ?>
        <button type="submit" name="add" form="empForm" class="btn btn-success">
            &#43; Add Employee
        </button>
        <?php endif; ?>
    </div>

</div>

<!-- SUCCESS MODAL -->
<div class="success-overlay" id="successOverlay">
    <div class="success-card">
        <div class="success-icon">&#10003;</div>
        <h3 id="successText"></h3>
        <button class="btn btn-primary" style="width:100%;justify-content:center;" onclick="closeSuccess()">Done</button>
    </div>
</div>

<script>
const drawer = document.getElementById('formDrawer');
const overlay = document.getElementById('drawerOverlay');

function openDrawer() {
    drawer.classList.add('open');
    overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeDrawer() {
    drawer.classList.remove('open');
    overlay.style.display = 'none';
    document.body.style.overflow = '';
}

function showSuccess(msg) {
    document.getElementById('successText').innerText = msg;
    document.getElementById('successOverlay').classList.add('show');
}

function closeSuccess() {
    document.getElementById('successOverlay').classList.remove('show');
}

<?php if($edit): ?>
openDrawer();
<?php endif; ?>

<?php if(!empty($successMessage)): ?>
showSuccess("<?= $successMessage ?>");
<?php endif; ?>
</script>

</body>
</html>
