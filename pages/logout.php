<?php
require 'init.php';

// Svuoto l'array di sessione
$_SESSION = [];

// Elimino la sessione
session_destroy();

// Reindirizzo l'utente alla home o al login
header("Location: index.php");
exit();
?>