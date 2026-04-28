<?php
$host = "sql104.infinityfree.com";
$user = "if0_41773320";
$pass = "2VYqb0x66h";
$db   = "if0_41773320_cenro";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>