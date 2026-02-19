<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus - Arte Contemporanea</title>
    <link rel="stylesheet" href="/nexus-space/assets/css/style.css">

    <!-- Google Font elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<!-- HEADER + NAV -->
<header class="header">
    <div class="logo">
        <img src="/nexus-space/assets/img/logo.png" alt="Nexus Logo">
    </div>

    <nav class="nav">
        <a href="index.php">Home</a>
        <a href="artisti.php">Artisti</a>
        <a href="eventi.php">Eventi</a>
        <a href="contatti.php">Contatti</a>
        
        <!--Immagine profilo-->
        <div class="nav-right">
            <?php
            session_start();
            // sezione momentanea perché bisognerebbe eseguire una query per ricavare l'immagine del profilo dell'utente loggato, ma per ora mettiamo un'immagine di default
            if (isset($_SESSION['user_id'])) {
                echo '<a href="/nexus-space/pages/profilo.php" class="profile-icon">
                        <img src="/nexus-space/assets/img/login-icon.png" alt="Profilo">
                      </a>';
            } else {
                echo '<a href="/nexus-space/pages/login.php" class="profile-icon">
                        <img src="/nexus-space/assets/img/login-icon.png" alt="Login">
                      </a>';
            }
            ?>
        </div>
    </nav>
</header>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-text">
        <h1>Connessioni Visive</h1>
        <p>Un dialogo tra forma, materia e spazio.</p>
        <button class="btn">Scopri la collezione</button>
    </div>
</section>

<!-- SEZIONE OPERE -->
<section class="collection">
    <h2>Opere Selezionate</h2>

    <div class="gallery">
        <div class="art-card">
            <div class="art-img"></div>
            <h3>Equilibrio</h3>
            <p>Olio su tela · 2024</p>
        </div>

        <div class="art-card">
            <div class="art-img"></div>
            <h3>Materia</h3>
            <p>Tecnica mista · 2023</p>
        </div>

        <div class="art-card">
            <div class="art-img"></div>
            <h3>Silenzio</h3>
            <p>Acrilico · 2025</p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <p>© 2026 Nexus — Galleria d'Arte Contemporanea</p>
        <p>Milano · Roma · Online</p>
    </div>
</footer>

</body>
</html>
