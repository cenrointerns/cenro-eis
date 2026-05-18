<?php
session_start();
include 'config.php';

/* =========================
   SECURITY CHECK
========================= */
if (!isset($_SESSION['employee_id'])) {
    header("Location: Employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

/* =========================
   DB CONNECTION
========================= */
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =========================
   FETCH DOCUMENTS
========================= */
$stmt = $conn->prepare("
    SELECT id, file_name, document_type, file_path, file_type, uploaded_at
    FROM documents
    WHERE employee_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Documents</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

/* =========================
   RESET
========================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins', sans-serif;
    background:
        linear-gradient(rgba(244,248,245,0.90), rgba(244,248,245,0.90)),
        url('assets/images/cenro.jpeg');
    background-size:cover;
    background-position:center;
    background-attachment:fixed;
    min-height:100vh;
    color:#333;
}

/* =========================
   CONTAINER
========================= */
.container{
    width:95%;
    max-width:1400px;
    margin:30px auto;
}

/* =========================
   HEADER
========================= */
.header{
    background:linear-gradient(135deg, #145a24, #1b5e20);
    color:white;
    padding:30px;
    border-radius:25px;
    position:relative;
    overflow:hidden;
    box-shadow:0 12px 30px rgba(0,0,0,0.15);
    animation:fadeIn 0.5s ease;
}

.header::before{
    content:'';
    position:absolute;
    width:220px;
    height:220px;
    background:rgba(255,255,255,0.08);
    border-radius:50%;
    top:-70px;
    right:-50px;
}

.header h2{
    font-size:32px;
    margin-bottom:10px;
    position:relative;
    z-index:1;
}

.header p{
    opacity:0.95;
    font-size:15px;
    position:relative;
    z-index:1;
}

/* =========================
   TOP ACTIONS
========================= */
.top-actions{
    margin-top:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
}

.search-box{
    flex:1;
    min-width:250px;
    position:relative;
}

.search-box input{
    width:100%;
    padding:14px 18px;
    border:none;
    outline:none;
    border-radius:14px;
    font-size:15px;
    background:white;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.back-btn{
    padding:13px 20px;
    background:#1b5e20;
    color:white;
    text-decoration:none;
    border-radius:14px;
    font-weight:500;
    transition:0.3s ease;
    box-shadow:0 5px 15px rgba(0,0,0,0.10);
}

.back-btn:hover{
    background:#2e7d32;
    transform:translateY(-3px);
}

/* =========================
   CARD
========================= */
.card{
    background:rgba(255,255,255,0.95);
    margin-top:25px;
    border-radius:25px;
    padding:25px;
    box-shadow:0 12px 30px rgba(0,0,0,0.10);
    overflow:hidden;
    animation:fadeIn 0.7s ease;
}

/* =========================
   TABLE
========================= */
.table-wrapper{
    overflow-x:auto;
    border-radius:18px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

thead{
    background:#145a24;
    color:white;
}

table th{
    padding:18px;
    text-align:left;
    font-size:15px;
    font-weight:600;
}

table td{
    padding:18px;
    border-bottom:1px solid #e9ecef;
    font-size:14px;
}

tbody tr{
    transition:0.3s ease;
}

tbody tr:hover{
    background:#f5faf5;
    transform:scale(1.003);
}

/* =========================
   FILE BADGE
========================= */
.badge{
    display:inline-block;
    padding:7px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
    color:white;
}

.pdf{
    background:#dc3545;
}

.doc{
    background:#007bff;
}

.image{
    background:#28a745;
}

.default{
    background:#6c757d;
}

/* =========================
   BUTTONS
========================= */
.action-buttons{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    padding:10px 16px;
    border-radius:12px;
    text-decoration:none;
    color:white;
    font-size:13px;
    font-weight:500;
    transition:0.3s ease;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.btn:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 18px rgba(0,0,0,0.15);
}

.view{
    background:#28a745;
}

.view:hover{
    background:#23913d;
}

.download{
    background:#007bff;
}

.download:hover{
    background:#0065d1;
}

/* =========================
   EMPTY STATE
========================= */
.empty{
    text-align:center;
    padding:70px 20px;
}

.empty img{
    width:130px;
    opacity:0.7;
    margin-bottom:20px;
}

.empty h3{
    color:#1b5e20;
    margin-bottom:10px;
}

.empty p{
    color:#666;
}

/* =========================
   MOBILE CARDS
========================= */
.mobile-docs{
    display:none;
}

.doc-card{
    background:#ffffff;
    border-radius:20px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    border-left:5px solid #1b5e20;
    transition:0.3s ease;
}

.doc-card:hover{
    transform:translateY(-5px);
}

.doc-card h3{
    color:#145a24;
    margin-bottom:10px;
    word-break:break-word;
}

.doc-info{
    margin-bottom:8px;
    font-size:14px;
}

.doc-info span{
    font-weight:600;
    color:#1b5e20;
}

/* =========================
   ANIMATION
========================= */
@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* =========================
   RESPONSIVE
========================= */
@media(max-width:991px){

    .header{
        padding:25px;
    }

    .header h2{
        font-size:26px;
    }

    .card{
        padding:20px;
    }

    table{
        min-width:800px;
    }
}

@media(max-width:768px){

    .table-wrapper{
        display:none;
    }

    .mobile-docs{
        display:block;
    }

    .header h2{
        font-size:24px;
    }

    .top-actions{
        flex-direction:column;
        align-items:stretch;
    }

    .back-btn{
        text-align:center;
    }
}

@media(max-width:480px){

    .container{
        width:92%;
    }

    .header{
        border-radius:18px;
        padding:22px;
    }

    .card{
        border-radius:18px;
        padding:18px;
    }

    .doc-card{
        border-radius:16px;
    }

    .header h2{
        font-size:22px;
    }
}

</style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">

        <h2>📁 My Documents</h2>

        <p>
            Access and manage all your uploaded DENR employee files securely.
        </p>

    </div>

    <!-- TOP ACTIONS -->
    <div class="top-actions">

        <div class="search-box">
            <input
                type="text"
                id="searchInput"
                placeholder="🔍 Search documents..."
            >
        </div>

        <a href="Employee_dashboard.php" class="back-btn">
            ← Back to Dashboard
        </a>

    </div>

    <!-- CARD -->
    <div class="card">

        <?php if ($result->num_rows > 0): ?>

            <!-- DESKTOP TABLE -->
            <div class="table-wrapper">

                <table id="documentsTable">

                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>File Type</th>
                            <th>Document Type</th>
                            <th>Date Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <?php

                        $fileType = strtolower($row['file_type']);

                        $badgeClass = 'default';

                        if(str_contains($fileType, 'pdf')){
                            $badgeClass = 'pdf';
                        }
                        elseif(
                            str_contains($fileType, 'doc') ||
                            str_contains($fileType, 'word')
                        ){
                            $badgeClass = 'doc';
                        }
                        elseif(
                            str_contains($fileType, 'jpg') ||
                            str_contains($fileType, 'jpeg') ||
                            str_contains($fileType, 'png')
                        ){
                            $badgeClass = 'image';
                        }

                        ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['file_name']); ?>
                            </td>

                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($row['file_type']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['document_type']); ?>
                            </td>

                            <td>
                                <?php echo date("F d, Y h:i A", strtotime($row['uploaded_at'])); ?>
                            </td>

                            <td>

                                <div class="action-buttons">

                                    <a
                                        class="btn view"
                                        href="view.php?id=<?php echo $row['id']; ?>"
                                        target="_blank"
                                    >
                                        👁 View
                                    </a>

                                    <a
                                        class="btn download"
                                        href="view_document.php?id=<?php echo $row['id']; ?>"
                                    >
                                        ⬇ Download
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

            <!-- MOBILE VIEW -->
            <div class="mobile-docs">

                <?php
                $stmt2 = $conn->prepare("
                    SELECT id, file_name, document_type, file_type, uploaded_at
                    FROM documents
                    WHERE employee_id = ?
                    ORDER BY uploaded_at DESC
                ");

                $stmt2->bind_param("i", $employee_id);
                $stmt2->execute();
                $mobileResult = $stmt2->get_result();

                while($row = $mobileResult->fetch_assoc()):
                ?>

                <div class="doc-card searchItem">

                    <h3>
                        <?php echo htmlspecialchars($row['file_name']); ?>
                    </h3>

                    <div class="doc-info">
                        <span>Type:</span>
                        <?php echo htmlspecialchars($row['file_type']); ?>
                    </div>

                    <div class="doc-info">
                        <span>Document:</span>
                        <?php echo htmlspecialchars($row['document_type']); ?>
                    </div>

                    <div class="doc-info">
                        <span>Uploaded:</span>
                        <?php echo date("F d, Y h:i A", strtotime($row['uploaded_at'])); ?>
                    </div>

                    <div class="action-buttons" style="margin-top:15px;">

                        <a
                            class="btn view"
                            href="view.php?id=<?php echo $row['id']; ?>"
                            target="_blank"
                        >
                            👁 View
                        </a>

                        <a
                            class="btn download"
                            href="view_document.php?id=<?php echo $row['id']; ?>"
                        >
                            ⬇ Download
                        </a>

                    </div>

                </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                <img src="assets/images/DENR_logo.png" alt="No Files">

                <h3>No Documents Found</h3>

                <p>
                    You currently do not have uploaded files in your account.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<script>

/* =========================
   SEARCH FILTER
========================= */
document.getElementById('searchInput').addEventListener('keyup', function(){

    let value = this.value.toLowerCase();

    let rows = document.querySelectorAll('#documentsTable tbody tr');

    rows.forEach(function(row){

        row.style.display =
            row.innerText.toLowerCase().includes(value)
            ? ''
            : 'none';

    });

    let cards = document.querySelectorAll('.searchItem');

    cards.forEach(function(card){

        card.style.display =
            card.innerText.toLowerCase().includes(value)
            ? ''
            : 'none';

    });

});

</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>