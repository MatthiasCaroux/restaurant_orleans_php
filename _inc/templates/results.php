<?php
session_start();

$additionalcss = "quiz.css";
include "_inc/templates/header.php";

$questions = $_SESSION["questions"];
$score = 0;
$totalQuestions = count($questions);
$responses = [];

foreach ($questions as $quest) {
    $_SESSION["player_name"] = $_POST["player_name"];
    $name = sprintf('question_%s', $quest["uuid"]);
    $submittedAnswer = $_POST[$name] ?? null;

    // Sauvegarder les réponses en session
    $_SESSION['quiz_answers'][$name] = $submittedAnswer;

    if ($quest["type"] === "checkbox") {
        // Si la question est une checkbox, comparer les réponses
        if (is_array($submittedAnswer)) {
            $correctAnswersCount = 0;
            $incorrectAnswersCount = 0;
            $totalAnswersCount = count($quest['correct']);
            
            // Comparer les réponses soumises avec les bonnes réponses
            foreach ($quest['correct'] as $correctAnswer) {
                if (in_array($correctAnswer, $submittedAnswer)) {
                    $correctAnswersCount++;
                }
            }

            $incorrectAnswersCount = count($submittedAnswer) - $correctAnswersCount;
            
            $scorePerCorrectAnswer = $correctAnswersCount / $totalAnswersCount;
            $scorePerIncorrectAnswer = -($incorrectAnswersCount / $totalAnswersCount);

            $scoreTotal = $scorePerCorrectAnswer + $scorePerIncorrectAnswer;
            if ($scoreTotal > 0 and $scoreTotal < 1) {
                $score += $scoreTotal;
                $isCorrect = null;
            } elseif ($scoreTotal === 1) {
                $score++;
                $isCorrect = true;
            } else {
                $isCorrect = false;
            }
        }
    } else {
        // Si la question est de type "radio"
        $isCorrect = $submittedAnswer === $quest['correct'];
        if ($isCorrect) {
            $score++;
        }
    }

    // Ajouter la réponse à la liste des réponses
    $responses[] = [
        'label' => $quest['label'],
        'submitted' => $submittedAnswer,
        'correct' => $quest['correct'],
        'isCorrect' => $isCorrect
    ];
} 

include "_inc/bd/db.php";
try {
    $playerName = $_SESSION["player_name"];
    $pdo = getPDO(); 
    $sql = "SELECT COUNT(*) FROM SCORES WHERE player_name = :player_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':player_name', $playerName, PDO::PARAM_STR);
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        $sql = "INSERT INTO JOUEURS (player_name) VALUES (:player_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':player_name', $playerName, PDO::PARAM_STR);
        $stmt->execute();
    }
    $sql = "INSERT INTO SCORES (player_name, score, total_questions) VALUES (:player_name, :score, :total_questions)";  
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':player_name', $playerName, PDO::PARAM_STR);
    $stmt->bindParam(':score', $score, PDO::PARAM_INT);
    $stmt->bindParam(':total_questions', $totalQuestions, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>

<main class="results">
    <h2>Résultats du Quiz</h2>
    <p>Votre score : <?= htmlspecialchars($score) ?>/<?= htmlspecialchars($totalQuestions) ?></p>

    <ul>
        <?php foreach ($responses as $response): ?>
            <li class="<?= ($response['isCorrect'] === null) ? 'partiel' : ($response['isCorrect'] ? 'correct' : 'incorrect') ?>">    
            <strong><?= htmlspecialchars($response['label']) ?></strong><br>
                Votre réponse : 
                <?php 
                if (is_array($response['submitted'])) {
                    echo htmlspecialchars(implode(', ', $response['submitted']));
                } else {
                    echo htmlspecialchars($response['submitted'] ?? 'Aucune réponse');
                }
                ?><br>
                Réponse correcte : 
                <?php 
                if (is_array($response['correct'])) {
                    echo htmlspecialchars(implode(', ', $response['correct']));
                } else {
                    echo htmlspecialchars($response['correct']);
                }
                ?><br>
                <?php
                if ($response['isCorrect'] === null) {
                    echo "<span class='repPartiel'>Partiellement correct</span>";
                } elseif ($response['isCorrect']) {
                    echo "<span class='repCorrect'>Correct</span>";
                } else {
                    echo "<span class='repIncorrect'>Incorrect</span>";
                }
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <button><a href="index.php?action=nbQuestions">Retour à l'accueil</a></button>
</main>

<?php include "_inc/templates/footer.php"; ?>

