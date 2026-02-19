<?php
session_start();
require_once "../config/database.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nickname = $_POST['nickname'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT ID_Utente, Password, Ruolo FROM Utenti WHERE Nickname = ?");
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        $stmt->bind_result($id, $hashed_password, $ruolo);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {

            $_SESSION['id'] = $id;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['ruolo'] = $ruolo;

            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Password errata!";
        }
    } else {
        $message = "Utente non trovato!";
    }

    $stmt->close();
}
?>
