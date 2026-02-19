<?php
session_start();

// Svuoto l'array di sessione
$_SESSION = array();

// Elimino il coockie della sessione (se esiste)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Elimino la sessione
session_destroy();

// Reindirizzo l'utente alla home o al login
header("Location: index.php");
exit();
?>