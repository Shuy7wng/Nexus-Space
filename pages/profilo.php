<?php
require_once __DIR__ . "/../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /Nexus-Space/pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Recupero dati completi + Ruolo
$stmt = $conn->prepare("
    SELECT u.*, r.Nome_ruolo 
    FROM utenti u 
    JOIN ruoli r ON u.ID_Ruolo = r.ID_ruolo 
    WHERE u.ID_Utente = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Recupero Storico (Commenti e Like)
$stmt_com = $conn->prepare("SELECT c.Commento, o.Titolo FROM commenti c JOIN opere o ON c.ID_Opera = o.ID_Opera WHERE c.ID_Utente = ?");
$stmt_com->bind_param("i", $user_id);
$stmt_com->execute();
$commenti = $stmt_com->get_result();

$stmt_like = $conn->prepare("SELECT o.Titolo FROM like_opere l JOIN opere o ON l.ID_Opera = o.ID_Opera WHERE l.ID_Utente = ?");
$stmt_like->bind_param("i", $user_id);
$stmt_like->execute();
$likes = $stmt_like->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/profilo.css">
    <title>Profilo - Nexus Space</title>
</head>
<body>
    <div class="profile-container">
        <h2 class="profile-title">Il Mio Account</h2>
        
        <div class="profile-header">
            <div class="pfp-section">
                <div class="pfp-wrapper">
                    <img src="/Nexus-Space/<?php echo $user['Percorso_File'] ?? 'assets/img/default_pfp.png'; ?>">
                </div>
                <form action="profilo.php" method="POST" enctype="multipart/form-data">
                    <label for="pfp_input" class="change-pfp-label">Change pfp</label>
                    <input type="file" id="pfp_input" name="new_pfp" style="display: none;" onchange="this.form.submit()">
                </form>
            </div>
            <div class="nickname-box">
                @<?php echo htmlspecialchars($user['Nickname']); ?>
                <span class="role-badge"><?php echo $user['Nome_ruolo']; ?></span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Nome e Cognome</label>
                <p><?php echo htmlspecialchars($user['Nome'] . " " . $user['Cognome']); ?></p>
            </div>
            <div class="info-item">
                <label>Email</label>
                <p><?php echo htmlspecialchars($user['Email']); ?></p>
            </div>
            <div class="info-item">
                <label>Data di Nascita</label>
                <p><?php echo date("d/m/Y", strtotime($user['Data_nascita'])); ?></p>
            </div>
        </div>

        <div class="history-grid">
            <div class="history-section">
                <h3>Comment History</h3>
                <div class="history-list">
                    <?php while($c = $commenti->fetch_assoc()): ?>
                        <div class="history-item">
                            <strong><?php echo $c['Titolo']; ?>:</strong> "<?php echo $c['Commento']; ?>"
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="history-section">
                <h3>Like History</h3>
                <div class="history-list">
                    <?php while($l = $likes->fetch_assoc()): ?>
                        <div class="history-item">Hai messo like a <strong><?php echo $l['Titolo']; ?></strong></div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="profile-footer">
            <a href="index.php" class="btn-outline">Home</a>
            <a href="logout.php" style="margin-left:20px; color:var(--verde-oliva);">Logout</a>
        </div>
    </div>
</body>
</html>