<?php
require 'auth.php';
requireLogin();
requireRole([2]); // Solo Artista
require_once __DIR__ . '/../config/database.php';

// Prende dati utente
$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM utenti WHERE ID_Utente = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Prende eventi
$eventiRes = $conn->query("SELECT ID_Evento, Nome FROM eventi ORDER BY Nome ASC");

// Se invia il form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $materiale = $_POST['materiale'] ?? '';
    $tipo_tela = $_POST['tipo_tela'] ?? '';
    $anno = $_POST['anno'] ?? '';
    $id_evento = $_POST['id_evento'] ?? '';

    if ($tipo !== 'Dipinto') $tipo_tela = null;
    // Upload file
    $filePercorso = null;

    if (isset($_FILES['percorso_file']) && $_FILES['percorso_file']['error'] === 0) {

        $estensione = strtolower(pathinfo($_FILES['percorso_file']['name'], PATHINFO_EXTENSION));

        // (opzionale ma consigliato) controllo estensione
        $estensioniConsentite = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($estensione, $estensioniConsentite)) {

            // Nome univoco
            $nome_file = "opera_" . time() . "_" . $userID . "." . $estensione;

            $cartella = "../uploads/opere/";

            // Controllo se la cartella esiste, altrimenti la creo (opzionale, ma evita errori)
            if (!is_dir($cartella)) {
                mkdir($cartella, 0777, true);
            }

            $percorso_salvataggio = $cartella . $nome_file;

            if (move_uploaded_file($_FILES['percorso_file']['tmp_name'], $percorso_salvataggio)) {
                $filePercorso = "uploads/opere/" . $nome_file;
            }
        }
    }

    // Salva opera nel DB
    $stmt = $conn->prepare("
        INSERT INTO Opere 
        (Titolo, Descrizione, Tipo, Materiale, Tipo_Tela, Anno, Percorso_File, Stato, NumLike, ID_Utente, ID_ArtistaS, ID_Evento) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'In attesa', 0, ?, NULL, ?)
    ");
    $stmt->bind_param("sssssisii", $titolo, $descrizione, $tipo, $materiale, $tipo_tela, $anno, $filePercorso, $userID, $id_evento);
    $stmt->execute();

    $successMsg = "Opera inserita correttamente!";
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Inserisci Opera</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/richiesta.css">
</head>

<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="opera-form-container">
        <h2>Inserisci Nuova Opera</h2>

        <?php if (isset($successMsg)): ?>
            <p class="success-msg"><?= $successMsg ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="opera-form">
            <label>Titolo
                <input type="text" name="titolo" placeholder="Titolo" required>
            </label>

            <label>Anno
                <input type="number" name="anno" min="1900" max="2026" placeholder="Anno" required>
            </label>

            <label class="full-row">Descrizione
                <textarea name="descrizione" placeholder="Descrizione" required></textarea>
            </label>

            <label>Evento
                <select name="id_evento" required>
                    <option value="">Seleziona evento</option>
                    <?php while ($evento = $eventiRes->fetch_assoc()): ?>
                        <option value="<?= $evento['ID_Evento'] ?>"><?= htmlspecialchars($evento['Nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>

            <label>Tipo
                <select name="tipo" id="tipo-opera" onchange="toggleTipoCampi()" required>
                    <option value="">Seleziona tipo</option>
                    <option value="Dipinto">Dipinto</option>
                    <option value="Scultura">Scultura</option>
                </select>
            </label>

            <label id="tipo-tela-label" style="display:none;">Tipo Tela
                <input type="text" name="tipo_tela" placeholder="es. cotone">
            </label>

            <label id="materiale-label" style="display:none;">Materiale
                <input type="text" name="materiale" placeholder="es. marmo">
            </label>

            <!-- Upload immagine centrato -->
            <label class="full-row upload-wrapper">
                <span id="file-label">Seleziona un'immagine</span>
                <input type="file" name="percorso_file" id="file-input" accept="image/*">
                <button type="button" onclick="document.getElementById('file-input').click()">Carica Immagine</button>
            </label>

            <button type="submit">Invia Opera</button>
        </form>
    </div>

    <script>
        const fileInput = document.getElementById('file-input');
        const fileLabel = document.getElementById('file-label');

        fileInput.addEventListener('change', () => {
            fileLabel.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : "Seleziona un'immagine";
        });

        function toggleTipoCampi() {
            const tipo = document.getElementById('tipo-opera').value;
            document.getElementById('tipo-tela-label').style.display = tipo === 'Dipinto' ? 'block' : 'none';
            document.getElementById('materiale-label').style.display = tipo === 'Scultura' ? 'block' : 'none';
        }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>