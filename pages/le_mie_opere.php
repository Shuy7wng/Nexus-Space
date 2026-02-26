<?php
require 'auth.php';
requireLogin();
requireRole([2]); // Solo Artista può accedere
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$userID = $_SESSION['user_id'];

// Recupera i dati dell'utente loggato
$stmt = $conn->prepare("SELECT * FROM Opere WHERE ID_Utente = ? ORDER BY ID_Opera DESC");
$stmt->bind_param("i", $userID);
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
    <link rel="stylesheet" href="/Nexus-Space/assets/css/le_mie_opere.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>

<body>
    <section class="hero-opere">
        <div class="hero-text">
            <h1 class="playfair">Le mie opere</h1>
            <p class="inter">Esplora lo stato delle tue opere</p>
        </div>
    </section>

    <main class="collection-opere">
        <div class="container">
            <div class="gallery-opere">

                <?php if ($risultato && $risultato->num_rows > 0): ?>
                    <?php while ($opera = $risultato->fetch_assoc()): ?>

                        <div class="opera-card">

                            <div class="opera-img">
                                <a href="opera.php?id=<?php echo $opera['ID_Opera']; ?>">
                                    <img
                                        src="/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>"
                                        alt="<?php echo htmlspecialchars($opera['Titolo']); ?>">
                                </a>
                            </div>

                            <div class="opera-info">
                                <h3 class="playfair">
                                    <?php echo htmlspecialchars($opera['Titolo']); ?>
                                </h3>

                                <p class="descrizione inter">
                                    <?php echo htmlspecialchars($opera['Descrizione']); ?>
                                </p>

                                <!--Faccio diventare lo Stato una classe CSS (al posto degli spazi metto dei trattini-->
                                <p class="stato <?= strtolower(str_replace(' ', '-', $opera['Stato'])) ?>"> 
                                    <?= $opera['Stato']; ?>
                                </p>
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
</body>

</html>