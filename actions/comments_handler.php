<?php
require '../pages/init.php';
require '../config/database.php';

// Specifico che la risposta sarà in formato JSON
header('Content-Type: application/json');

// Se l'ID non è valorizzato o non è un numero, lancio un errore
$id_opera = intval($_GET['id_opera'] ?? $_POST['id_opera'] ?? 0);

// Se è POST leggo il JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents("php://input"), true);

    $id_opera = intval($input['id_opera'] ?? 0);
    $commento = trim($input['commento'] ?? '');

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

$comments = [];
while($row = $result->fetch_assoc()){
    $comments[] = ['nickname'=>$row['Nickname'], 'commento'=>$row['Commento']];
}

// Restituisco i commenti in formato JSON
echo json_encode($comments);