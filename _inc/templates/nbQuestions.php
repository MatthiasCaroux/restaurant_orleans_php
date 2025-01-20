<?php
session_start();

$additionalcss = "nbQuestions.css";
include "_inc/templates/header.php";

$nbQuestionsDisponible = getNumberOfQuestions(); 

// if (isset($_SESSION['nb_questions'])) {
//     $nbQuestions = $_SESSION['nb_questions'];
// } else {
//     $nbQuestions = $nbQuestionsDisponible;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nbQuestionsSaisi = (int)$_POST['nb_questions'];

    if ($nbQuestionsSaisi < 1) {
        $nbQuestionsSaisi = 1;
    } elseif ($nbQuestionsSaisi > $nbQuestionsDisponible) {
        $nbQuestionsSaisi = $nbQuestionsDisponible;
    }

    $_SESSION['nb_questions'] = $nbQuestionsSaisi;

    header("Location: index.php?action=quiz");
    exit;
}
?>

<main class="nb-questions">
    <h1>Configurer le nombre de questions</h1>
    <p>Actuellement, nous pouvons vous proposer <strong><?php echo $nbQuestionsDisponible; ?></strong> questions de culture générale.</p>
    <p>Choisissez le nombre de questions que vous souhaitez pour votre quiz :</p> 
    <form method="POST">
        <label for="nb_questions">Nombre de questions par quiz :</label>
        <input type="number" name="nb_questions" id="nb_questions" value="<?php echo $nbQuestionsDisponible ?>" min="1" max="<?php echo $nbQuestionsDisponible; ?>" required>
        <button type="submit">Valider</button>
    </form>
</main>

<?php include "_inc/templates/footer.php"; ?>
