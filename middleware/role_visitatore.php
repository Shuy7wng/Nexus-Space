<?php
require_once __DIR__ . "/auth.php";
if ($_SESSION['role'] != 3) { // 3 = Visitatore
    header("Location: /Nexus-Space/pages/index.php");
    exit();
}
?>