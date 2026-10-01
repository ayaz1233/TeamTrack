<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde trainers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];


// Alleen POST gebruiken voor verwijderen.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$userId = $_POST['user_id'];


// Controleer of de sporter bij het team hoort.
$sql = "SELECT user_id
        FROM user
        WHERE user_id = ?
        AND team_id = ?
        AND rol = 'sporter'";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $userId,
    $teamId
]);

$sporter = $stmt->fetch();

if (!$sporter) {
    die('Geen toegang tot deze sporter.');
}


// Verwijder eerst aanwezigheid van deze sporter.
$sql = "DELETE FROM attendance
        WHERE user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);


// Verwijder daarna persoonlijke doelen.
$sql = "DELETE FROM goal
        WHERE user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);


// Verwijder als laatste de sporter.
$sql = "DELETE FROM user
        WHERE user_id = ?
        AND team_id = ?
        AND rol = 'sporter'";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $userId,
    $teamId
]);


// Terug naar dashboard.
header('Location: dashboard.php');
exit;
?>