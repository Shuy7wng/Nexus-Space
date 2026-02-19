<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Controllo se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ricavo l'ID dell'utente
$user_id = $_SESSION['user_id'];

// Gestione caricamento nuova immagine profilo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_pfp'])) { 
    $target_dir = "../uploads/profili/"; // cartella in cui verranno inserite le immagini degli utenti

    // Quando si carica un file, PHP lo inserisce in una cartella temporanea quindi ne ricavo il percorso
    $file_extension = strtolower(pathinfo($_FILES["new_pfp"]["name"], PATHINFO_EXTENSION));

    // Dato che le immagini sono salvate nella cartella, creo dei nomi delle immagini univoci (in modo da evitare utenti con lo stesso nome immagine)
    // Il nuovo nome del file sarà composto da ID_USER e dal timestamp corrente
    $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;

    // Creo il percorso del file 
    $target_file = $target_dir . $new_filename;

    // Se questa funzione ritorna TRUE, lo spostamento del file è andato a buon fine, dunque inserisco il percorso dell'immagine anche nel database
    if (move_uploaded_file($_FILES["new_pfp"]["tmp_name"], $target_file)) {
        $db_path = "uploads/profili/" . $new_filename;
        $stmt = $conn->prepare("UPDATE utenti SET Percorso_File = ? WHERE ID_Utente = ?");
        $stmt->bind_param("si", $db_path, $user_id);
        $stmt->execute();
    }
}

// Recupero dati dell'utente
$stmt = $conn->prepare("
    SELECT u.*, r.Nome_ruolo 
    FROM utenti u 
    JOIN ruoli r ON u.ID_Ruolo = r.ID_ruolo 
    WHERE u.ID_Utente = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/style.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/profilo.css">
</head>
<body class="bg-light">

    <div class="profile-container">
        <h2 class="profile-title">Il Mio Account</h2>
        
        <div class="profile-header">
            <div class="pfp-section">
                <div class="pfp-wrapper">
                    <img src="/Nexus-Space/<?php echo $user['Percorso_File'] ?? 'assets/img/default_pfp.png'; ?>" alt="PFP">
                </div>
                <form action="profilo.php" method="POST" enctype="multipart/form-data">
                    <label for="pfp_input" class="change-pfp-label">Aggiorna Foto</label>
                    <input type="file" id="pfp_input" name="new_pfp" style="display: none;" onchange="this.form.submit()">
                </form>
            </div>

            <div class="nickname-box">
                @<?php echo htmlspecialchars($user['Nickname']); ?>
                <span class="role-badge"><?php echo $user['Nome_ruolo']; ?></span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Nome Completo</label>
                <p><?php echo htmlspecialchars($user['Nome'] . " " . $user['Cognome']); ?></p>
            </div>
            <div class="info-item">
                <label>Email</label>
                <p><?php echo htmlspecialchars($user['Email']); ?></p>
            </div>
            <div class="info-item">
                <label>Data di Nascita</label>
                <p><?php echo date("d/m/Y", strtotime($user['Data_nascita'])); ?></p>
            </div>
        </div>

        <div class="profile-footer">
            <a href="index.php" class="btn-outline">Torna alla Galleria</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

</body>
</html>