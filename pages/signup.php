<?php
session_start();
require_once __DIR__ . '/../config/database.php'; // file con connessione al DB

$errore = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $data_nascita = $_POST['data_nascita'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nickname = $_POST['nickname'];

    // Recupero il ruolo dal form. Se non settato, fallisce.
    // Impediamo che qualcuno possa registrarsi come Admin (ID 1) via script
    $ruolo = isset($_POST['id_ruolo']) ? intval($_POST['id_ruolo']) : 3;
    
    if ($ruolo === 1) {
        $ruolo = 3; // Forza a Visitatore se tentano di farsi admin
    }

    // Controllo email o nickname già esistenti
    $stmt_check = $conn->prepare("SELECT ID_Utente FROM utenti WHERE Email = ? OR Nickname = ?");
    $stmt_check->bind_param("ss", $email, $nickname);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if($result_check->num_rows > 0){
        $errore = "Email o nickname già registrati.";
    } else {
        // Inserimento nel DB con il ruolo scelto
        $stmt = $conn->prepare("INSERT INTO utenti (Nome, Cognome, Email, Data_nascita, Password, Nickname, ID_Ruolo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $nome, $cognome, $email, $data_nascita, $password, $nickname, $ruolo);
        
        if($stmt->execute()){
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['role'] = $ruolo;
            
            // Reindirizzamento alla index dentro la cartella pages
            header("Location: index.php");
            exit();
        } else {
            $errore = "Errore durante la registrazione.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Nexus</title>
    <link rel="stylesheet" href="/nexus-space/assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="form-container">
    <h2>Registrati</h2>
    <?php if($errore) echo "<p class='error'>$errore</p>"; ?>
    <form method="POST">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="cognome" placeholder="Cognome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="date" name="data_nascita" required>
        <div class="role-selection">
            <p>Chi sei?</p>
            
            <label>
                <input type="radio" name="id_ruolo" value="3" onclick="showDescription('visitatore')" required> 
                Visitatore
            </label>
            
            <label>
                <input type="radio" name="id_ruolo" value="2" onclick="showDescription('artista')"> 
                Artista
            </label>

            <div id="role-description" style="margin-top: 10px; font-style: italic; color: #666;">
                Seleziona un ruolo per vedere i dettagli.
            </div>
        </div>
        <input type="text" name="nickname" placeholder="Nickname" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Registrati</button>
        <p>Hai già un account? <a href="login.php">Accedi</a></p>
    </form>
</div>
<script>
function showDescription(role) {
    const descBox = document.getElementById('role-description');
    
    if (role === 'visitatore') {
        descBox.innerText = "Come Visitatore potrai esplorare le opere, lasciare like e commentare i tuoi capolavori preferiti.";
    } else if (role === 'artista') {
        descBox.innerText = "Come Artista potrai caricare le tue opere, partecipare agli eventi e gestire la tua galleria personale.";
    }
}
</script>
</body>
</html>
