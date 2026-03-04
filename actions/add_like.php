<?php
require '../config/database.php';
require '../pages/auth.php';
requireLogin();

header('Content-Type: application/json');

// Leggo il JSON
$input = json_decode(file_get_contents("php://input"), true);

$id_opera = intval($input['id_opera'] ?? 0);

if (!$id_opera) {
    echo json_encode(['status' => 'error']);
    exit;
}

$id_utente = $_SESSION['user_id'];

// Controllo se l'utente ha già messo like a questa opera
$stmt = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
$stmt->bind_param("ii", $id_utente, $id_opera);
$stmt->execute();
$stmt->store_result();

// Se non lo ha fatto, la query ritorna 0 righe
if ($stmt->num_rows == 0) {

    // Quindi inserisco nella tabella il nuovo like
    $insert = $conn->prepare("INSERT INTO likes (ID_Utente, ID_Opera) VALUES (?, ?)");
    $insert->bind_param("ii", $id_utente, $id_opera);

    // La risposta è "added" se il like è stato aggiunto, altrimenti "error"
    if ($insert->execute()) {
        echo json_encode(['status' => 'added']);
    } else {
        echo json_encode(['status' => 'error']);
    }

    $insert->close();

} else { // Se lo ha fatto

    // Rimuovo il like dalla tabella
    $delete = $conn->prepare("DELETE FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
    $delete->bind_param("ii", $id_utente, $id_opera);

    // La risposta è "removed" se il like è stato rimosso, altrimenti "error"
    if ($delete->execute()) {
        echo json_encode(['status' => 'removed']);
    } else {
        echo json_encode(['status' => 'error']);
    }

    $delete->close();
}

$stmt->close();
$conn->close();