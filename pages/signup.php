<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errore = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $data_nascita = $_POST['data_nascita'] ?? '';
    $nickname = trim($_POST['nickname'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    if (empty($nome) || empty($cognome) || empty($email) || empty($data_nascita) || empty($nickname) || empty($password_raw)) {
        $errore = "Compila tutti i campi.";
    } else {

        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        // Ruolo sicuro: checkbox Artista = 2, altrimenti Visitatore = 3
        $ruolo = isset($_POST['artista']) ? 2 : 3;

        // Controllo email o nickname esistenti
        $stmt_check = $conn->prepare("SELECT ID_Utente FROM Utenti WHERE Email = ? OR Nickname = ?");
        $stmt_check->bind_param("ss", $email, $nickname);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $errore = "Email o nickname già registrati.";
        } else {

            $stmt = $conn->prepare("
                INSERT INTO Utenti 
                (Nome, Cognome, Email, Data_nascita, Password, Nickname, ID_Ruolo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param("ssssssi", $nome, $cognome, $email, $data_nascita, $password, $nickname, $ruolo);

            if ($stmt->execute()) {

                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['nickname'] = $nickname;
                $_SESSION['role'] = $ruolo;

                header("Location: /Nexus-Space/pages/index.php");
                exit();
            } else {
                $errore = "Errore durante la registrazione.";
            }

            $stmt->close();
        }

        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Nexus</title>

    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/login.css">
</head>

<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
    <div class="form-wrapper">
        <div class="form-container">

            <h2>Registrati</h2>

            <?php if ($errore): ?>
                <p class="error"><?= $errore ?></p>
            <?php endif; ?>

            <form method="POST">

                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="cognome" placeholder="Cognome" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="date" name="data_nascita" required>

                <input type="text" name="nickname" placeholder="Nickname" required>
                <input type="password" name="password" placeholder="Password" required>

                <div class="role-selection">

                    <label class="artist-option">
                        <span>Registrati come Artista</span>
                        <input type="checkbox" name="artista" onclick="showDescription(this.checked)">
                    </label>

                    <div id="role-description" class="role-description">
                        Se non selezioni questa opzione verrai registrato come Visitatore.
                    </div>

                </div>
                <button type="submit">Registrati</button>

                <p>Hai già un account? <a href="login.php">Accedi</a></p>

            </form>

        </div>
    </div>

    <script>
        function showDescription(isChecked) {
            const descBox = document.getElementById('role-description');

            if (isChecked) {
                descBox.innerText = "Come Artista potrai caricare opere e gestire la tua galleria personale.";
            } 
        }
    </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>