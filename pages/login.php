<?php
session_start();
require_once __DIR__ . '/../config/database.php'; // file con connessione al DB

$errore = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepara la query
    $stmt = $conn->prepare("SELECT ID_Utente, ID_Ruolo, Password FROM Utenti WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $utente = $result->fetch_assoc();
        if (password_verify($password, $utente['Password'])) {
            $_SESSION['user_id'] = $utente['ID_Utente'];
            $_SESSION['role'] = $utente['ID_Ruolo'];
            header("Location: /nexus-space/pages/index.php");
            exit();
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Email non trovata.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nexus</title>
    <link rel="stylesheet" href="/nexus-space/assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="form-container">
    <h2>Login</h2>
    <?php if($errore) echo "<p class='error'>$errore</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Accedi</button>
        <p>Non hai un account? <a href="signup.php">Registrati</a></p>
    </form>
</div>
</body>
</html>
