<?php
require 'init.php';
require 'auth.php';
require '../config/database.php';
require '../includes/header.php';

// Controllo che l'id sia stato passato
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Evento non valido.");
}

// Per sicurezza converto l'ID in intero (sanitizzazione)
$id_evento = intval($_GET['id']);

// Recupero delle opere dal DB
$stmt = $conn->prepare("SELECT o.*, u.Nome AS Nome_Autore, u.Cognome AS Cognome_Autore FROM opere o INNER JOIN Utenti u ON o.ID_Utente = u.ID_Utente WHERE ID_Evento = ? AND Stato = 'Accettata'");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$risultato = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Opere - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/opere.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>

<body>

    <section class="hero-opere">
        <div class="hero-text">
            <h1 class="playfair">Opere</h1>
            <p class="inter">Esplora le opere di quest'evento.</p>
        </div>
    </section>

    <main class="collection-opere">
        <div class="container">
            <div class="gallery-opere">

                <?php if ($risultato && $risultato->num_rows > 0): ?>
                    <?php while ($opera = $risultato->fetch_assoc()): ?>
                        <div class="opera-card">

                            <div class="opera-img">
                                <a href="dettagli.php?id=<?php echo $opera['ID_Opera']; ?>">
                                    <img src="/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>"
                                        alt="<?php echo htmlspecialchars($opera['Titolo']); ?>">
                                </a>
                            </div>

                            <div class="opera-info">
                                <h3 class="playfair"><?php echo htmlspecialchars($opera['Titolo']); ?></h3>
                                <p class="autore inter"><?php echo htmlspecialchars($opera['Nome_Autore'] . " " . $opera['Cognome_Autore']); ?></p>
                                <p class="descrizione inter"><?php echo htmlspecialchars($opera['Descrizione']); ?></p>

                                <div class="actions">
                                    <?php
                                    $id_utente = $_SESSION['user_id'] ?? 0;

                                    // Query che conta i like per questa opera
                                    $stmt_count = $conn->prepare("SELECT COUNT(*) as totale FROM likes WHERE ID_Opera = ?");
                                    $stmt_count->bind_param("i", $opera['ID_Opera']);
                                    $stmt_count->execute();
                                    $num_likes = $stmt_count->get_result()->fetch_assoc()['totale'];
                                    $stmt_count->close();

                                    // Query che controlla se l'utente ha già messo like
                                    $already_liked = false;
                                    if ($id_utente) {
                                        $stmt_check = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
                                        $stmt_check->bind_param("ii", $id_utente, $opera['ID_Opera']);
                                        $stmt_check->execute();
                                        $stmt_check->store_result();
                                        $already_liked = $stmt_check->num_rows > 0; // Se c'è almeno una riga, l'utente ha già messo like
                                        $stmt_check->close();
                                    }

                                    $heart = $already_liked ? '♥' : '♡';
                                    $like_class = $already_liked ? 'liked' : '';
                                    ?>

                                    <!-- Span che mostra il numero di like dell'opera -->
                                    <!-- L'ID include l'ID dell'opera per permettere aggiornamenti dinamici tramite JS -->
                                    <span class="like-count" id="like-count-<?php echo $opera['ID_Opera']; ?>"><?php echo $num_likes; ?></span>
                                    
                                    <!-- Pulsante per mettere o togliere il like -->
                                    <!-- La classe $like_class cambia se l'utente ha già messo like -->
                                    <!-- data-id contiene l'ID dell'opera per permettere azioni via JS -->
                                    <button class="btn-like <?php echo $like_class; ?>" data-id="<?php echo $opera['ID_Opera']; ?>"><?php echo $heart; ?></button>
                                    

                                    <!-- Pulsante per aprire i commenti dell'opera -->
                                    <!-- data-id identifica a quale opera si riferiscono i commenti -->
                                    <button class="btn-comment" data-id="<?php echo $opera['ID_Opera']; ?>">💬</button>
                                </div>
                            </div>

                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="inter empty">Nessuna opera trovata.</p>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- --- MODAL COMMENTI FULL SCREEN (UNA SOLA VOLTA) --- -->
    <div id="comment-modal" style="display:none;">
        <div class="modal-content">
            
            <!-- &times; è un entità HTML che rappresenta una X-->
            <span id="close-modal">&times;</span>
            <h2 class="modal-title">Commenti</h2>

            <div class="modal-body" id="modal-comment-list">
                <p class="loading">Caricamento commenti...</p>
            </div>

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

    <?php include '../includes/footer.php'; ?>

    <script src="/Nexus-Space/assets/js/dettagli.js"></script>
</body>
</html>