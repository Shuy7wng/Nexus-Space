<?php
require_once "auth.php";

if ($_SESSION['ruolo'] !== 'Artista') {
    header("Location: dashboard.php");
    exit();
}
?>
