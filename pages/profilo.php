<?php
require '../config/database.php';
require 'auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Query che recupera i dati dell'utente e il nome del ruolo associato
$stmt = $conn->prepare("
    SELECT u.*, r.Nome_ruolo 
    FROM utenti u 
    INNER JOIN ruoli r ON u.ID_Ruolo = r.ID_ruolo 
    WHERE u.ID_Utente = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Query che recuperano lo storico di commenti e like 
// Commenti
$stmt_com = $conn->prepare("
SELECT c.Commento, o.Titolo, o.ID_Opera
FROM commenti c 
INNER JOIN opere o ON c.ID_Opera = o.ID_Opera 
WHERE c.ID_Utente = ?
");
$stmt_com->bind_param("i", $user_id);
$stmt_com->execute();
$commenti = $stmt_com->get_result();

// Like
$stmt_like = $conn->prepare("
SELECT o.Titolo, o.ID_Opera
FROM likes l 
INNER JOIN opere o ON l.ID_Opera = o.ID_Opera 
WHERE l.ID_Utente = ?
");
$stmt_like->bind_param("i", $user_id);
$stmt_like->execute();
$likes = $stmt_like->get_result();

/* Perché bisogna spostare l'immagine caricata?
    - Il file caricato tramite un form HTML viene temporaneamente salvato in una cartella di sistema (spesso /tmp) 
      con un nome generico. 
    - Se non si sposta, rimarrà lì e potrebbe essere cancellato automaticamente dal sistema dopo un certo periodo di tempo. 
    - Spostandolo in una cartella specifica del progetto (es. uploads/profili/) con un nome univoco, 
      garantisco che l'immagine sia accessibile e persistente per il profilo dell'utente.
*/

// Verifico se è stato inviato un nuovo file per la pfp e se non ci sono errori di upload
if (isset($_FILES['new_pfp']) && $_FILES['new_pfp']['error'] == 0) {

    $estensione = strtolower(pathinfo($_FILES['new_pfp']['name'], PATHINFO_EXTENSION));

    // Controllo dell'estensione
    // Un utente con cattive intenzioni potrebbe provare a caricare un file eseguibile rinominato con estensione .jpg, 
    // quindi è importante controllare l'estensione.
    $estensioniConsentite = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($estensione, $estensioniConsentite)) {
        die("Formato file non valido. Consentiti: jpg, jpeg, png, webp.");
    }

    // Creazione di un nome file univoco
    $nome_file = "user_" . $user_id . "." . $estensione;

    $cartella = "../uploads/profili/";

    // Se la cartella non esiste, la creo (opzionale ma evita errori)
    if (!is_dir($cartella)) {
         // Permessi di lettura/scrittura/esecuzione per tutti (opzionale, ma evita errori)
        mkdir($cartella, 0777, true);
    }

    $percorso_salvataggio = $cartella . $nome_file;

    // Se lo spostamento del file è andato a buon fine, aggiorno il percorso nel DB
    if (move_uploaded_file($_FILES['new_pfp']['tmp_name'], $percorso_salvataggio)) {
        $percorso_db = "uploads/profili/" . $nome_file;

        $update = $conn->prepare("UPDATE utenti SET Percorso_File = ? WHERE ID_Utente = ?");
        $update->bind_param("si", $percorso_db, $user_id);
        $update->execute();

        header("Location: profilo.php");
        exit();
    } else {
        die("Errore nel caricamento del file.");
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/profilo.css">
    <title>Profilo - Nexus Space</title>
</head>

<body>
    <div class="profile-container">
        <h2 class="profile-title">Il Mio Account</h2>

        <div class="profile-header">
            <div class="pfp-section">
                <div class="pfp-wrapper">
                    <img class="commento-pfp"
                        src="/Nexus-Space/<?php echo !empty($user['Percorso_File']) ? htmlspecialchars($user['Percorso_File']) : '/assets/img/login-icon.png'; ?>"
                        alt="pfp">
                </div>
                <form action="profilo.php" method="POST" enctype="multipart/form-data">
                    <label for="pfp_input" class="change-pfp-label">Change pfp</label>
                    <input type="file" id="pfp_input" name="new_pfp" style="display: none;" onchange="this.form.submit()">
                </form>
            </div>
            <div class="nickname-box">
                @<?php echo htmlspecialchars($user['Nickname']); ?>
                <span class="role-badge"><?php echo htmlspecialchars($user['Nome_ruolo']); ?></span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Nome e Cognome</label>
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

        <div class="history-grid">
            <!-- COMMENT HISTORY -->
            <div class="history-section">
                <h3>Comment History</h3>
                <div class="history-list">
                    <?php if ($commenti->num_rows > 0): ?>
                        <?php while ($c = $commenti->fetch_assoc()): ?>
                            <a href="/Nexus-Space/pages/dettagli.php?id=<?php echo $c['ID_Opera']; ?>" class="history-item link-item">
                                Commento a <strong><?php echo htmlspecialchars($c['Titolo']); ?></strong>:
                                "<?php echo htmlspecialchars($c['Commento']); ?>"
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-history">Nessun commento ancora.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LIKE HISTORY -->
            <div class="history-section">
                <h3>Like History</h3>
                <div class="history-list">
                    <?php if ($likes->num_rows > 0): ?>
                        <?php while ($l = $likes->fetch_assoc()): ?>
                            <a href="/Nexus-Space/pages/dettagli.php?id=<?php echo $l['ID_Opera']; ?>" class="history-item link-item">
                                Like a <strong><?php echo htmlspecialchars($l['Titolo']); ?></strong>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-history">Nessun like ancora.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-footer">
            <a href="index.php" class="btn-outline">Home</a>
            <a href="logout.php" style="margin-left:20px; color:var(--verde-oliva);">Logout</a>
        </div>
    </div>
</body>

</html>