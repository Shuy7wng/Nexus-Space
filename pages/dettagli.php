<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($conn)) {
    die("Connessione al database non trovata.");
}

// Controllo ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID opera non valido");
}

$id_opera = intval($_GET['id']);

// Query opera
$stmt = $conn->prepare("
    SELECT o.*, 
           u.Nome AS Nome_Autore, 
           u.Cognome AS Cognome_Autore,
           u.Email, 
           s.Nome AS Sponsor
    FROM opere o
    INNER JOIN Utenti u ON o.ID_Utente = u.ID_Utente
    LEFT JOIN Sponsor s ON o.ID_Sponsor = s.ID_Sponsor
    WHERE o.ID_Opera = ?
");

$stmt->bind_param("i", $id_opera);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Opera non trovata.");
}

$opera = $result->fetch_assoc();
$stmt->close();
// Recupero commenti con dati utente
$stmt_commenti = $conn->prepare("
    SELECT c.Commento, u.Nickname, u.Percorso_File
    FROM Commenti c
    INNER JOIN Utenti u ON c.ID_Utente = u.ID_Utente
    WHERE c.ID_Opera = ?
    ORDER BY c.ID_Com DESC
");

$stmt_commenti->bind_param("i", $id_opera);
$stmt_commenti->execute();
$commenti = $stmt_commenti->get_result();
$stmt_commenti->close();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($opera['Titolo']); ?> - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/dettagli.css">
    <!-- Google Font elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="opera-dettaglio">
        <div class="container">
            <div class="opera-layout">
                <!-- IMMAGINE -->
                <div class="opera-img">
                    <img src="/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>"
                        alt="<?php echo htmlspecialchars($opera['Titolo']); ?>">
                </div>

                <!-- INFO -->
                <div class="opera-info">

                    <h1 class="playfair">
                        <?php echo htmlspecialchars($opera['Titolo']); ?>
                    </h1>

                    <p class="anno inter">
                        <strong>Anno:</strong>
                        <?php echo htmlspecialchars($opera['Anno']); ?>
                    </p>

                    <p class="autore inter">
                        <strong>Autore dell'opera:</strong>
                        <?php echo htmlspecialchars($opera['Nome_Autore'] . " " . $opera['Cognome_Autore']); ?>
                    </p>

                    <p class="contatti inter">
                        <strong>Contatti dell'autore:</strong>
                        <?php echo htmlspecialchars($opera['Email']); ?>
                    </p>

                    <p class="sponsor inter">
                        <strong>Sponsorizzata da:</strong>
                        <?php echo htmlspecialchars($opera['Sponsor']); ?>
                    </p>

                    <div class="descrizione">
                        <p class="inter">
                            <strong>Descrizione:</strong>
                            <?php echo nl2br(htmlspecialchars($opera['Descrizione'])); ?>
                        </p>
                    </div><br>

                    <div class="like-section">
                        <?php
                        if (!isset($_SESSION['user_id'])) {
                            echo '<a href="/Nexus-Space/pages/login.php" class="btn-like">Accedi per mettere like!</a>';
                        } else {

                            $id_utente = $_SESSION['user_id'];

                            // Controllo se ha già messo like
                            $stmt_check = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
                            $stmt_check->bind_param("ii", $id_utente, $id_opera);
                            $stmt_check->execute();
                            $stmt_check->store_result();

                            $already_liked = $stmt_check->num_rows > 0;
                            $stmt_check->close();

                            // Conteggio totale like
                            $stmt_count = $conn->prepare("SELECT COUNT(*) as totale FROM likes WHERE ID_Opera = ?");
                            $stmt_count->bind_param("i", $id_opera);
                            $stmt_count->execute();
                            $num_likes = $stmt_count->get_result()->fetch_assoc()['totale'];
                            $stmt_count->close();

                            $class = $already_liked ? "liked" : "";
                            $cuore = $already_liked ? "♥" : "♡";
                        ?>
                            <button class="btn-like <?php echo $class; ?>"
                                onclick="addLike(<?php echo $id_opera; ?>, this)">
                                <?php echo $cuore; ?> Mi piace <?php echo $num_likes; ?>
                            </button>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="commenti-section">

        <h2 class="titolo-commenti">COMMENTI</h2>

        <div class="lista-commenti">
            <?php if ($commenti->num_rows > 0): ?>
                <?php while ($commento = $commenti->fetch_assoc()): ?>

                    <div class="commento-item">

                        <div class="commento-header">
                            <img class="commento-pfp"
                                src="/Nexus-Space/<?php echo htmlspecialchars($commento['Percorso_File'] ?? 'assets/img/default.png'); ?>"
                                alt="pfp">

                            <span class="commento-nickname">
                                <?php echo htmlspecialchars($commento['Nickname']); ?>
                            </span>
                        </div>

                        <p class="commento-testo">
                            <?php echo nl2br(htmlspecialchars($commento['Commento'])); ?>
                        </p>

                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; opacity:0.7;">Nessun commento ancora.</p>
            <?php endif; ?>
        </div>

    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script>
        function addLike(idOpera, button) {

            fetch('/Nexus-Space/actions/add_like.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id_opera=' + idOpera
                })
                .then(response => response.text())
                .then(data => {

                    if (data === "not_logged") {
                        window.location.href = '/Nexus-Space/pages/login.php';
                        return;
                    }

                    let numero = button.innerText.match(/\d+/);

                    if (!numero) return;

                    let count = parseInt(numero[0]);

                    if (data === "added") {
                        button.innerText = "♥ Mi piace " + (count + 1);
                        button.classList.add("liked");
                    }

                    if (data === "removed") {
                        button.innerText = "♡ Mi piace " + (count - 1);
                        button.classList.remove("liked");
                    }

                })
                .catch(error => console.error("Errore:", error));
        }
    </script>
</body>

</html>