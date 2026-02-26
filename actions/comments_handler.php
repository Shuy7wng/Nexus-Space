<?php
require_once __DIR__.'/../pages/init.php';
require_once __DIR__.'/../config/database.php';

header('Content-Type: application/json');

$id_opera = intval($_GET['id_opera'] ?? $_POST['id_opera'] ?? 0);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Aggiungi commento
    if(!isset($_SESSION['user_id'])){
        echo json_encode(['status'=>'not_logged']);
        exit;
    }

    $id_utente = $_SESSION['user_id'];
    $commento = trim($_POST['commento'] ?? '');
    if($commento){
        $stmt = $conn->prepare("INSERT INTO commenti (ID_Opera, ID_Utente, Commento) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_opera, $id_utente, $commento);
        $stmt->execute();
    }
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

echo json_encode($comments);