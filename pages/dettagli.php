<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Validare l'ID dell'opera dal parametro GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit("ID opera non valido");
}

$id_opera = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT o.*, 
           u.Nome AS Nome_Autore, 
           u.Cognome AS Cognome_Autore,
           u.Email, s.Nome AS Sponsor
    FROM Utenti u
    INNER JOIN opere o ON u.ID_Utente = o.ID_Utente
    LEFT JOIN Sponsor s ON o.ID_Sponsor = s.ID_Sponsor
    WHERE o.ID_Opera = ?
");
$stmt->bind_param("i", $id_opera);
$stmt->execute();
$risultato = $stmt->get_result();

if ($risultato->num_rows === 0) {
    die("Opera non trovata.");
}

$opera = $risultato->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($opera['Titolo']); ?> - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/opera.css">
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
                <div class="opera-info" style="border-left: 1px solid var(--beige); padding-left: 3rem;">

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
                            echo '<a href="/Nexus-Space/pages/login.php" class="btn-like">Accedi per mettere like! </a>';
                        } else {
                            $id_utente = $_SESSION['user_id'];
                            $stmt_check = $conn->prepare("SELECT COUNT(*) as liked FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
                            $stmt_check->bind_param("ii", $id_utente, $id_opera);
                            $stmt_check->execute();
                            $check_result = $stmt_check->get_result()->fetch_assoc();

                            $already_liked = $check_result['liked'] > 0;

                            $stmt_count = $conn->prepare("SELECT COUNT(*) as total_likes FROM likes WHERE ID_Opera = ?");
                            $stmt_count->bind_param("i", $id_opera);
                            $stmt_count->execute();
                            $count_result = $stmt_count->get_result()->fetch_assoc();
                            $num_likes = $count_result['total_likes'];

                            $disabled = $already_liked ? "disabled" : "";
                            $class = $already_liked ? "liked" : "";
                        ?>
                            <button class="btn-like <?php echo $class; ?>" <?php echo $disabled; ?>
                                onclick="addLike(<?php echo $id_opera; ?>, this)">
                                ♡ Mi piace <?php echo $num_likes; ?>
                            </button>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script>
        function addLike(idOpera, button) {

            fetch('/Nexus-Space/actions/add_like.php', {
                    method: 'POST',
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

                    if (data === "added") {
                        // Aggiunge il like
                        if (numero) {
                            let nuovoNumero = parseInt(numero[0]) + 1;
                            button.innerText = "♥ Mi piace " + nuovoNumero;
                            button.disabled = true;
                            button.classList.add("liked");
                        }
                    } else if (data === "removed") {
                        // Rimuove il like
                        if (numero) {
                            let nuovoNumero = parseInt(numero[0]) - 1;
                            button.innerText = "♡ Mi piace " + nuovoNumero;
                            button.disabled = false;
                            button.classList.remove("liked");
                        }
                    }

                })
                .catch(error => {
                    console.error("Errore:", error);
                });
        }
    </script>
</body>

</html>