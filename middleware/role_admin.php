<?php
require_once __DIR__ . "/auth.php";
if ($_SESSION['role'] != 1) { // 1 = Admin nel DB
    header("Location: /Nexus-Space/pages/index.php");
    exit();
}
?>