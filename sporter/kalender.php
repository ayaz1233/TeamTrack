<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen sporters mogen deze pagina bekijken.
if ($_SESSION['rol'] !== 'sporter') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];


// ----------------------------------------------------
// TRAININGEN VAN HET EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM training
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TeamTrack - Trainingskalender</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

    <h1>Trainingskalender</h1>

    <p>
        Hier zie je de geplande trainingen van jouw team.
    </p>


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
                echo htmlspecialchars(
                    substr($training['tijd'], 0, 5)
                );
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


                <?php if ($training['gewijzigd'] == 1) { ?>

                    <p>
                        Let op: de datum of tijd van deze
                        training is gewijzigd.
                    </p>

                <?php } ?>


                <strong>Reactiedeadline:</strong>

                <?php

                if ($training['reactiedeadline'] !== null) {

                    echo htmlspecialchars(
                        $training['reactiedeadline']
                    );

                } else {

                    echo 'Geen deadline ingesteld';
                }

                ?>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>
            Er zijn op dit moment geen trainingen gepland.
        </p>

    <?php } ?>


    <br>

    <a href="dashboard.php">
        Terug naar dashboard
    </a>

</body>

</html>
