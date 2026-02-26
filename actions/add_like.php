<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../pages/auth.php';
requireLogin();

if (!isset($_GET['id_opera']) || empty($_GET['id_opera'])) {
    die("Opera non valida.");
}

$id_opera  = intval($_GET['id_opera']);
$id_utente = $_SESSION['user_id'];

/* Controllo se esiste */
$stmt = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
$stmt->bind_param("ii", $id_utente, $id_opera);
$stmt->execute();
$stmt->store_result();

$already_liked = $stmt->num_rows > 0;
$stmt->close();

if (!$already_liked) {

    // Aggiungo il like
    $stmt = $conn->prepare("INSERT INTO likes (ID_Utente, ID_Opera) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_utente, $id_opera);
    
    if ($stmt->execute()) {
        echo "added";
    } else {
        echo "error";
    }

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