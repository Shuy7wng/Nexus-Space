<?php
require 'auth.php';
requireRole([1]); // solo admin
require_once "../config/database.php";

// Gestisce l'approvazione o il rifiuto di un'opera tramite query string (id e azione)
if (isset($_GET['id'], $_GET['azione'])) {

    $id = (int)$_GET['id'];
    $azione = $_GET['azione'];

    if ($azione === 'accetta') {

        $stmt = $conn->prepare("UPDATE Opere SET Stato = 'Accettata' WHERE ID_Opera = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

    } elseif ($azione === 'rifiuta') {

        $stmt = $conn->prepare("UPDATE Opere SET Stato = 'Non approvata' WHERE ID_Opera = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: gestione_opere.php");
    exit;
}

// Ricava le opere in attesa di approvazione con i dati dell'autore e dell'evento
$query = "
    SELECT o.*, u.Nome AS Nome_Autore, u.Cognome AS Cognome_Autore, e.Nome AS Nome_Evento
    FROM Opere o
    INNER JOIN utenti u ON o.ID_Utente = u.ID_Utente
    INNER JOIN eventi e ON o.ID_Evento = e.ID_Evento
    WHERE o.Stato = 'In attesa'
    ORDER BY o.ID_Opera DESC
";

$risultato = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Utenti - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/opere.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/gestione_opere.css">

    <!-- Google Font elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="collection-opere">
        <div class="container">
            <div class="gallery-opere">

            <?php if ($risultato && $risultato->num_rows > 0): ?>
                <?php while ($opera = $risultato->fetch_assoc()): ?>
                    
                    <div class="opera-card">
                        
                        <div class="opera-img">
                            <a href="opera.php?id=<?php echo $opera['ID_Opera']; ?>"><img src="/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>" 
                                alt="<?php echo htmlspecialchars($opera['Titolo']); ?>"></a>
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
                        </div>

                        <!-- Con questi link la pagina richiama se stessa passando con GET l'ID il tipo di azione effettuata tramite --> 
                        <div class="opera-actions">
                            <a href="gestione_opere.php?id=<?= $opera['ID_Opera']; ?>&azione=accetta" 
                            class="btn-outline btn-accetta">Accetta</a>

                            <a href="gestione_opere.php?id=<?= $opera['ID_Opera']; ?>&azione=rifiuta" 
                            class="btn-outline btn-rifiuta">Rifiuta</a>
                        </div>
                        <br>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p class="inter empty">Nessuna opera trovata.</p>
            <?php endif; ?>

            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>