<?php
session_start();
require_once __DIR__ . "/../config/database.php";
include __DIR__ . '/../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Opera non valida.");
}

$id_opera = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT o.*, 
           u.Nome AS Nome_Autore, 
           u.Cognome AS Cognome_Autore,
           u.Email, s.Nome AS Sponsor
    FROM Utenti u
    INNER JOIN opere o ON u.ID_Utente = o.ID_Utente
    INNER JOIN Sponsor s ON o.ID_Sponsor = s.ID_Sponsor
    WHERE o.ID_Opera = ?
");
$stmt->bind_param("i", $id_opera);
$stmt->execute();
$risultato = $stmt->get_result();

if ($risultato->num_rows === 0) {
    die("Opera non trovata.");
}

$opera = $risultato->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($opera['Titolo']); ?> - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/opera.css">
</head>
<body>
    <main class="opera-dettaglio">
        <div class="container">
            <div class="opera-layout">
                <!-- IMMAGINE -->
                <div class="opera-img">
                    <img src="/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>"
                        alt="<?php echo htmlspecialchars($opera['Titolo']); ?>">
                </div>

                <!-- INFO -->
                <div class="opera-info">

                    <h1 class="playfair">
                        <?php echo htmlspecialchars($opera['Titolo']); ?>
                    </h1>

                    <p class="anno inter">
                        <strong>Anno:</strong>
                        <?php echo htmlspecialchars($opera['Anno']); ?>
                    </p>

                    <p class="autore inter">
                        <strong>Autore dell'opera:</strong>
                        <?php echo htmlspecialchars($opera['Nome_Autore'] . " " . $opera['Cognome_Autore']); ?>
                    </p>

                    <p class="contatti inter">
                        <strong>Contatti dell'autore:</strong>
                        <?php echo htmlspecialchars($opera['Email']); ?>
                    </p>

                    <p class="sponsor inter">
                        <strong>Sponsorizzata da:</strong>
                        <?php echo htmlspecialchars($opera['Sponsor']); ?>
                    </p>

                    <div class="descrizione">
                        <p class="inter">
                            <strong>Descrizione:</strong>
                            <?php echo nl2br(htmlspecialchars($opera['Descrizione'])); ?>
                        </p>
                    </div><br>

                    <div class="like-section">
                        <button class="btn-like">♡ Mi piace</button>
                        <span class="like-count"><?php echo htmlspecialchars($opera['NumLike']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p class="playfair">&copy; 2024 Nexus Space - International Art Gallery</p>
        <p class="inter" style="font-size: 0.8rem; margin-top: 10px; opacity: 0.7;">Tutti i diritti riservati.</p>
    </footer>
</body>
</html>