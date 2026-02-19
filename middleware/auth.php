<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
?>

// Nelle pagine che richiedono login va messo: require_once "../middleware/auth.php";