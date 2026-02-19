<?php
require_once __DIR__ . "/auth.php";
if ($_SESSION['role'] != 2) { // 2 = Artista
    header("Location: /Nexus-Space/pages/index.php");
    exit();
}
?>