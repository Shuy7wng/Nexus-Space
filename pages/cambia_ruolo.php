<?php
require 'auth.php';
requireRole([1]); // solo admin
require ' ../config/database.php';

// Il cast serve ad evitare SQL Injection
$id = (int)$_GET['id'];
$ruolo = (int)$_GET['ruolo'];
    
// Prevengo la retrocessione di me stesso
if ($id == $_SESSION['user_id']) {
die("Non puoi cambiare il tuo ruolo.");
}

// Controllo sicurezza: ruolo valido (Admin=1, Visitatore=3)
if (!in_array($ruolo, [1,3])) {
die("Ruolo non valido.");
}

// Aggiorno l'utente
$stmt = $conn->prepare("UPDATE utenti SET ID_Ruolo = ? WHERE ID_Utente = ?");
$stmt->bind_param("ii", $ruolo, $id);
$stmt->execute();

header("Location: gestione_utenti.php");
exit;