<?php
include "config.php";

if (!isset($_GET['file'])) {
    die("No file specified.");
}

$file = basename($_GET['file']); // security fix
$path = "uploads/" . $file;

if (!file_exists($path)) {
    die("File not found.");
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Document</title>
</head>
<body style="margin:0;">

<?php if ($ext == "pdf") { ?>

    <iframe src="<?= $path ?>" style="width:100%; height:100vh;"></iframe>

<?php } elseif (in_array($ext, ["doc", "docx"])) { ?>

    <p style="padding:20px;">
        ⚠ Word files cannot be previewed directly.<br><br>
        <a href="<?= $path ?>" download>Download File</a>
    </p>

<?php } else { ?>

    <p>Unsupported file type.</p>

<?php } ?>

</body>
</html>