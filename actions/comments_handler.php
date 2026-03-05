<?php
require '../pages/init.php';
require '../config/database.php';

// Specifico che la risposta sarà in formato JSON
header('Content-Type: application/json');

// Se l'ID non è valorizzato o non è un numero, lancio un errore
$id_opera = intval($_GET['id_opera'] ?? $_POST['id_opera'] ?? 0);

// Se è POST leggo il JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leggo il JSON
    // Json decode restituisce un array associativo se il secondo parametro è true, altrimenti un oggetto
    // File get contents legge tutto il contenuto del file, in questo caso php://input che è lo stream di input della richiesta HTTP, quindi il corpo della richiesta
    $input = json_decode(file_get_contents("php://input"), true);

    $id_opera = intval($input['id_opera'] ?? 0);
    $commento = trim($input['commento'] ?? '');

    // Se l'utente non è loggato, restituisco un errore
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'not_logged']);
        exit;
    }

    if ($id_opera && $commento) {
        $id_utente = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO commenti (ID_Opera, ID_Utente, Commento) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_opera, $id_utente, $commento);
        $stmt->execute();
    }

    // Restituisco un messaggio di successo
    echo json_encode(['status' => 'success']);
    exit;
}

// Recupera commenti
$stmt = $conn->prepare("
    SELECT c.Commento, u.Nickname
    FROM commenti c
    INNER JOIN utenti u ON c.ID_Utente = u.ID_Utente
    WHERE c.ID_Opera = ?
    ORDER BY c.ID_Com ASC
");
$stmt->bind_param("i", $id_opera);
$stmt->execute();
$result = $stmt->get_result();

// Creo un array di commenti da restituire
$comments = [];
// Per ogni commento, aggiungo un array associativo con il nickname e il commento
while($row = $result->fetch_assoc()){
    $comments[] = ['nickname'=>$row['Nickname'], 'commento'=>$row['Commento']];
}

// Restituisco i commenti in formato JSON
echo json_encode($comments);