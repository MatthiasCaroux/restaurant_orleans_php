<?php

function getQuestionsFile($jsonFilePath) {
    $jsonData = file_get_contents($jsonFilePath);
    $questions = json_decode($jsonData, true);
    return $questions;
}

function getQuestions() {
    $jsonData = file_get_contents("./_inc/data/model.json");
    $questions = json_decode($jsonData, true);
    return $questions;
}

function getNumberOfQuestions() {
    $jsonData = file_get_contents("./_inc/data/model.json");
    $questions = json_decode($jsonData, true);
    return count($questions);
}

function getRandomQuestions($nbQuestions) {
    $jsonData = file_get_contents("./_inc/data/model.json");
    $questions = json_decode($jsonData, true);
    shuffle($questions);
    return array_slice($questions, 0, $nbQuestions);
}

?>