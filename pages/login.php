<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errore = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($login) && !empty($password)) {

        // Cerca per Email O Nickname
        $stmt = $conn->prepare("
            SELECT ID_Utente, ID_Ruolo, Password 
            FROM Utenti 
            WHERE Email = ? OR Nickname = ?
        ");

        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $utente = $result->fetch_assoc();
            if (password_verify($password, $utente['Password'])) {
                $_SESSION['user_id'] = $utente['ID_Utente'];
                $_SESSION['role'] = $utente['ID_Ruolo'];
                header("Location: /Nexus-Space/pages/index.php");
                exit();
            } else {
                $errore = "Password errata.";
            }
        } else {
            $errore = "Email o Nickname non trovato.";
        }
        $stmt->close();
    } else {
        $errore = "Compila tutti i campi.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nexus</title>

    <!-- ATTENZIONE: stesso percorso maiuscolo/minuscolo -->
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/login.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="form-wrapper">
    <div class="form-container">
        <h2>Login</h2>

        <?php if ($errore): ?>
            <p class="error"><?php echo $errore; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="login" placeholder="Email o Nickname" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Accedi</button>
            <p>
                Non hai un account?
                <a href="signup.php">Registrati</a>
            </p>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>