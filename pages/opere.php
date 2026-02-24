<?php
session_start();
require 'auth.php';
require_once __DIR__ . "/../config/database.php";

// Controllo che l'id sia stato passato
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Evento non valido.");
}

$id_evento = intval($_GET['id']); // Sanitizzazione dell'ID

// Recupero delle opere dal DB
$stmt = $conn->prepare("SELECT o.*, u.Nome AS Nome_Autore, u.Cognome AS Cognome_Autore FROM opere o INNER JOIN Utenti u ON o.ID_Utente = u.ID_Utente WHERE ID_Evento = ? AND Stato = 'Accettata'");
$stmt->bind_param("i", $id_evento);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <section class="hero-opere">
        <div class="hero-text">
            <h1 class="playfair">Opere</h1>
            <p class="inter">Esplora le opere di quest'evento.</p>
        </div>
    </section>

    <main class="collection-opere">
        <div class="container">
            <div class="gallery-opere">

                <?php if ($risultato && $risultato->num_rows > 0): ?>
                    <?php while ($opera = $risultato->fetch_assoc()): ?>
                        <div class="opera-card">

                            <div class="opera-img">
                                <a href="dettagli.php?id=<?php echo $opera['ID_Opera']; ?>">
                                    <img src="/Nexus-Space/<?php echo htmlspecialchars($opera['Percorso_File']); ?>"
                                        alt="<?php echo htmlspecialchars($opera['Titolo']); ?>">
                                </a>
                            </div>

                            <div class="opera-info">
                                <h3 class="playfair"><?php echo htmlspecialchars($opera['Titolo']); ?></h3>
                                <p class="autore inter"><?php echo htmlspecialchars($opera['Nome_Autore'] . " " . $opera['Cognome_Autore']); ?></p>
                                <p class="descrizione inter"><?php echo htmlspecialchars($opera['Descrizione']); ?></p>

                                <div class="actions">
                                    <?php
                                    $id_utente = $_SESSION['user_id'] ?? 0;

                                    // Conteggio totale like
                                    $stmt_count = $conn->prepare("SELECT COUNT(*) as totale FROM likes WHERE ID_Opera = ?");
                                    $stmt_count->bind_param("i", $opera['ID_Opera']);
                                    $stmt_count->execute();
                                    $num_likes = $stmt_count->get_result()->fetch_assoc()['totale'];
                                    $stmt_count->close();

                                    // Controllo se l'utente ha già messo like
                                    $already_liked = false;
                                    if ($id_utente) {
                                        $stmt_check = $conn->prepare("SELECT 1 FROM likes WHERE ID_Utente = ? AND ID_Opera = ?");
                                        $stmt_check->bind_param("ii", $id_utente, $opera['ID_Opera']);
                                        $stmt_check->execute();
                                        $stmt_check->store_result();
                                        $already_liked = $stmt_check->num_rows > 0;
                                        $stmt_check->close();
                                    }

                                    $heart = $already_liked ? '♥' : '♡';
                                    $like_class = $already_liked ? 'liked' : '';
                                    ?>

                                    <span class="like-count" id="like-count-<?php echo $opera['ID_Opera']; ?>"><?php echo $num_likes; ?></span>
                                    <button class="btn-like <?php echo $like_class; ?>" data-id="<?php echo $opera['ID_Opera']; ?>"><?php echo $heart; ?></button>
                                    <button class="btn-comment" data-id="<?php echo $opera['ID_Opera']; ?>">💬</button>
                                </div>
                            </div>

                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="inter empty">Nessuna opera trovata.</p>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- --- MODAL COMMENTI FULL SCREEN (UNA SOLA VOLTA) --- -->
    <div id="comment-modal" style="display:none;">
        <div class="modal-content">
            <span id="close-modal">&times;</span>

            <!-- TITOLO COMMENTI -->
            <h2 class="modal-title">Commenti</h2>

            <div class="modal-body" id="modal-comment-list">
                <p class="loading">Caricamento commenti...</p>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="modal-comment-form">
                    <input type="text" name="commento" placeholder="Scrivi un commento..." required>
                    <button type="submit">Invia</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">Devi <a href="/Nexus-Space/pages/login.php">accedere</a> per commentare.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        // LIKE
        document.querySelectorAll('.btn-like').forEach(btn => {
            btn.addEventListener('click', () => {
                const operaId = btn.dataset.id;
                fetch('/Nexus-Space/actions/add_like.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id_opera=' + operaId
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data === 'not_logged') {
                            window.location.href = '/Nexus-Space/pages/login.php';
                            return;
                        }
                        let countElem = document.getElementById(`like-count-${operaId}`);
                        let count = parseInt(countElem.textContent);
                        if (data === 'added') {
                            btn.innerText = '♥';
                            btn.classList.add('liked');
                            countElem.textContent = count + 1;
                        }
                        if (data === 'removed') {
                            btn.innerText = '♡';
                            btn.classList.remove('liked');
                            countElem.textContent = count - 1;
                        }
                    }).catch(err => console.error(err));
            });
        });

        // COMMENTI
        let currentOperaId = null;
        const modal = document.getElementById('comment-modal');
        const modalBody = document.getElementById('modal-comment-list');
        const modalForm = document.getElementById('modal-comment-form');
        const closeBtn = document.getElementById('close-modal');

        document.querySelectorAll('.btn-comment').forEach(btn => {
            btn.addEventListener('click', () => {
                currentOperaId = btn.dataset.id;
                modal.style.display = 'flex';
                loadComments(currentOperaId);
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            modalBody.innerHTML = '<p class="loading">Caricamento commenti...</p>';
        });

        window.addEventListener('click', e => {
            if (e.target === modal) {
                modal.style.display = 'none';
                modalBody.innerHTML = '<p class="loading">Caricamento commenti...</p>';
            }
        });

        function loadComments(operaId) {
            fetch(`/Nexus-Space/actions/comments_handler.php?id_opera=${operaId}`)
                .then(res => res.json())
                .then(data => {
                    modalBody.innerHTML = '';
                    if (data.length === 0) {
                        modalBody.innerHTML = '<p class="no-comments">Nessun commento ancora.</p>';
                        return;
                    }
                    data.forEach(c => {
                        const div = document.createElement('div');
                        div.className = 'comment-item';
                        div.innerHTML = `<strong>${c.nickname}:</strong> ${c.commento}`;
                        modalBody.appendChild(div);
                    });
                    modalBody.scrollTop = modalBody.scrollHeight;
                });
        }

        if (modalForm) {
            modalForm.addEventListener('submit', e => {
                e.preventDefault();
                const input = modalForm.querySelector('input[name="commento"]');
                const commento = input.value.trim();
                if (!commento) return;

                fetch('/Nexus-Space/actions/comments_handler.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id_opera=${currentOperaId}&commento=${encodeURIComponent(commento)}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'not_logged') {
                            window.location.href = '/Nexus-Space/pages/login.php';
                            return;
                        }
                        input.value = '';
                        loadComments(currentOperaId);
                    });
            });
        }
    </script>
</body>

</html>