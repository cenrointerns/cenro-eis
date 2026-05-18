<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* MARK AS READ */
if(isset($_POST['mark_read_id'])){
    $id = intval($_POST['mark_read_id']);
    $conn->query("UPDATE announcements SET is_read = 1 WHERE id = $id");
    exit();
}

$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nature Announcements</title>

<style>
body {
    margin: 0;
    font-family: "Segoe UI", sans-serif;
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9, #e0f2f1);
}

.header {
    background: linear-gradient(135deg, #1b5e20, #2e7d32, #388e3c);
    color: white;
    padding: 22px;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
}

.container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

.card {
    background: rgba(255,255,255,0.92);
    border-radius: 18px;
    padding: 18px;
    margin-bottom: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    position: relative;
    border-left: 6px solid #81c784;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-3px);
}

.title {
    font-size: 18px;
    font-weight: bold;
    color: #1b5e20;
}

.message {
    font-size: 14px;
    color: #333;
    margin-top: 8px;
}

.meta {
    font-size: 12px;
    color: #607d8b;
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 12px;
    font-size: 11px;
    border-radius: 20px;
    color: white;
}

.light { background: #66bb6a; }
.medium { background: #ffa726; }
.urgent { background: #ef5350; }

.unread {
    border-left: 6px solid #2e7d32;
}

.btn {
    margin-top: 12px;
    padding: 7px 14px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
}

.read-btn {
    background: #2e7d32;
    color: white;
}

.more-btn {
    background: #607d8b;
    color: white;
    margin-left: 8px;
}

/* FILE BUTTON */
.file-btn {
    background: #1565c0;
    color: white;
    margin-left: 8px;
}

.file-btn:hover {
    background: #0d47a1;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: #fff;
    width: 90%;
    max-width: 650px;
    margin: 8% auto;
    padding: 20px;
    border-radius: 15px;
}

.close {
    float: right;
    font-size: 20px;
    cursor: pointer;
}

.modal-title {
    font-size: 20px;
    font-weight: bold;
    color: #1b5e20;
}

.modal-message {
    margin-top: 10px;
    font-size: 14px;
    line-height: 1.7;
    white-space: pre-wrap;
}

.modal-file {
    margin-top: 15px;
}
</style>
</head>

<body>

<div class="header">Announcements</div>

<div class="container">

<?php while($row = $result->fetch_assoc()): ?>

<div class="card <?php echo $row['is_read'] ? '' : 'unread'; ?>">

    <div class="badge <?php echo $row['urgency']; ?>">
        <?php echo strtoupper($row['urgency']); ?>
    </div>

    <div class="title">🌱 <?php echo htmlspecialchars($row['title']); ?></div>

    <div class="message">
        <?php echo nl2br(htmlspecialchars(substr($row['message'], 0, 120))); ?>...
    </div>

    <div class="meta">
        <span>📅 <?php echo $row['deadline']; ?></span>
        <span>🕒 <?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
    </div>

    <!-- READ MORE -->
    <button class="btn more-btn"
        data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>"
        data-message="<?php echo htmlspecialchars($row['message'], ENT_QUOTES); ?>"
        data-file="<?php echo htmlspecialchars($row['file_path'], ENT_QUOTES); ?>"
        onclick="openModal(this)">
        Read More
    </button>

    <?php if(!empty($row['file_path'])): ?>
        <a class="btn file-btn" href="<?php echo $row['file_path']; ?>" target="_blank">
            📎 View File
        </a>
    <?php endif; ?>

    <?php if(!$row['is_read']): ?>
        <button class="btn read-btn" onclick="markRead(<?php echo $row['id']; ?>, this)">
            Mark as Read
        </button>
    <?php else: ?>
        <button class="btn read-btn" disabled>✔ Read</button>
    <?php endif; ?>

</div>

<?php endwhile; ?>

</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <div class="modal-title" id="modalTitle"></div>
        <div class="modal-message" id="modalMessage"></div>

        <div class="modal-file" id="modalFile"></div>
    </div>
</div>

<script>

function markRead(id, btn){
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "announcements.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onload = function(){
        if(this.status == 200){
            btn.innerHTML = "✔ Read";
            btn.disabled = true;
            btn.parentElement.classList.remove("unread");
        }
    };

    xhr.send("mark_read_id=" + id);
}

function openModal(btn){
    let title = btn.getAttribute("data-title");
    let message = btn.getAttribute("data-message");
    let file = btn.getAttribute("data-file");

    document.getElementById("modalTitle").innerHTML = "🌿 " + title;
    document.getElementById("modalMessage").innerHTML = message.replace(/\n/g, "<br>");

    let fileBox = document.getElementById("modalFile");

    if(file && file !== "null"){
        fileBox.innerHTML = `
            <a href="${file}" target="_blank" class="btn file-btn">
                📎 Open Attachment
            </a>
        `;
    } else {
        fileBox.innerHTML = "";
    }

    document.getElementById("modal").style.display = "block";
}

function closeModal(){
    document.getElementById("modal").style.display = "none";
}

window.onclick = function(event){
    let modal = document.getElementById("modal");
    if(event.target == modal){
        modal.style.display = "none";
    }
}
</script>

</body>
</html>