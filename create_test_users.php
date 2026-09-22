<?php

// Laad de verbinding met de TeamTrack database.
require_once 'config/database.php';

// Testgegevens voor het team.
$teamNaam = 'Team A';

// Voeg eerst een testteam toe.
$sql = "INSERT INTO team (naam) VALUES (?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$teamNaam]);

// Haal het ID op van het team dat net is toegevoegd.
$teamId = $pdo->lastInsertId();

// Maak veilige wachtwoorden.
// password_hash zorgt ervoor dat het echte wachtwoord
// niet leesbaar in de database wordt opgeslagen.
$trainerWachtwoord = password_hash('Trainer123!', PASSWORD_DEFAULT);
$sporterWachtwoord = password_hash('Sporter123!', PASSWORD_DEFAULT);

// Voeg de trainer toe.
$sql = "INSERT INTO user (team_id, naam, email, password_hash, rol)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $teamId,
    'Test Trainer',
    'trainer@teamtrack.nl',
    $trainerWachtwoord,
    'trainer'
]);

// Voeg de sporter toe.
$stmt->execute([
    $teamId,
    'Test Sporter',
    'sporter@teamtrack.nl',
    $sporterWachtwoord,
    'sporter'
]);

echo 'Testteam en testaccounts zijn aangemaakt.';