<?php
session_start();

require_once __DIR__ . "/../config/database.php";
include __DIR__ . '/../includes/header.php';

// Se l'utente è loggato recupero il ruolo, altrimenti lo setto a null
$ruolo = $_SESSION['role'] ?? null;

// Recupero degli eventi dal DB
$query = "SELECT * FROM eventi ORDER BY Nome ASC";
$risultato = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Eventi - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/eventi.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <section class="hero-eventi">
        <div class="hero-text">
            <h1 class="playfair">Nexus Events</h1>
            <p class="inter">Esplora le esposizioni correnti e le opportunità della nostra galleria.</p>
        </div>
    </section>

    <main class="collection-eventi">
        <div class="container">
            
            <?php if ($ruolo == 1): ?>
                <div class="admin-actions">
                    <a href="crea_evento.php" class="btn btn-admin">Nuovo Evento</a>
                </div>
            <?php endif; ?>

            <div class="gallery-eventi">
                <?php if ($risultato->num_rows > 0): ?>
                    <?php while($evento = $risultato->fetch_assoc()): ?>
                        <article class="event-card">
                            <div class="event-img-placeholder">
                                <span class="playfair">Nexus</span>
                            </div>
                            
                            <div class="event-info">
                                <h3 class="playfair"><?php echo htmlspecialchars($evento['Nome']); ?></h3>
                                <p class="event-desc"><?php echo htmlspecialchars($evento['Descrizione']); ?></p>
                                
                                <!--Distinzione delle azioni eseguibili in base al ruolo-->
                                <div class="event-actions">
                                    <?php if ($ruolo == 1): ?>
                                        <a href="modifica_evento.php?id=<?php echo $evento['ID_Evento']; ?>" class="btn-action">Modifica</a>
                                    <?php elseif ($ruolo == 2): ?>
                                        <a href="request.php?id_evento=<?php echo $evento['ID_Evento']; ?>" class="btn-action">Invia Opera</a>
                                    <?php else: ?>
                                        <span class="badge-info">Scopri di più</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="inter" style="width: 100%; opacity: 0.6; text-align: center;">Nessun evento disponibile.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p class="playfair">&copy; 2024 Nexus Space - International Art Gallery</p>
        <p class="inter" style="font-size: 0.8rem; margin-top: 10px; opacity: 0.7;">Tutti i diritti riservati.</p>
    </footer>

</body>
</html>