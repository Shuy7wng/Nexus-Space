<?php
require 'init.php';

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
        header("Location: login.php");
        exit;
    }
}
?>
