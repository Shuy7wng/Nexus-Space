<?php
require_once "auth.php";

if ($_SESSION['ruolo'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}
?>
