<?php 
require '../includes/header.php';
require '../config/database.php';

$sql = "SELECT ID_Opera, Titolo, Tipo, Anno, Percorso_File 
        FROM Opere 
        WHERE Stato = 'Accettata'
        ORDER BY ID_Opera DESC 
        LIMIT 3";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/style.css">

    <!-- Google Font elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-text">
        <h1>Nexus Space</h1>
        <p>
            Una piattaforma dedicata alla valorizzazione dell’arte contemporanea e storica.<br>
            Opere, correnti artistiche ed eventi si intrecciano in uno spazio digitale
            pensato per connettere artisti, utenti e cultura.
        </p>
    </div>
</section>

<section class="about">
    <div class="about-container">
        <h2>Cos’è Nexus Space</h2>
        <p>
            Nexus Space nasce come progetto digitale per la gestione e promozione di opere d’arte.
            Gli utenti possono esplorare dipinti e sculture, scoprire correnti artistiche,
            partecipare a eventi tematici e interagire attraverso commenti e preferenze.
        </p>

        <p>
            La piattaforma mette in relazione artisti storici, sponsor e pubblico,
            creando un ecosistema culturale dinamico e interattivo.
        </p>
    </div>
</section>
<hr>
<!-- SEZIONE OPERE -->
<div class="gallery">
    <h2>Ultime opere</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($opera = $result->fetch_assoc()): ?>
            <div class="art-card">
                <div class="art-img" 
                    style="background-image: url('/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>');">
                </div>
                <h3><?php echo htmlspecialchars($opera['Titolo']); ?></h3>
                <p>
                    <?php echo htmlspecialchars($opera['Tipo']); ?> · 
                    <?php echo htmlspecialchars($opera['Anno']); ?>
                </p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nessuna opera disponibile al momento.</p>
    <?php endif; ?>
</div><br><br><br>

<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

</body>
</html>