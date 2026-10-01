<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen trainers mogen trainingen verwijderen.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

// Verwijderen mag alleen via POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Ongeldige aanvraag.');
}

$teamId = $_SESSION['team_id'];
$trainingId = $_POST['training_id'] ?? '';


// Controleer of een training is gekozen.
if ($trainingId === '') {
    die('Geen training gekozen.');
}


// ----------------------------------------------------
// CONTROLEREN OF TRAINING BIJ EIGEN TEAM HOORT
// ----------------------------------------------------

$sql = "SELECT *
        FROM training
        WHERE training_id = ?
        AND team_id = ?";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $trainingId,
    $teamId
]);

$training = $stmt->fetch();

if (!$training) {
    die('Training niet gevonden of geen toegang.');
}


// ----------------------------------------------------
// GEKOPPELDE GEGEVENS VERWIJDEREN
// ----------------------------------------------------

// Eerst gekoppelde oefeningen verwijderen.
$sql = "DELETE FROM training_exercise
        WHERE training_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$trainingId]);


// Daarna aanwezigheidsregistraties verwijderen.
$sql = "DELETE FROM attendance
        WHERE training_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$trainingId]);


// ----------------------------------------------------
// TRAINING VERWIJDEREN
// ----------------------------------------------------

$sql = "DELETE FROM training
        WHERE training_id = ?
        AND team_id = ?";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $trainingId,
    $teamId
]);


// Terug naar het dashboard.
header('Location: dashboard.php');
exit;

?>