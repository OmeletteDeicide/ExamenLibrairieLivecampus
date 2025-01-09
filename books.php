<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Livres - Librairie XYZ</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            color: #fff;
            text-align: center;
            padding: 1em 0;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            color: #fff;
        }

        .book-image {
            max-width: 100px;
            height: auto;
        }

        button {
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Liste des Livres - Librairie XYZ</h1>
    </header>

    <div class="container">
        <!-- Logique pour emprunter un livre -->
        <?php
        require('config.php');
        session_start();

        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            die("Vous devez être connecté pour voir la liste des livres.");
        }

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_livre'])) {
            $id_livre = $_POST['id_livre'];
            $id_utilisateur = $_SESSION['user_id'];

            // Vérifier si le livre est disponible
            $stmt = $pdo->prepare("SELECT statut FROM livres WHERE id = ?");
            $stmt->execute([$id_livre]);
            $livre = $stmt->fetch();

            if ($livre && $livre['statut'] === 'disponible') {
                // Calculer la date de retour prévue
                $date_emprunt = date('Y-m-d');
                $date_retour_prevue = date('Y-m-d', strtotime('+30 days'));

                // Insérer l'emprunt
                $stmt = $pdo->prepare("INSERT INTO emprunts (id_utilisateur, id_livre, date_emprunt, date_retour_prevue)
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_utilisateur, $id_livre, $date_emprunt, $date_retour_prevue]);

                // Mettre à jour le statut du livre
                $stmt = $pdo->prepare("UPDATE livres SET statut = 'emprunté' WHERE id = ?");
                $stmt->execute([$id_livre]);

                $message = "Livre emprunté avec succès.";
            } else {
                $message = "Ce livre n'est pas disponible.";
            }
        }

        // Récupérer les livres
        $query = "SELECT * FROM livres";
        $stmt = $pdo->query($query);
        ?>

        <?php if (!empty($message)): ?>
            <p><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Affichage des livres -->
        <table>
            <tr>
                <th>Image</th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Date de publication</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><img class="book-image" src="<?= htmlspecialchars($row['photo_url']); ?>" alt="<?= htmlspecialchars($row['titre']); ?>"></td>
                    <td><?= htmlspecialchars($row['titre']); ?></td>
                    <td><?= htmlspecialchars($row['auteur']); ?></td>
                    <td><?= htmlspecialchars($row['date_publication']); ?></td>
                    <td><?= htmlspecialchars($row['statut']); ?></td>
                    <td>
                        <?php if ($row['statut'] === 'disponible'): ?>
                            <form method="post">
                                <input type="hidden" name="id_livre" value="<?= $row['id']; ?>">
                                <button type="submit">Emprunter</button>
                            </form>
                        <?php else: ?>
                            <span>Non disponible</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Boutons supplémentaires -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <button onclick="window.location.href = 'add_book.php'">Ajouter un livre</button>
        <?php endif; ?>
        <button onclick="window.location.href = 'index.php'">Retour à l'accueil</button>
    </div>
</body>
</html>
