    <?php
    include 'config.php';

    $success = "";
    $error = "";

    /* CREATE UPLOAD FOLDER IF NOT EXIST */
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $title = $_POST['title'];
        $message = $_POST['message'];
        $deadline = $_POST['deadline'];
        $urgency = $_POST['urgency'];

        $file_path = NULL;

        /* FILE UPLOAD */
        if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != "") {

            $fileName = time() . "_" . basename($_FILES["attachment"]["name"]);
            $targetFile = $uploadDir . $fileName;

            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // allowed file types
            $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (in_array($fileType, $allowedTypes)) {

                if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFile)) {
                    $file_path = $targetFile;
                } else {
                    $error = "❌ Failed to upload file.";
                }

            } else {
                $error = "❌ Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.";
            }
        }

        /* INSERT DATA IF NO ERROR */
        if (!$error) {

            $sql = "INSERT INTO announcements (title, message, deadline, urgency, file_path)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $title, $message, $deadline, $urgency, $file_path);

            if ($stmt->execute()) {
                $success = "✅ Announcement created successfully!";
            } else {
                $error = "❌ Something went wrong: " . $stmt->error;
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement</title>

    <style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family: 'Segoe UI', sans-serif;
    }

    body{
        min-height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        background: linear-gradient(135deg, #0f172a, #1e3a8a, #2563eb);
        padding:20px;
    }

    .container{
        width:100%;
        max-width:600px;
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn{
        from{opacity:0; transform:translateY(20px);}
        to{opacity:1; transform:translateY(0);}
    }

    .card{
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding:40px;
        border-radius:20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    }

    .card h2{
        font-size:30px;
        color:#0f172a;
    }

    .subtitle{
        font-size:14px;
        color:#64748b;
        margin-bottom:25px;
    }

    .form-group{
        margin-bottom:18px;
    }

    label{
        display:block;
        margin-bottom:6px;
        font-weight:600;
        color:#334155;
    }

    input, textarea, select{
        width:100%;
        padding:14px;
        border-radius:12px;
        border:1px solid #e2e8f0;
        background:#f8fafc;
        transition:0.3s;
        font-size:15px;
    }

    input:focus,
    textarea:focus,
    select:focus{
        outline:none;
        border-color:#2563eb;
        box-shadow:0 0 0 4px rgba(37,99,235,0.15);
        background:#fff;
    }

    textarea{
        resize:none;
    }

    .counter{
        font-size:12px;
        text-align:right;
        color:#64748b;
        margin-top:5px;
    }

    /* BUTTON */
    .btn{
        width:100%;
        padding:14px;
        border:none;
        border-radius:12px;
        background: linear-gradient(135deg,#2563eb,#1d4ed8);
        color:white;
        font-size:16px;
        font-weight:600;
        cursor:pointer;
        transition:0.3s;
    }

    .btn:hover{
        transform:translateY(-2px);
        box-shadow:0 10px 20px rgba(37,99,235,0.3);
    }

    /* ALERT */
    .alert{
        padding:12px;
        border-radius:10px;
        margin-bottom:15px;
        font-size:14px;
    }

    .success{
        background:#dcfce7;
        color:#166534;
    }

    .error{
        background:#fee2e2;
        color:#991b1b;
    }

    /* URGENCY */
    .preview{
        font-size:13px;
        margin-top:5px;
        font-weight:600;
    }

    .light{color:#16a34a;}
    .medium{color:#d97706;}
    .urgent{color:#dc2626;}

    @media(max-width:500px){
        .card{padding:25px;}
    }
    </style>
    </head>

    <body>

    <div class="container">
        <div class="card">

            <h2>📢 Create Announcement</h2>
            <p class="subtitle">Publish important updates to your employees instantly</p>

            <?php if($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea id="message" name="message" rows="6" maxlength="5000" required></textarea>
                    <div class="counter" id="counter">0 / 5000</div>
                </div>

                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" required>
                </div>

                <div class="form-group">
                    <label>Urgency Level</label>
                    <select name="urgency" id="urgency" onchange="updateUrgency()">
                        <option value="light">🟢 Light</option>
                        <option value="medium">🟠 Medium</option>
                        <option value="urgent">🔴 Urgent</option>
                    </select>
                    <div class="preview" id="preview">Selected: 🟢 Light</div>
                </div>

                <!-- FILE ATTACHMENT -->
                <div class="form-group">
                    <label>Attachment (Optional)</label>
                    <input type="file" name="attachment">
                    <small style="color:#64748b;">Allowed: PDF, DOC, DOCX, JPG, PNG</small>
                </div>

                <button type="submit" class="btn">🚀 Create Announcement</button>

            </form>

        </div>
    </div>

    <script>
    const message = document.getElementById("message");
    const counter = document.getElementById("counter");
    const maxLength = 5000;

    message.addEventListener("input", () => {
        counter.textContent = `${message.value.length} / ${maxLength}`;
    });

    function updateUrgency(){
        const urgency = document.getElementById("urgency");
        const preview = document.getElementById("preview");

        const selectedText = urgency.options[urgency.selectedIndex].text;
        preview.textContent = "Selected: " + selectedText;

        preview.className = "preview " + urgency.value;
    }
    </script>

    </body>
    </html>