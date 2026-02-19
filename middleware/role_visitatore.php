<?php
require_once "auth.php";

if ($_SESSION['ruolo'] !== 'Visitatore') {
    header("Location: dashboard.php");
    exit();
}
?>
