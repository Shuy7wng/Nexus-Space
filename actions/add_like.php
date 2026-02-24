<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo "not_logged";
    exit;
}

if (!isset($_POST['id_opera']) || !is_numeric($_POST['id_opera'])) {
    echo "error";
    exit;
}

$id_utente = $_SESSION['user_id'];
$id_opera = intval($_POST['id_opera']);

/* Controllo se esiste */
$stmt = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
$stmt->bind_param("ii", $id_utente, $id_opera);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {

    // INSERT
    $insert = $conn->prepare("INSERT INTO likes (ID_Utente, ID_Opera) VALUES (?, ?)");
    $insert->bind_param("ii", $id_utente, $id_opera);

    if ($insert->execute()) {
        echo "added";
    } else {
        echo "error";
    }

    $insert->close();

} else {

    // DELETE
    $delete = $conn->prepare("DELETE FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
    $delete->bind_param("ii", $id_utente, $id_opera);

    if ($delete->execute()) {
        echo "removed";
    } else {
        echo "error";
    }

    $delete->close();
}

$stmt->close();
$conn->close();