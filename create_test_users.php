<?php

// Laad de verbinding met de TeamTrack database.
require_once 'config/database.php';


// ----------------------------------------------------
// TESTTEAM CONTROLEREN
// ----------------------------------------------------

$teamNaam = 'Team A';

// Kijk eerst of Team A al bestaat.
$sql = "SELECT team_id
        FROM team
        WHERE naam = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamNaam]);

$team = $stmt->fetch();


// Bestaat het team nog niet?
// Dan maken we het aan.
if (!$team) {

    $sql = "INSERT INTO team (naam)
            VALUES (?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$teamNaam]);

    $teamId = $pdo->lastInsertId();

} else {

    // Gebruik het bestaande team.
    $teamId = $team['team_id'];
}


// ----------------------------------------------------
// TESTTRAINER CONTROLEREN
// ----------------------------------------------------

$trainerEmail = 'trainer@teamtrack.nl';

$sql = "SELECT user_id
        FROM user
        WHERE email = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$trainerEmail]);

$trainer = $stmt->fetch();


// Alleen aanmaken als de trainer nog niet bestaat.
if (!$trainer) {

    $trainerWachtwoord =
        password_hash(
            'Trainer123!',
            PASSWORD_DEFAULT
        );

    $sql = "INSERT INTO user
            (
                team_id,
                naam,
                email,
                password_hash,
                rol
            )
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $teamId,
        'Test Trainer',
        $trainerEmail,
        $trainerWachtwoord,
        'trainer'
    ]);
}


// ----------------------------------------------------
// TESTSPORTER CONTROLEREN
// ----------------------------------------------------

$sporterEmail = 'sporter@teamtrack.nl';

$sql = "SELECT user_id
        FROM user
        WHERE email = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$sporterEmail]);

$sporter = $stmt->fetch();


// Alleen aanmaken als de sporter nog niet bestaat.
if (!$sporter) {

    $sporterWachtwoord =
        password_hash(
            'Sporter123!',
            PASSWORD_DEFAULT
        );

    $sql = "INSERT INTO user
            (
                team_id,
                naam,
                email,
                password_hash,
                rol
            )
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $teamId,
        'Test Sporter',
        $sporterEmail,
        $sporterWachtwoord,
        'sporter'
    ]);
}


// ----------------------------------------------------
// KLAAR
// ----------------------------------------------------

echo 'Testteam en testaccounts zijn klaar.<br><br>';

echo 'Trainer:<br>';
echo 'trainer@teamtrack.nl<br>';
echo 'Wachtwoord: Trainer123!<br><br>';

echo 'Sporter:<br>';
echo 'sporter@teamtrack.nl<br>';
echo 'Wachtwoord: Sporter123!';

?>