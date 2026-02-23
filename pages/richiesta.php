<?php
require 'auth.php';
requireRole([2]); // Solo Artista può accedere

require_once __DIR__ . '/../config/database.php';

// Controlla che l'utente sia loggato
$userID = $_SESSION['user_id'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit;
}

// Recupera i dati dell'utente loggato
$stmt = $conn->prepare("SELECT * FROM utenti WHERE ID_Utente = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

// Gestione invio form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $materiale = $_POST['materiale'] ?? '';
    $tipo_tela = $_POST['tipo_tela'] ?? '';
    $anno = $_POST['anno'] ?? '';

    // Se non è un dipinto, tipo_tela diventa null
    if ($tipo !== 'Dipinto') {
        $tipo_tela = null;
    }

    // percorso assoluto della cartella uploads/opere
    $uploadDir = __DIR__ . '/../../uploads/opere/';

    // crea la cartella se non esiste
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // gestione upload
    $filePercorso = 'uploads/opere/default_opera.png'; // percorso di default
    if (isset($_FILES['percorso_file']) && $_FILES['percorso_file']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['percorso_file']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['percorso_file']['tmp_name'], $targetFile)) {
            $filePercorso = 'uploads/opere/' . $fileName; // percorso relativo al sito
        }
    }

    // Inserimento nel DB
    $stmt = $conn->prepare("INSERT INTO Opere 
        (Titolo, Descrizione, Tipo, Materiale, Tipo_Tela, Anno, Percorso_File, Stato, NumLike, ID_Utente, ID_ArtistaS) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'In attesa', 0, ?, NULL)");

    $stmt->bind_param(
        "sssssisi",
        $titolo,
        $descrizione,
        $tipo,
        $materiale,
        $tipo_tela,
        $anno,
        $filePercorso,
        $userID
    );

    $stmt->execute();
    $stmt->close();

    $successMsg = "Opera inserita correttamente!";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inserisci Opera</title>
<link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
<link rel="stylesheet" href="/Nexus-Space/assets/css/richiesta.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="opera-form-container">
    <h2>Inserisci Nuova Opera</h2>

    <?php if(isset($successMsg)): ?>
        <p class="success-msg"><?= $successMsg ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="opera-form">

        <!-- Titolo e Anno -->
        <label>
            Titolo
            <input type="text" name="titolo" placeholder="Titolo dell'opera" required>
        </label>
        <label>
            Anno
            <input type="number" name="anno" min="1900" max="2026" placeholder="Anno di creazione" required>
        </label>

        <!-- Descrizione (riga intera) -->
        <label class="full-row">
            Descrizione
            <textarea name="descrizione" placeholder="Descrizione dell'opera" required></textarea>
        </label>

        <!-- Tipo, Materiale, Tipo Tela (visibile solo se Dipinto) -->
        <label>
            Tipo
            <select name="tipo" id="tipo-opera" onchange="toggleTipoTela()" required>
                <option value="">Seleziona tipo</option>
                <option value="Dipinto">Dipinto</option>
                <option value="Scultura">Scultura</option>
            </select>
        </label>

        <label>
            Materiale
            <input type="text" name="materiale" placeholder="Materiale">
        </label>

        <label id="tipo-tela-label" style="display:none;">
            Tipo Tela
            <input type="text" name="tipo_tela" placeholder="Tipo di tela (solo dipinti)" value="null">
        </label>

        <!-- File immagine -->
        <label class="full-row">
            Immagine Opera
            <input type="file" name="percorso_file" accept="image/*">
        </label>

        <button type="submit">Invia Opera</button>
    </form>
</div>

<script>
function toggleTipoTela() {
    const tipo = document.getElementById('tipo-opera').value;
    const labelTela = document.getElementById('tipo-tela-label');
    if(tipo === 'Dipinto') {
        labelTela.style.display = 'block';
    } else {
        labelTela.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>