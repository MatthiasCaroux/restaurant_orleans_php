<?php

$additionalcss = "leaderboard.css";
include "_inc/templates/header.php";
include "_inc/bd/db.php";

try {
    $pdo = getPDO();
    $sql = "SELECT player_name, score, total_questions, fait_a FROM SCORES ORDER BY score DESC, fait_a ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    $leaderboard = [];
}
?>

<main>
    <h1>Tableau des scores</h1>

    <?php if (!empty($leaderboard)): ?>
        <table class="leaderboard">
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Nom du joueur</th>
                    <th>Score</th>
                    <th>Questions totales</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $rank => $player): ?>
                    <tr>
                        <td><?= $rank + 1 ?></td>
                        <td><?= htmlspecialchars($player['player_name']) ?></td>
                        <td><?= htmlspecialchars($player['score']) ?></td>
                        <td><?= htmlspecialchars($player['total_questions']) ?></td>
                        <td><?= htmlspecialchars(date("d/m/Y H:i", strtotime($player['fait_a']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">Aucun score disponible pour le moment.</p>
    <?php endif; ?>
</main>

<?php include "_inc/templates/footer.php"; ?>
