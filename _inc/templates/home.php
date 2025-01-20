<?php
$additionalcss = "home.css";
include "_inc/templates/header.php";
session_unset();
?> 

<main class="home">
    <section class="intro">
        <h2>Bienvenue sur QuizMaster !</h2>
        <p>Êtes-vous prêt à tester vos connaissances et défier vos amis ?</p>
        <a href="index.php?action=nbQuestions" class="button">Commencer un quiz</a>
    </section>

    <section class="features">
        <h3>Ce que nous offrons :</h3>
        <ul>
            <li><strong>Des quiz variés :</strong> Questions sur différents sujets.</li>
            <li><strong>Classements en temps réel :</strong> Comparez vos scores avec vos amis.</li>
            <li><strong>Améliorez vos connaissances :</strong> Apprenez tout en jouant !</li>
        </ul>
    </section>

    <section class="call-to-action">
        <h3>Prêt à commencer ?</h3>
        <a href="index.php?action=nbQuestions" class="button">Lancez votre premier quiz</a>
    </section>
</main>

<?php include "_inc/templates/footer.php"; ?>

