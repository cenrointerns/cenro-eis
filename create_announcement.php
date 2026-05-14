<?php
include 'config.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $message = $_POST['message'];
    $deadline = $_POST['deadline'];
    $urgency = $_POST['urgency'];

    $sql = "INSERT INTO announcements (title, message, deadline, urgency)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $title, $message, $deadline, $urgency);

    if ($stmt->execute()) {
        $success = "Announcement created successfully!";
    } else {
        $error = "Something went wrong: " . $stmt->error;
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
        }

        body{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fb;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
        }

        .container{
            width:100%;
            max-width:550px;
        }

        .card{
            background:#fff;
            padding:35px;
            border-radius:18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .card h2{
            font-size:28px;
            color:#1e293b;
            margin-bottom:8px;
        }

        .subtitle{
            color:#64748b;
            margin-bottom:30px;
            font-size:14px;
        }

        .form-group{
            margin-bottom:20px;
        }

        label{
            display:block;
            margin-bottom:8px;
            font-weight:600;
            color:#334155;
        }

        input,
        textarea,
        select{
            width:100%;
            padding:14px;
            border:1px solid #dbe2ea;
            border-radius:10px;
            font-size:15px;
            transition:0.3s ease;
            background:#f8fafc;
        }

        input:focus,
        textarea:focus,
        select:focus{
            border-color:#2563eb;
            outline:none;
            background:#fff;
            box-shadow:0 0 0 4px rgba(37,99,235,0.1);
        }

        textarea{
            resize:none;
        }

        .btn{
            width:100%;
            padding:14px;
            border:none;
            border-radius:10px;
            background:#2563eb;
            color:white;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
            transition:0.3s ease;
        }

        .btn:hover{
            background:#1d4ed8;
            transform:translateY(-1px);
        }

        .success{
            background:#dcfce7;
            color:#166534;
            padding:12px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:14px;
        }

        .error{
            background:#fee2e2;
            color:#991b1b;
            padding:12px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:14px;
        }

        .urgency-light{
            color:#16a34a;
        }

        .urgency-medium{
            color:#d97706;
        }

        .urgency-urgent{
            color:#dc2626;
        }

    </style>
</head>
<body>

    <div class="container">

        <div class="card">

            <h2>Create Announcement</h2>
            <p class="subtitle">Fill in the details below to publish an announcement.</p>

            <?php if($success): ?>
                <div class="success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Enter announcement title" required>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" placeholder="Write your announcement here..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" required>
                </div>

                <div class="form-group">
                    <label>Urgency Level</label>
                    <select name="urgency">
                        <option value="light">🟢 Light</option>
                        <option value="medium">🟠 Medium</option>
                        <option value="urgent">🔴 Urgent</option>
                    </select>
                </div>

                <button type="submit" class="btn">
                    Create Announcement
                </button>

            </form>

        </div>

    </div>

</body>
</html>