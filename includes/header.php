<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ruolo = $_SESSION['role'] ?? null;
?>

<!-- HEADER + NAV -->
<header class="header">
    <div class="logo">
        <img src="/nexus-space/assets/img/logo.png" alt="Nexus Logo">
    </div>

    <nav class="nav">
        <a href="index.php">Home</a>
        <a href="eventi.php">Eventi</a>
        <a href="contatti.php">Contatti</a>

        <!-- Link solo Admin -->
        <?php if ($ruolo === 1): ?>
            <a href="gestione_utenti.php">Utenti</a>
            <a href="gestione_opere.php">Gestione Opere</a>
        <?php endif; ?>

        <!-- Link solo Artista -->
        <?php if ($ruolo === 2): ?>
            <a href="le_mie_opere.php">Le mie opere</a>
            <a href="richiesta.php">Richiesta pubblicazione</a>
        <?php endif; ?>

        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/nexus-space/pages/profilo.php" class="profile-icon">
                    <img src="/nexus-space/assets/img/login-icon.png" alt="Profilo">
                </a>
            <?php else: ?>
                <a href="/nexus-space/pages/login.php" class="profile-icon">
                    <img src="/nexus-space/assets/img/login-icon.png" alt="Login">
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>