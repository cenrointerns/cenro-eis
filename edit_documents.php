<?php
include "config.php";

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM documents WHERE id=$id")->fetch_assoc();

if ($_POST) {
  $name = $_POST['file_name'];
  $type = $_POST['document_type'];

  $stmt = $conn->prepare("UPDATE documents SET file_name=?, document_type=? WHERE id=?");
  $stmt->bind_param("ssi", $name, $type, $id);
  $stmt->execute();

  header("Location: documents.php");
}
?>

<form method="POST">
<input name="file_name" value="<?= $row['file_name'] ?>"><br>
<input name="document_type" value="<?= $row['document_type'] ?>"><br>
<button>Save</button>
</form>