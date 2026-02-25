<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($conn)) {
    die("Connessione al database non trovata.");
}

// Controllo ID opera
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID opera non valido.");
}
$id_opera = intval($_GET['id']);

// Recupero dati opera con autore e sponsor
$stmt = $conn->prepare("
    SELECT o.*, 
           u.Nome AS Nome_Autore, 
           u.Cognome AS Cognome_Autore,
           u.Email, 
           s.Nome AS Sponsor
    FROM opere o
    INNER JOIN Utenti u ON o.ID_Utente = u.ID_Utente
    LEFT JOIN Sponsor s ON o.ID_Sponsor = s.ID_Sponsor
    WHERE o.ID_Opera = ?
");
$stmt->bind_param("i", $id_opera);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Opera non trovata.");
}

$opera = $result->fetch_assoc();
$stmt->close();

// Recupero commenti con dati utente
$stmt_commenti = $conn->prepare("
    SELECT c.Commento, u.Nickname, u.Percorso_File
    FROM Commenti c
    INNER JOIN Utenti u ON c.ID_Utente = u.ID_Utente
    WHERE c.ID_Opera = ?
    ORDER BY c.ID_Com DESC
");
$stmt_commenti->bind_param("i", $id_opera);
$stmt_commenti->execute();
$commenti = $stmt_commenti->get_result();
$stmt_commenti->close();

// Recupero informazioni like
$num_likes = 0;
$already_liked = false;

if (isset($_SESSION['user_id'])) {
    $id_utente = $_SESSION['user_id'];

    // Controllo like utente
    $stmt_check = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
    $stmt_check->bind_param("ii", $id_utente, $id_opera);
    $stmt_check->execute();
    $stmt_check->store_result();
    $already_liked = $stmt_check->num_rows > 0;
    $stmt_check->close();
}

// Conteggio totale like
$stmt_count = $conn->prepare("SELECT COUNT(*) as totale FROM likes WHERE ID_Opera = ?");
$stmt_count->bind_param("i", $id_opera);
$stmt_count->execute();
$num_likes = $stmt_count->get_result()->fetch_assoc()['totale'];
$stmt_count->close();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($opera['Titolo']); ?> - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/dettagli.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

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
                    <h1 class="playfair"><?php echo htmlspecialchars($opera['Titolo']); ?></h1>

                    <p class="anno inter"><strong>Anno:</strong> <?php echo htmlspecialchars($opera['Anno']); ?></p>
                    <p class="autore inter"><strong>Autore:</strong> <?php echo htmlspecialchars($opera['Nome_Autore'] . " " . $opera['Cognome_Autore']); ?></p>
                    <p class="contatti inter"><strong>Contatti:</strong> <?php echo htmlspecialchars($opera['Email']); ?></p>
                    <p class="sponsor inter"><strong>Tipo:</strong> <?php echo htmlspecialchars($opera['Tipo']); ?></p>
                    
                    <!-- In base al tipo di opera, mostra i campi Tipo di tela o Materiale -->
                    <?php if ($opera['Tipo'] === 'Dipinto'): ?>
                        <p class="sponsor inter">
                            <strong>Tipo di Tela:</strong>
                            <?php echo htmlspecialchars($opera['Tipo_Tela']); ?>
                        </p>

                    <?php elseif ($opera['Tipo'] === 'Scultura'): ?>
                        <p class="sponsor inter">
                            <strong>Materiale:</strong>
                            <?php echo htmlspecialchars($opera['Materiale']); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Se l'opera è sponsorizzata, viene mostrato il nome dello Sponsor -->
                    <?php if (!empty($opera['Sponsor'])): ?>
                        <p class="sponsor inter">
                            <strong>Sponsor:</strong>
                            <?php echo htmlspecialchars($opera['Sponsor']); ?>
                        </p>
                    <?php endif; ?>

                    <div class="descrizione">
                        <p class="descrizione inter"><strong>Descrizione:</strong><br><?php echo nl2br(htmlspecialchars($opera['Descrizione'])); ?></p>
                    </div>

                    <!-- SEZIONE LIKE -->
                    <div class="like-section">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="/Nexus-Space/pages/login.php" class="btn-like">♡ Mi piace <?php echo $num_likes; ?></a>
                        <?php else: ?>
                            
                            <!-- Se already_liked è TRUE, aggiunge la stringa liked al nome della classe, altrimenti non mette nulla -->
                            <button class="btn-like <?php echo $already_liked ? 'liked' : ''; ?>" data-id="<?php echo $id_opera; ?>">
                                <?php echo $already_liked ? '♥' : '♡'; ?> <!-- Se already_liked è TRUE, mette il cuore pieno, altrimenti quello vuoto -->
                            </button>
                            
                            <!-- Per l'ID dello span si usa anche l'ID dell'opera --> 
                            <span id="like-count-<?php echo $id_opera; ?>"><?php echo $num_likes; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- COMMENTI -->
                    <div class="comment-button-section">
                        <button class="btn-comment" data-id="<?php echo $id_opera; ?>">💬 Commenti</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SEZIONE COMMENTI MODAL -->
    <div id="comment-modal" style="display:none;">
        <div class="modal-content">
            <!-- &times; è un entità HTML che rappresenta una X-->
            <span id="close-modal">&times;</span>
            <h2 class="modal-title">Commenti</h2>
            <div class="modal-body" id="modal-comment-list">
                <?php if ($commenti->num_rows > 0): ?>
                    <?php while($commento = $commenti->fetch_assoc()): ?>
                        <div class="comment-item">
                            <div class="commento-header">
                                <img src="/Nexus-Space/<?php echo htmlspecialchars($commento['Percorso_File'] ?? 'assets/img/login-icon.png'); ?>" alt="pfp">
                                <span class="commento-nickname"><?php echo htmlspecialchars($commento['Nickname']); ?></span>
                            </div>
                            <p class="commento-testo"><?php echo nl2br(htmlspecialchars($commento['Commento'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-comments">Nessun commento ancora.</p>
                <?php endif; ?>
            </div>

                <!-- Se l'utente è loggato, mostra il form per commentare -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="modal-comment-form">
                    <input type="text" name="commento" placeholder="Scrivi un commento..." required>
                    <button type="submit">Invia</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">Devi <a href="/Nexus-Space/pages/login.php">accedere</a> per commentare.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Script separato: gestione like e commenti -->
    <script src="/Nexus-Space/assets/js/dettagli.js"></script>
</body>
</html>