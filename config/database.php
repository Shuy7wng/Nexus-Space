<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "nexus";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>

// nei file che devono connettersi al db: require_once "../config/database.php";
