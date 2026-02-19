<!-- 
Un admin vede tutte le richieste da parte degli utenti.
Un admin può accettare o rifiutare una richiesta.
Un admin può modificare o eliminare un evento.
La pagina mostra le varie richieste con due semplici bottoni si puo accettare o rifiutare 

 -->
<?php 
require 'auth.php';
requireRole([1]); // 1 = Admin, solo l'Admin può visualizzare questa pagina
?>