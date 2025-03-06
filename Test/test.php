<?php
// !!!!!!!!!!!!!!!!!!   décommenter pour tester la connexion à la base de données   !!!!!!!!!!!!
require __DIR__ . '/_inc/bd/db.php';

try {
    $pdo = getPDO();

    // Récupérer toutes les tables dans la base de données
    $queryTables = $pdo->query("
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
    ");

    $tables = $queryTables->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Liste des tables dans la base de données</h1>";

    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $tableName = htmlspecialchars($table['table_name']);

            echo "<h2>Table : $tableName</h2>";

            // Récupérer les colonnes et leurs types pour chaque table
            $queryColumns = $pdo->prepare("
                SELECT column_name, data_type
                FROM information_schema.columns
                WHERE table_name = :table_name
            ");
            $queryColumns->execute(['table_name' => $tableName]);
            $columns = $queryColumns->fetchAll(PDO::FETCH_ASSOC);

            if (count($columns) > 0) {
                echo "<table border='1' cellspacing='0' cellpadding='5'>";
                echo "<thead>";
                echo "<tr><th>Nom de la colonne</th><th>Type de données</th></tr>";
                echo "</thead>";
                echo "<tbody>";

                foreach ($columns as $column) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($column['column_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['data_type']) . "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Aucune colonne trouvée pour cette table.</p>";
            }
        }
    } else {
        echo "<p>Aucune table trouvée dans la base de données.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Erreur : " . $e->getMessage() . "</p>";
}
?>