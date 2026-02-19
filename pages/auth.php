<?php
session_start();

function isLogged() {
    return isset($_SESSION['role']);
}

function requireLogin() {
    if (!isLogged()) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        header("Location: no_access.php");
        exit;
    }
}
?>
