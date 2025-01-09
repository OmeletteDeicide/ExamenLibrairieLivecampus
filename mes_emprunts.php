<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour voir vos emprunts.");
}

$id_utilisateur = $_SESSION['user_id'];

$message = '';

// Logique pour retourner un livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emprunt'])) {
    $id_emprunt = $_POST['id_emprunt'];

    // Mettre à jour la date de retour effective
    $date_retour_effective = date('Y-m-d');
    $stmt = $pdo->prepare("UPDATE emprunts SET date_retour_effective = ? WHERE id_emprunt = ?");
    $stmt->execute([$date_retour_effective, $id_emprunt]);

    // Récupérer l'ID du livre associé
    $stmt = $pdo->prepare("SELECT id_livre FROM emprunts WHERE id_emprunt = ?");
    $stmt->execute([$id_emprunt]);
    $id_livre = $stmt->fetchColumn();

    // Mettre à jour le statut du livre
    $stmt = $pdo->prepare("UPDATE livres SET statut = 'disponible' WHERE id = ?");
    $stmt->execute([$id_livre]);

    $message = "Livre retourné avec succès.";
}

// Récupérer les emprunts pour l'utilisateur
$stmt = $pdo->prepare("SELECT e.id_emprunt, l.titre, e.date_emprunt, e.date_retour_prevue, e.date_retour_effective
                        FROM emprunts e
                        JOIN livres l ON e.id_livre = l.id
                        WHERE e.id_utilisateur = ?");
$stmt->execute([$id_utilisateur]);
$emprunts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Emprunts</title>
</head>
<body>
    <h1>Mes Emprunts</h1>

    <!-- Message de confirmation -->
    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID Emprunt</th>
            <th>Titre</th>
            <th>Date d'Emprunt</th>
            <th>Date de Retour Prévue</th>
            <th>Date de Retour Effective</th>
            <th>Action</th>
        </tr>
        <?php foreach ($emprunts as $emprunt): ?>
        <tr>
            <td><?= htmlspecialchars($emprunt['id_emprunt']); ?></td>
            <td><?= htmlspecialchars($emprunt['titre']); ?></td>
            <td><?= htmlspecialchars($emprunt['date_emprunt']); ?></td>
            <td><?= htmlspecialchars($emprunt['date_retour_prevue']); ?></td>
            <td><?= htmlspecialchars($emprunt['date_retour_effective'] ?? 'Non retourné'); ?></td>
            <td>
                <?php
                if ($emprunt['date_retour_effective'] === null) {
                    $date_retour_prevue = new DateTime($emprunt['date_retour_prevue']);
                    $date_actuelle = new DateTime();

                    if ($date_actuelle > $date_retour_prevue) {
                        echo "<span style='color: red;'>En retard</span>";
                    } else {
                        echo "<span>Dans les temps</span>";
                    }
                } else {
                    echo "<span>Retourné</span>";
                }
                ?>
            </td>
            <td>
                <?php if ($emprunt['date_retour_effective'] === null): ?>
                    <form method="post">
                        <input type="hidden" name="id_emprunt" value="<?= $emprunt['id_emprunt']; ?>">
                        <button type="submit">Retourner</button>
                    </form>
                <?php else: ?>
                    <span>Déjà retourné</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
