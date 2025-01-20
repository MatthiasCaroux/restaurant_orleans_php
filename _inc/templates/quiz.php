<?php
session_start();

$additionalcss = "quiz.css";
include "_inc/templates/header.php";

if (isset($_SESSION['nb_questions'])) {
    $nbQuestions = $_SESSION['nb_questions'];
    $questions = getRandomQuestions($nbQuestions);
} else {
    $questions = getQuestions();
}

$_SESSION["questions"] = $questions;

use Form\Type\Radio;
use Form\Type\Checkbox;

// Initialiser les réponses du quiz en session si elles n'existent pas
if (!isset($_SESSION['quiz_answers'])) {
    $_SESSION['quiz_answers'] = [];
}
?>

<main>
    <form method="POST" action="index.php?action=results" class="quiz-form" id="quizForm">
        <?php
        echo "<label for='player_name'>Nom du joueur :</label>";
        echo "<input type='text' name='player_name' id='player_name' required>";
        foreach ($questions as $quest) {
            echo "<h2>" . htmlspecialchars($quest["label"]) . "</h2>";
            // Générer un name unique pour chaque question
            $name = sprintf('question_%s', $quest["uuid"]);

            foreach ($quest["choices"] as $index => $choix) {
                // Générer un ID unique pour chaque choix
                $id = sprintf('%s_%d', $name, $index);

                if ($quest["type"] === "radio") {
                    $radio = new Radio($name, $choix, $choix, true);
                    $radio->setId($id);
                    echo "<div>" . $radio->render() . "</div>";
                } elseif ($quest["type"] === "checkbox") {
                    $checkbox = new Checkbox($name . '[]', $choix, $choix);
                    $checkbox->setId($id);
                    echo "<div>" . $checkbox->render() . "</div>";
                }
            }
        }
        ?>
        <br>
        <div class="btn-submit">
            <button type="submit">Soumettre</button>
        </div>
    </form>
</main>

<script>
document.getElementById('quizForm').addEventListener('submit', function(event) {
    const questions = document.querySelectorAll('.quiz-form h2');
    const unansweredQuestions = [];
    let allAnswered = true;

    questions.forEach(question => {
        const questionName = question.nextElementSibling.querySelector('input[type="radio"]').name;
        const selected = document.querySelector(`input[name="${questionName}"]:checked`);
        const container = question.parentElement; 

        if (!selected) {
            unansweredQuestions.push(question.textContent); 
            container.classList.add('unanswered'); 
            allAnswered = false;
        } else {
            container.classList.remove('unanswered'); 
        }
    });


    if (!allAnswered) {
        event.preventDefault(); 

        if (unansweredQuestions.length === questions.length) {
            alert("Veuillez répondre à toutes les questions avant de soumettre le quiz.");
        } else {
            alert("Veuillez répondre aux questions suivantes :\n- " + unansweredQuestions.join("\n- "));
        }
    }
});
</script>


<?php include "_inc/templates/footer.php"; ?>
