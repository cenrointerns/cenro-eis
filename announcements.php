<?php
include 'config.php';

/*
|--------------------------------------------------------------------------
| MARK AS READ
|--------------------------------------------------------------------------
*/
if(isset($_GET['read'])){

    $id = (int) $_GET['read'];

    $update = "UPDATE announcements SET is_read = 1 WHERE id = $id";
    $conn->query($update);

    header("Location: announcements.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH ANNOUNCEMENTS
|--------------------------------------------------------------------------
*/
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);

/*
|--------------------------------------------------------------------------
| UNREAD COUNT
|--------------------------------------------------------------------------
*/
$countSql = "SELECT COUNT(*) AS total_unread 
             FROM announcements 
             WHERE is_read = 0";

$countResult = $conn->query($countSql);
$countRow = $countResult->fetch_assoc();

$unreadCount = $countRow['total_unread'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Poppins', sans-serif;
            background:#f4f6f9;
            padding:40px 20px;
            color:#333;
        }

        .container{
            max-width:900px;
            margin:auto;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }

        .page-title{
            font-size:32px;
            font-weight:700;
            color:#111827;
        }

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATION BELL
        |--------------------------------------------------------------------------
        */

        .notification{
            position:relative;
            background:#fff;
            width:55px;
            height:55px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
            cursor:pointer;
            transition:0.3s;
        }

        .notification:hover{
            transform:translateY(-3px);
        }

        .notification i{
            font-size:22px;
            color:#111827;
        }

        .notif-count{
            position:absolute;
            top:-5px;
            right:-2px;
            background:#ef4444;
            color:#fff;
            font-size:12px;
            font-weight:600;
            min-width:22px;
            height:22px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            border:2px solid white;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        .card{
            background:#fff;
            border-radius:14px;
            padding:24px;
            margin-bottom:24px;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
            transition:0.3s ease;
            position:relative;
            overflow:hidden;
        }

        .card:hover{
            transform:translateY(-4px);
            box-shadow:0 8px 18px rgba(0,0,0,0.12);
        }

        .card::before{
            content:'';
            position:absolute;
            top:0;
            left:0;
            width:6px;
            height:100%;
        }

        .light::before{
            background:#22c55e;
        }

        .medium::before{
            background:#f59e0b;
        }

        .urgent::before{
            background:#ef4444;
        }

        /*
        |--------------------------------------------------------------------------
        | READ STYLE
        |--------------------------------------------------------------------------
        */

        .read{
            opacity:0.75;
            background:#f9fafb;
        }

        .top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:15px;
        }

        .title{
            font-size:22px;
            font-weight:600;
            color:#111827;
        }

        .badge{
            padding:6px 14px;
            border-radius:30px;
            font-size:12px;
            font-weight:600;
            text-transform:uppercase;
            color:white;
        }

        .light-badge{
            background:#22c55e;
        }

        .medium-badge{
            background:#f59e0b;
        }

        .urgent-badge{
            background:#ef4444;
        }

        .message{
            font-size:15px;
            line-height:1.8;
            color:#4b5563;
            margin-bottom:20px;
        }

        .footer{
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-top:1px solid #e5e7eb;
            padding-top:14px;
            font-size:14px;
            color:#6b7280;
        }

        .deadline{
            font-weight:600;
            color:#111827;
        }

        /*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

        .btn-read{
            text-decoration:none;
            background:#2563eb;
            color:white;
            padding:10px 18px;
            border-radius:8px;
            font-size:14px;
            font-weight:500;
            transition:0.3s;
        }

        .btn-read:hover{
            background:#1d4ed8;
        }

        .read-text{
            color:#16a34a;
            font-weight:600;
        }

        .empty{
            background:white;
            padding:30px;
            border-radius:12px;
            text-align:center;
            color:#6b7280;
            box-shadow:0 4px 10px rgba(0,0,0,0.05);
        }

        @media(max-width:600px){

            .header{
                flex-direction:column;
                align-items:flex-start;
                gap:20px;
            }

            .top,
            .footer{
                flex-direction:column;
                align-items:flex-start;
                gap:10px;
            }
        }

    </style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">

        <h1 class="page-title">📢 Announcements</h1>

        <!-- Notification Bell -->
        <div class="notification">
            <i class="fa-solid fa-bell"></i>

            <?php if($unreadCount > 0){ ?>
                <span class="notif-count">
                    <?php echo $unreadCount; ?>
                </span>
            <?php } ?>
        </div>

    </div>

    <?php if($result->num_rows > 0){ ?>

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="card <?php echo $row['urgency']; ?> 
                 <?php echo ($row['is_read'] == 1) ? 'read' : ''; ?>">

                <div class="top">

                    <h2 class="title">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h2>

                    <span class="badge <?php echo $row['urgency']; ?>-badge">
                        <?php echo ucfirst($row['urgency']); ?>
                    </span>

                </div>

                <div class="message">
                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                </div>

                <div class="footer">

                    <div>
                        📅 Posted:
                        <?php echo date("F d, Y", strtotime($row['created_at'])); ?>
                        <br>

                        <span class="deadline">
                            ⏰ Deadline:
                            <?php echo date("F d, Y", strtotime($row['deadline'])); ?>
                        </span>
                    </div>

                    <div>

                        <?php if($row['is_read'] == 0){ ?>

                            <a href="?read=<?php echo $row['id']; ?>" 
                               class="btn-read">
                               Mark as Read
                            </a>

                        <?php } else { ?>

                            <span class="read-text">
                                ✔ Read
                            </span>

                        <?php } ?>

                    </div>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty">
            No announcements available.
        </div>

    <?php } ?>

</div>

</body>
</html>