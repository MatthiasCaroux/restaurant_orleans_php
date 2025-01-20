<?php
session_start();

$additionalcss = "about.css";
include "_inc/templates/header.php";
?>

<main>
    <section class="about-section">
        <h1>À propos de QuizMaster</h1>
        <p>
            Bienvenue sur <strong>QuizMaster</strong>, la plateforme ultime pour tester vos connaissances tout en vous amusant ! 
            Notre objectif est de rendre l'apprentissage et le jeu accessibles à tous, avec une expérience utilisateur engageante 
            et des questions adaptées à divers sujets et niveaux.
        </p>
        <p>
            Que vous soyez un expert ou simplement curieux, QuizMaster est fait pour vous. Défiez vos amis, améliorez vos compétences 
            et suivez votre progression avec notre tableau des scores. Vous pouvez choisir parmi une variété de catégories, répondre à des 
            quiz stimulants, et voir comment vous vous classez parmi les meilleurs !
        </p>
    </section>

    <section class="features-section">
        <h2>Pourquoi choisir QuizMaster ?</h2>
        <ul>
            <li>🎯 <strong>Questions diversifiées</strong> : Des milliers de questions couvrant plusieurs catégories.</li>
            <li>🏆 <strong>Classements en direct</strong> : Comparez vos scores avec ceux des autres joueurs.</li>
            <li>📊 <strong>Suivi de progression</strong> : Enregistrez vos performances pour voir vos améliorations.</li>
            <li>🖥️ <strong>Accessibilité</strong> : Jouez depuis n'importe quel appareil, où que vous soyez.</li>
            <li>👥 <strong>Communauté</strong> : Rejoignez une communauté de passionnés du quiz.</li>
        </ul>
    </section>

    <section class="contact-section">
        <h2>Contactez-nous</h2>
        <p>
            Vous avez des suggestions, des retours ou des idées ? Nous serions ravis de les entendre ! Contactez-nous à 
            <a href="mailto:contact@quizmaster.com">contact@quizmaster.com</a>.
        </p>
        <p>
            Suivez-nous également sur nos réseaux sociaux pour rester informé des dernières nouveautés et événements :
        </p>
    </section>
</main>

<?php include "_inc/templates/footer.php"; ?>
