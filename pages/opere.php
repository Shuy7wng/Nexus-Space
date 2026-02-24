<?php
require 'auth.php';
require_once __DIR__ . "/../config/database.php";

// Controllo che l'id sia stato passato
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Evento non valido.");
}

$id_evento = intval($_GET['id']); // Sanitizzazione dell'ID

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
    <?php include __DIR__ . '/../includes/header.php'; ?>
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
                                <h3 class="playfair">
                                    <?php echo htmlspecialchars($opera['Titolo']); ?>
                                </h3>

                                <p class="autore inter">
                                    <?php echo htmlspecialchars($opera['Nome_Autore'] . " " . $opera['Cognome_Autore']); ?>
                                </p>

                                <p class="descrizione inter">
                                    <?php echo htmlspecialchars($opera['Descrizione']); ?>
                                </p>

                                <!-- Sezione Like e Commenti con solo icone -->
                                <div class="actions">
                                    <!-- Sezione Like e Commenti con numero like a sinistra -->
                                    <div class="actions">
                                        <!-- Numero di like -->
                                        <span class="like-count" id="like-count-<?php echo $opera['ID_Opera']; ?>">0</span>

                                        <!-- Bottone Like -->
                                        <button class="btn-like" data-id="<?php echo $opera['ID_Opera']; ?>">
                                            <img src="/Nexus-Space/assets/icons/heart.svg" alt="Mi piace">
                                        </button>

                                        <!-- Bottone Commenti -->
                                        <button class="btn-comment" data-id="<?php echo $opera['ID_Opera']; ?>">
                                            <img src="/Nexus-Space/assets/icons/comment.svg" alt="Commenti">
                                        </button>
                                    </div>
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
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script>
        document.querySelectorAll('.btn-like').forEach(btn => {
            btn.addEventListener('click', () => {
                const operaId = btn.dataset.id;
                const span = document.getElementById(`like-count-${operaId}`);
                // Simulazione incremento like
                span.textContent = parseInt(span.textContent) + 1;
                // Qui puoi fare una chiamata fetch/Ajax al server per salvare il like
            });
        });

        document.querySelectorAll('.btn-comment').forEach(btn => {
            btn.addEventListener('click', () => {
                const operaId = btn.dataset.id;
                // Qui puoi aprire un modal o redirect a pagina commenti
                window.location.href = `comments.php?id=${operaId}`;
            });
        });
    </script>
</body>

</html>