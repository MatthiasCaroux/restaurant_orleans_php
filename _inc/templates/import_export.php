<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Exporter le fichier model.json
    if (isset($_POST['download_model'])) {
        $modelPath = __DIR__ . '/../data/model.json'; // Chemin relatif au fichier model.json

        if (file_exists($modelPath)) {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="model.json"');
            header('Content-Length: ' . filesize($modelPath));

            readfile($modelPath);
            exit;
        } else {
            $message = "Le fichier model.json est introuvable.";
        }
    }

    // Importer un fichier JSON
    if (isset($_FILES['json_file'])) {
        $modelPath = __DIR__ . '/../data/model.json'; // Chemin relatif au fichier model.json
        $file = $_FILES['json_file'];
        $jsonData = file_get_contents($file['tmp_name']);
        $newQuestions = json_decode($jsonData, true);

        // Valider que le fichier est un tableau de questions
        if ($newQuestions && is_array($newQuestions)) {
            // Charger les questions existantes depuis model.json
            if (file_exists($modelPath)) {
                $currentQuestions = json_decode(file_get_contents($modelPath), true);
                if (!$currentQuestions) {
                    $currentQuestions = [];
                }
            } else {
                $currentQuestions = [];
            }

            // Filtrer les doublons basés sur les UUID
            $existingUuids = array_column($currentQuestions, 'uuid');
            foreach ($newQuestions as $newQuestion) {
                if (!in_array($newQuestion['uuid'], $existingUuids)) {
                    $currentQuestions[] = $newQuestion;
                }
            }

            // Sauvegarder les questions mises à jour dans model.json
            file_put_contents($modelPath, json_encode($currentQuestions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $message = "Importation réussie. Les nouvelles questions ont été ajoutées.";
        } else {
            $message = "Le fichier JSON est invalide ou ne correspond pas au modèle attendu.";
        }
    }
}
?>

<?php
$additionalcss = "import_export.css";
include "_inc/templates/header.php";
?>

<main class="container">
    <h1>Import/Export JSON</h1>
    
    <!-- Message d'erreur ou de succès -->
    <?php if (!empty($message)): ?>
        <p class="message <?php echo strpos($message, 'réussie') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <!-- Formulaire d'importation -->
    <div class="import-export-form">
        <form method="POST" enctype="multipart/form-data">
            <label for="json_file">Importer un fichier JSON :</label>
            <input type="file" name="json_file" id="json_file" accept="application/json" required>
            <button type="submit" class="btn-upload">Importer</button>
        </form>

        <form method="POST">
            <input type="hidden" name="download_model" value="1">
            <button type="submit" class="btn-download">Exporter le fichier model.json</button>
        </form>
    </div>

    


</main>



<?php include "_inc/templates/footer.php"; ?>

