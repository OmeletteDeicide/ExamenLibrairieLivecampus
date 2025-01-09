<?php
require('config.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer le nombre total de livres
$queryTotalBooks = "SELECT COUNT(*) as total_books FROM livres";
$stmtTotalBooks = $pdo->prepare($queryTotalBooks);
$stmtTotalBooks->execute();
$resultTotalBooks = $stmtTotalBooks->fetch(PDO::FETCH_ASSOC);

// Récupérer le nombre d'utilisateurs enregistrés
$queryTotalUsers = "SELECT COUNT(*) as total_users FROM utilisateurs";
$stmtTotalUsers = $pdo->prepare($queryTotalUsers);
$stmtTotalUsers->execute();
$resultTotalUsers = $stmtTotalUsers->fetch(PDO::FETCH_ASSOC);

// Vérifier les emprunts en retard
$id_utilisateur = $_SESSION['user_id'];
$queryLateEmprunts = "SELECT l.titre, e.date_retour_prevue 
                      FROM emprunts e 
                      JOIN livres l ON e.id_livre = l.id 
                      WHERE e.id_utilisateur = ? 
                      AND e.date_retour_effective IS NULL 
                      AND e.date_retour_prevue < CURDATE()";
$stmtLateEmprunts = $pdo->prepare($queryLateEmprunts);
$stmtLateEmprunts->execute([$id_utilisateur]);
$lateEmprunts = $stmtLateEmprunts->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script>
        // Fonction pour afficher une popup si des emprunts sont en retard
        function showRetardPopup() {
            let message = "Vous avez des emprunts en retard :\n\n";
            <?php foreach ($lateEmprunts as $emprunt): ?>
                message += "Livre : <?= addslashes($emprunt['titre']) ?>, Retour prévu : <?= $emprunt['date_retour_prevue'] ?>\n";
            <?php endforeach; ?>
            alert(message);
        }
    </script>
</head>
<body>
<header>
    <h1>Librairie XYZ</h1>
</header>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <ul>
            <?php if (isset($_SESSION['user'])) : ?>
                <li>Bonjour <?= htmlspecialchars($_SESSION['prenom']); ?></li>
                <li><a href="books.php">Voir la liste des livres</a></li>
                <li><a href="mes_emprunts.php">Mes Emprunts</a></li>
                <li><a href="profile.php">Mon profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            <?php else : ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <div class="container">
            <h1>Dashboard</h1>
            <div class="statistic">
                <h3>Total des Livres</h3>
                <p><?php echo htmlspecialchars($resultTotalBooks['total_books']); ?></p>
            </div>
            <div class="statistic">
                <h3>Utilisateurs Enregistrés</h3>
                <p><?php echo htmlspecialchars($resultTotalUsers['total_users']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Affichage de la popup si des emprunts sont en retard -->
<?php if (count($lateEmprunts) > 0): ?>
    <script>
        // Afficher la popup
        showRetardPopup();
    </script>
<?php endif; ?>

<!-- Footer -->
<footer>
    <div class="container">
        <p>&copy; <?= date("Y"); ?> Librairie XYZ</p>
    </div>
</footer>
</body>
</html>
