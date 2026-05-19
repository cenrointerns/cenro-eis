<?php
include 'config.php';

$success = "";
$error   = "";

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // First get the file path to delete the file from server
    $sql_file = "SELECT file_path FROM announcements WHERE id = ?";
    $stmt_file = $conn->prepare($sql_file);
    $stmt_file->bind_param("i", $delete_id);
    $stmt_file->execute();
    $result_file = $stmt_file->get_result();
    if ($row = $result_file->fetch_assoc()) {
        if ($row['file_path'] && file_exists($row['file_path'])) {
            unlink($row['file_path']); // Delete the file from server
        }
    }
    $stmt_file->close();
    
    // Delete the announcement
    $sql_delete = "DELETE FROM announcements WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);
    
    if ($stmt_delete->execute()) {
        $success = "Announcement deleted successfully!";
    } else {
        $error = "Failed to delete announcement: " . $stmt_delete->error;
    }
    $stmt_delete->close();
}

// Handle Edit Request (fetch data for editing)
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $sql_edit = "SELECT * FROM announcements WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    $edit_data = $result_edit->fetch_assoc();
    $stmt_edit->close();
}

// Handle Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $title    = $_POST['title'];
    $message  = $_POST['message'];
    $deadline = $_POST['deadline'];
    $urgency  = $_POST['urgency'];
    $file_path = $_POST['existing_file'] ?: NULL;
    
    // Handle new file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != "") {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName   = time() . "_" . basename($_FILES["attachment"]["name"]);
        $targetFile = $uploadDir . $fileName;
        $fileType   = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['pdf','doc','docx','jpg','jpeg','png'];
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFile)) {
                // Delete old file if exists
                if ($_POST['existing_file'] && file_exists($_POST['existing_file'])) {
                    unlink($_POST['existing_file']);
                }
                $file_path = $targetFile;
            } else {
                $error = "Failed to upload new file.";
            }
        } else {
            $error = "Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.";
        }
    }
    
    if (!$error) {
        $sql_update = "UPDATE announcements SET title = ?, message = ?, deadline = ?, urgency = ?, file_path = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssi", $title, $message, $deadline, $urgency, $file_path, $update_id);
        
        if ($stmt_update->execute()) {
            $success = "Announcement updated successfully!";
            header("Location: manage_announcements.php");
            exit();
        } else {
            $error = "Update failed: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

// Fetch all announcements
$sql = "SELECT * FROM announcements ORDER BY 
        CASE urgency 
            WHEN 'urgent' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'light' THEN 3 
        END, 
        deadline ASC, 
        id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Announcements · DENR Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --forest:     #0b5d3b;
    --forest-dk:  #084529;
    --forest-lt:  #e8f5ee;
    --leaf:       #22c55e;
    --cream:      #faf9f6;
    --paper:      #ffffff;
    --mist:       #f0f4f2;
    --stone:      #8a9a90;
    --pebble:     #c8d6cd;
    --ink:        #1a2820;
    --charcoal:   #3d4f45;
    --amber:      #d97706;
    --amber-lt:   #fef3c7;
    --red:        #dc2626;
    --red-lt:     #fee2e2;
    --green:      #16a34a;
    --green-lt:   #dcfce7;
    --radius-sm:  10px;
    --radius-md:  16px;
    --radius-lg:  24px;
    --transition: 0.25s cubic-bezier(0.4,0,0.2,1);
}

*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--ink);
    min-height: 100vh;
    padding: 48px 20px 80px;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: radial-gradient(ellipse 70% 50% at 0% 30%, rgba(11,93,59,0.06), transparent);
    pointer-events: none;
    z-index: 0;
}

.page-wrap {
    position: relative;
    z-index: 1;
    max-width: 1400px;
    margin: 0 auto;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--stone);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    padding: 8px 0;
    margin-bottom: 28px;
    transition: color var(--transition);
}

.back-link:hover { color: var(--forest); }

.page-title {
    margin-bottom: 32px;
}

.page-title .eyebrow {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--stone);
    margin-bottom: 8px;
}

.page-title h1 {
    font-family: 'Fraunces', serif;
    font-size: 38px;
    font-weight: 600;
    color: var(--forest);
    line-height: 1.1;
}

.page-title p {
    margin-top: 8px;
    font-size: 15px;
    color: var(--stone);
}

.new-btn {
    margin-top: 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--forest);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-sm);
    font-weight: 500;
    transition: all var(--transition);
}

.new-btn:hover {
    background: var(--forest-dk);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(11,93,59,0.2);
}

.alert {
    padding: 16px 28px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    border-radius: var(--radius-md);
    margin-bottom: 30px;
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
}

.alert.success {
    background: var(--green-lt);
    color: var(--green);
    border-left: 4px solid var(--green);
}

.alert.error {
    background: var(--red-lt);
    color: var(--red);
    border-left: 4px solid var(--red);
}

.announcements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
}

.announcement-card {
    background: var(--paper);
    border-radius: var(--radius-lg);
    border: 1px solid var(--pebble);
    overflow: hidden;
    transition: all var(--transition);
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

.announcement-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.card-header {
    padding: 20px 24px;
    background: var(--mist);
    border-bottom: 1px solid var(--pebble);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.urgency-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.urgency-light {
    background: var(--green-lt);
    color: #14532d;
}

.urgency-medium {
    background: var(--amber-lt);
    color: #92400e;
}

.urgency-urgent {
    background: var(--red-lt);
    color: #7f1d1d;
}

.deadline {
    font-size: 12px;
    color: var(--stone);
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
}

.card-body {
    padding: 24px;
}

.card-title {
    font-family: 'Fraunces', serif;
    font-size: 20px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 12px;
    line-height: 1.3;
}

.card-message {
    color: var(--charcoal);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
}

.attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--mist);
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--forest);
    text-decoration: none;
    margin-top: 12px;
    transition: all var(--transition);
}

.attachment-link:hover {
    background: var(--forest-lt);
    color: var(--forest-dk);
}

.card-actions {
    padding: 16px 24px;
    background: var(--cream);
    border-top: 1px solid var(--pebble);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-edit, .btn-delete {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all var(--transition);
    cursor: pointer;
    border: none;
}

.btn-edit {
    background: var(--mist);
    color: var(--forest);
}

.btn-edit:hover {
    background: var(--forest);
    color: white;
    transform: translateY(-1px);
}

.btn-delete {
    background: var(--red-lt);
    color: var(--red);
}

.btn-delete:hover {
    background: var(--red);
    color: white;
    transform: translateY(-1px);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: var(--paper);
    border-radius: var(--radius-lg);
    border: 2px dashed var(--pebble);
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-family: 'Fraunces', serif;
    color: var(--stone);
    margin-bottom: 12px;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal.active {
    display: flex;
}

.modal-content {
    background: var(--paper);
    border-radius: var(--radius-lg);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease-out;
}

.modal-header {
    padding: 20px 24px;
    background: var(--mist);
    border-bottom: 1px solid var(--pebble);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-family: 'Fraunces', serif;
    color: var(--forest);
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: var(--stone);
    transition: color var(--transition);
}

.modal-close:hover {
    color: var(--red);
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--charcoal);
    margin-bottom: 8px;
}

.form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--pebble);
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.modal-footer {
    padding: 16px 24px;
    background: var(--cream);
    border-top: 1px solid var(--pebble);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-save, .btn-cancel {
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    font-weight: 500;
    cursor: pointer;
    border: none;
}

.btn-save {
    background: var(--forest);
    color: white;
}

.btn-save:hover {
    background: var(--forest-dk);
}

.btn-cancel {
    background: var(--mist);
    color: var(--stone);
}

.btn-cancel:hover {
    background: var(--pebble);
}

@media (max-width: 768px) {
    .announcements-grid {
        grid-template-columns: 1fr;
    }
    
    .page-title h1 {
        font-size: 28px;
    }
}
</style>
</head>
<body>

<div class="page-wrap">
    <a href="index.php" class="back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Back to Dashboard
    </a>

    <div class="page-title">
        <div class="eyebrow">Administration</div>
        <h1>Manage Announcements</h1>
        <p>Review, edit, or remove existing announcements</p>
        <a href="index.php" class="new-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            New Announcement
        </a>
    </div>

    <?php if ($success): ?>
    <div class="alert success">
        <span>✅</span>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert error">
        <span>⚠️</span>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="announcements-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="announcement-card">
                    <div class="card-header">
                        <div>
                            <div class="urgency-badge urgency-<?= $row['urgency'] ?>">
                                <?php if ($row['urgency'] == 'urgent'): ?>🔴
                                <?php elseif ($row['urgency'] == 'medium'): ?>🟠
                                <?php else: ?>🟢
                                <?php endif; ?>
                                <?= ucfirst($row['urgency']) ?>
                            </div>
                            <div class="deadline">
                                📅 Deadline: <?= date('F j, Y', strtotime($row['deadline'])) ?>
                            </div>
                        </div>
                        <div class="card-id" style="font-size: 11px; color: var(--stone);">
                            ID: <?= $row['id'] ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($row['title']) ?></h3>
                        <div class="card-message">
                            <?= nl2br(htmlspecialchars(substr($row['message'], 0, 200))) ?>
                            <?php if (strlen($row['message']) > 200): ?>...<?php endif; ?>
                        </div>
                        <?php if ($row['file_path']): ?>
                            <a href="<?= htmlspecialchars($row['file_path']) ?>" class="attachment-link" target="_blank">
                                📎 View Attachment
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-actions">
                        <button class="btn-edit" onclick="openEditModal(<?= $row['id'] ?>)">
                            ✏️ Edit
                        </button>
                        <button class="btn-delete" onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>')">
                            🗑️ Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No announcements found</h3>
                <p style="color: var(--stone);">Create your first announcement to get started.</p>
                <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: var(--forest); color: white; text-decoration: none; border-radius: var(--radius-sm);">Create Announcement →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Edit Announcement</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="update_id" id="edit_id">
            <input type="hidden" name="existing_file" id="existing_file">
            <div class="modal-body">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="edit_title" required maxlength="120">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" id="edit_message" required maxlength="5000"></textarea>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" id="edit_deadline" required>
                </div>
                <div class="form-group">
                    <label>Urgency Level</label>
                    <select name="urgency" id="edit_urgency" required>
                        <option value="light">🟢 Light - General info</option>
                        <option value="medium">🟠 Medium - Needs attention</option>
                        <option value="urgent">🔴 Urgent - Immediate action</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Attachment (Optional)</label>
                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <p style="font-size: 12px; color: var(--stone); margin-top: 5px;" id="current_file">Current file: None</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id) {
    // Fetch announcement data via AJAX
    fetch(`get_announcement.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_title').value = data.title;
                document.getElementById('edit_message').value = data.message;
                document.getElementById('edit_deadline').value = data.deadline;
                document.getElementById('edit_urgency').value = data.urgency;
                document.getElementById('existing_file').value = data.file_path || '';
                const currentFileSpan = document.getElementById('current_file');
                if (data.file_path) {
                    currentFileSpan.innerHTML = `Current file: <a href="${data.file_path}" target="_blank" style="color: var(--forest);">View attachment</a>`;
                } else {
                    currentFileSpan.innerHTML = 'Current file: None';
                }
                document.getElementById('editModal').classList.add('active');
            } else {
                alert('Failed to load announcement data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading announcement');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function confirmDelete(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        window.location.href = `manage_announcements.php?delete_id=${id}`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
}
</script>

</body>
</html>