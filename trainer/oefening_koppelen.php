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

// Alleen trainers mogen oefeningen koppelen.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

// Team van de ingelogde trainer.
$teamId = $_SESSION['team_id'];

$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// OEFENING AAN TRAINING KOPPELEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $trainingId = $_POST['training_id'];
    $exerciseId = $_POST['exercise_id'];

    if ($trainingId == '' || $exerciseId == '') {

        $foutmelding = 'Kies een training en een oefening.';

    } else {

        // Controleer eerst of de training bij
        // het team van deze trainer hoort.
        $sql = "SELECT training_id
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

            $foutmelding = 'Geen toegang tot deze training.';

        } else {

            // Controleer of deze oefening al gekoppeld is.
            $sql = "SELECT *
                    FROM training_exercise
                    WHERE training_id = ?
                    AND exercise_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $trainingId,
                $exerciseId
            ]);

            $bestaandeKoppeling = $stmt->fetch();

            if ($bestaandeKoppeling) {

                $foutmelding = 'Deze oefening is al aan deze training gekoppeld.';

            } else {

                // Maak de koppeling.
                $sql = "INSERT INTO training_exercise
                        (training_id, exercise_id)
                        VALUES (?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $trainingId,
                    $exerciseId
                ]);

                $melding = 'Oefening is gekoppeld aan de training.';
            }
        }
    }
}


// ----------------------------------------------------
// TRAININGEN VAN EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM training
        WHERE team_id = ?
        ORDER BY datum ASC, tijd ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$trainingen = $stmt->fetchAll();


// ----------------------------------------------------
// OEFENINGEN OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM exercise
        ORDER BY naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$oefeningen = $stmt->fetchAll();


// ----------------------------------------------------
// BESTAANDE KOPPELINGEN VAN EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT
            training.datum,
            training.tijd,
            exercise.naam
        FROM training_exercise

        INNER JOIN training
        ON training_exercise.training_id = training.training_id

        INNER JOIN exercise
        ON training_exercise.exercise_id = exercise.exercise_id

        WHERE training.team_id = ?

        ORDER BY training.datum ASC,
                 training.tijd ASC,
                 exercise.naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$koppelingen = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamTrack - Oefening koppelen</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Oefening koppelen aan training</h2>


    <?php if ($melding != '') { ?>

        <p>
            <?php echo htmlspecialchars($melding); ?>
        </p>

    <?php } ?>


    <?php if ($foutmelding != '') { ?>

        <p>
            <?php echo htmlspecialchars($foutmelding); ?>
        </p>

    <?php } ?>


    <form method="POST">

        <label for="training_id">
            Kies een training
        </label>

        <br>

        <select
            id="training_id"
            name="training_id"
            required
        >

            <option value="">
                Kies een training
            </option>

            <?php foreach ($trainingen as $training) { ?>

                <option
                    value="<?php echo $training['training_id']; ?>"
                >
                    <?php
                    echo htmlspecialchars(
                        $training['datum'] . ' - ' .
                        $training['tijd']
                    );
                    ?>
                </option>

            <?php } ?>

        </select>

        <br><br>


        <label for="exercise_id">
            Kies een oefening
        </label>

        <br>

        <select
            id="exercise_id"
            name="exercise_id"
            required
        >

            <option value="">
                Kies een oefening
            </option>

            <?php foreach ($oefeningen as $oefening) { ?>

                <option
                    value="<?php echo $oefening['exercise_id']; ?>"
                >
                    <?php
                    echo htmlspecialchars($oefening['naam']);
                    ?>
                </option>

            <?php } ?>

        </select>

        <br><br>


        <button type="submit">
            Oefening koppelen
        </button>

    </form>


    <h3>Gekoppelde oefeningen</h3>

    <?php if (count($koppelingen) > 0) { ?>

        <?php foreach ($koppelingen as $koppeling) { ?>

            <p>

                <strong>
                    <?php
                    echo htmlspecialchars($koppeling['datum']);
                    ?>
                </strong>

                -

                <?php
                echo htmlspecialchars($koppeling['tijd']);
                ?>

                :

                <?php
                echo htmlspecialchars($koppeling['naam']);
                ?>

            </p>

        <?php } ?>

    <?php } else { ?>

        <p>
            Er zijn nog geen oefeningen gekoppeld.
        </p>

    <?php } ?>


    <p>
        <a href="oefeningen.php">
            Oefeningen beheren
        </a>
    </p>

    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

</body>

</html>
