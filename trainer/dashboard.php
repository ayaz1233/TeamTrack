<?php

// Start de sessie.
session_start();

// Laad de databaseverbinding.
require_once '../config/database.php';

// Controleer of de gebruiker is ingelogd.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen een trainer mag deze pagina openen.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

// Haal het team-ID van de trainer uit de sessie.
$teamId = $_SESSION['team_id'];


// ----------------------------------------------------
// TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT * FROM team WHERE team_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$team = $stmt->fetch();


// ----------------------------------------------------
// SPORTERS VAN HET EIGEN TEAM OPHALEN
// ----------------------------------------------------

// Alleen gebruikers met de rol sporter
// en hetzelfde team worden opgehaald.
$sql = "SELECT * FROM user
        WHERE team_id = ?
        AND rol = 'sporter'
        ORDER BY naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$sporters = $stmt->fetchAll();


// ----------------------------------------------------
// TRAININGEN VAN HET EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT * FROM training
        WHERE team_id = ?
        ORDER BY datum ASC, tijd ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$trainingen = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <title>TeamTrack - Trainer Dashboard</title>

</head>

<body>

    <h1>TeamTrack</h1>

    <h2>
        Welkom,
        <?php echo htmlspecialchars($_SESSION['naam']); ?>
    </h2>

    <p>Je bent ingelogd als trainer.</p>


    <h3>Mijn team</h3>

    <?php if ($team) { ?>

        <p>
            <?php echo htmlspecialchars($team['naam']); ?>
        </p>

    <?php } else { ?>

        <p>Geen team gevonden.</p>

    <?php } ?>


    <h3>Sporters</h3>

    <?php if (count($sporters) > 0) { ?>

        <?php foreach ($sporters as $sporter) { ?>

            <div>

                <strong>Naam:</strong>

                <?php
                echo htmlspecialchars($sporter['naam']);
                ?>

                <br>

                <strong>E-mail:</strong>

                <?php
                echo htmlspecialchars($sporter['email']);
                ?>

                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>Er zijn nog geen sporters in dit team.</p>

    <?php } ?>


    <h3>Trainingen</h3>

    <?php if (count($trainingen) > 0) { ?>

        <?php foreach ($trainingen as $training) { ?>

            <div>

                <strong>Datum:</strong>

                <?php
                echo htmlspecialchars($training['datum']);
                ?>

                <br>

                <strong>Tijd:</strong>

                <?php
                echo htmlspecialchars($training['tijd']);
                ?>

                <br>

                <strong>Reactiedeadline:</strong>

                <?php
                echo htmlspecialchars($training['reactiedeadline']);
                ?>

                <br>

                <strong>Status:</strong>

                <?php

                if ($training['gewijzigd'] == 1) {
                    echo 'Gewijzigd';
                } else {
                    echo 'Gepland';
                }

                ?>

                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>Er zijn nog geen trainingen gepland.</p>

    <?php } ?>


    <p>
        <a href="../logout.php">
            Uitloggen
        </a>
    </p>

</body>

</html>