<?php
session_start();
session_unset();
session_destroy();

header("Location: index.php"); // change if your login page is different
exit();