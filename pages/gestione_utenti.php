<?php require 'auth.php';
requireRole([1]); // solo admin
require_once "../config/database.php";

$search = $_GET['search'] ?? ''; // search bar
$searchParam = "%$search%";

// Query per ottenere gli utenti con ruolo Admin o Visitatore, escludendo l'utente loggato e filtrando per ID o Nickname
$query = "SELECT u.ID_Utente, u.Nickname, u.Email, r.Nome_Ruolo, u.ID_Ruolo
          FROM utenti u
          INNER JOIN ruoli r ON u.ID_Ruolo = r.ID_Ruolo
          WHERE r.Nome_Ruolo IN ('Admin','Visitatore') 
            AND u.ID_Utente != ?                            
            AND (u.ID_Utente LIKE ? OR u.Nickname LIKE ?)
          ORDER BY u.ID_Utente ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $_SESSION['user_id'], $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Utenti - Nexus Space</title>
    <link rel="stylesheet" href="/Nexus-Space/assets/css/base.css">
    <link rel="stylesheet" href="/Nexus-Space/assets/css/utenti.css">

    <!-- Google Font elegante -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <div class="page-wrapper">
        <h1 class="page-title">Gestione Utenti</h1>
        <div class="search-container">
            <form method="GET">
                <input type="text" name="search" placeholder="Cerca per ID o Nickname" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-action">Cerca</button>
            </form>
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nickname</th>
                    <th>Email</th>
                    <th>Ruolo</th>
                    <th>Operazioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while($utente = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $utente['ID_Utente']; ?></td>
                        <td><?php echo htmlspecialchars($utente['Nickname']); ?></td>
                        <td><?php echo htmlspecialchars($utente['Email']); ?></td>
                        <td><?php echo htmlspecialchars($utente['Nome_Ruolo']); ?></td>
                        <td> 
                            <!--Se un utente è Visitatore, metto il tasto 'Promuovi', se invece è Admin metto il tasto 'Retrocedi' -->
                            <?php if ($utente['ID_Ruolo'] == 3): // Visitatore ?>
                                <a href="cambia_ruolo.php?id=<?php echo $utente['ID_Utente']; ?>&ruolo=1" class="btn-action">Promuovi</a>
                            <?php elseif ($utente['ID_Ruolo'] == 1): // Admin ?>
                                <a href="cambia_ruolo.php?id=<?php echo $utente['ID_Utente']; ?>&ruolo=3" class="btn-action">Retrocedi</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>