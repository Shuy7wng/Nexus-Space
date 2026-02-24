<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificare se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    echo "not_logged";
    exit;
}

// Verificare che id_opera sia stato inviato
if (!isset($_POST['id_opera']) || !is_numeric($_POST['id_opera'])) {
    echo "errore";
    exit;
}

$id_utente = $_SESSION['user_id'];
$id_opera = intval($_POST['id_opera']);

// Controllare se l'utente ha già messo like
$stmt = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
$stmt->bind_param("ii", $id_utente, $id_opera);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Se non ha messo like, inserire
    $stmt_insert = $conn->prepare("INSERT INTO likes (ID_Utente, ID_Opera) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $id_utente, $id_opera);
    
    if ($stmt_insert->execute()) {
        echo "added";
    } else {
        echo "errore";
    }
    $stmt_insert->close();
} else {
    // Se ha già messo like, rimuoverlo
    $stmt_delete = $conn->prepare("DELETE FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
    $stmt_delete->bind_param("ii", $id_utente, $id_opera);
    
    if ($stmt_delete->execute()) {
        echo "removed";
    } else {
        echo "errore";
    }
    $stmt_delete->close();
}

$stmt->close();
$conn->close();
?>
