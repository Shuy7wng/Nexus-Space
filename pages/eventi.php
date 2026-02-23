<?php
session_start();

require_once __DIR__ . "/../config/database.php";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/eventi.css">

    <!-- Google Font elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
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
                                    <?php else: ?>
                                        <a href="opere.php?id=<?php echo $evento['ID_Evento']; ?>" class="btn-action">Dettagli</a>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>