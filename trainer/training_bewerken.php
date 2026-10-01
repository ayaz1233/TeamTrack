<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen trainers.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];

$foutmelding = '';
$succesmelding = '';


// ----------------------------------------------------
// TRAINING-ID CONTROLEREN
// ----------------------------------------------------

$trainingId = $_GET['id'] ?? '';

if ($trainingId === '') {
    die('Geen training gekozen.');
}


// ----------------------------------------------------
// TRAINING OPHALEN
// Alleen een training van het eigen team.
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
// WIJZIGING OPSLAAN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $datum = $_POST['datum'] ?? '';
    $tijd = $_POST['tijd'] ?? '';
    $reactiedeadline = $_POST['reactiedeadline'] ?? '';

    if (
        $datum === '' ||
        $tijd === '' ||
        $reactiedeadline === ''
    ) {

        $foutmelding = 'Vul alle velden in.';

    } else {

        // Maak vergelijkbare datum/tijd-waarden.
        $trainingMoment = strtotime(
            $datum . ' ' . $tijd
        );

        $deadlineMoment = strtotime(
            $reactiedeadline
        );

        if (
            $trainingMoment === false ||
            $deadlineMoment === false
        ) {

            $foutmelding =
                'De datum, tijd of reactiedeadline is niet geldig.';

        } elseif ($deadlineMoment >= $trainingMoment) {

            $foutmelding =
                'De reactiedeadline moet vóór de training liggen.';

        } else {

            // Controleer of datum of tijd echt is veranderd.
            if (
                $datum !== $training['datum'] ||
                $tijd !== $training['tijd']
            ) {
                $gewijzigd = 1;
            } else {
                // Bewaar bestaande status.
                $gewijzigd = $training['gewijzigd'];
            }


            // Training bijwerken.
            // team_id staat ook in de WHERE voor extra controle.
            $sql = "UPDATE training
                    SET datum = ?,
                        tijd = ?,
                        reactiedeadline = ?,
                        gewijzigd = ?
                    WHERE training_id = ?
                    AND team_id = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $datum,
                $tijd,
                $reactiedeadline,
                $gewijzigd,
                $trainingId,
                $teamId
            ]);

            $succesmelding =
                'Training is succesvol bijgewerkt.';


            // Haal de bijgewerkte training opnieuw op.
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
        }
    }
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TeamTrack - Training bewerken</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

    <h1>Training bewerken</h1>


    <?php if ($foutmelding !== '') { ?>

        <p>
            <?php echo htmlspecialchars($foutmelding); ?>
        </p>

    <?php } ?>


    <?php if ($succesmelding !== '') { ?>

        <p>
            <?php echo htmlspecialchars($succesmelding); ?>
        </p>

    <?php } ?>


    <form method="POST">

        <label for="datum">
            Datum
        </label>

        <br>

        <input
            type="date"
            id="datum"
            name="datum"
            value="<?php
                echo htmlspecialchars(
                    $training['datum']
                );
            ?>"
            required
        >

        <br><br>


        <label for="tijd">
            Tijd
        </label>

        <br>

        <input
            type="time"
            id="tijd"
            name="tijd"
            value="<?php
                echo htmlspecialchars(
                    substr($training['tijd'], 0, 5)
                );
            ?>"
            required
        >

        <br><br>


        <label for="reactiedeadline">
            Reactiedeadline
        </label>

        <br>

        <input
            type="datetime-local"
            id="reactiedeadline"
            name="reactiedeadline"
            value="<?php

                if ($training['reactiedeadline'] !== null) {

                    echo htmlspecialchars(
                        date(
                            'Y-m-d\TH:i',
                            strtotime(
                                $training['reactiedeadline']
                            )
                        )
                    );
                }

            ?>"
            required
        >

        <br><br>


        <button type="submit">
            Wijzigingen opslaan
        </button>

    </form>


    <br>

    <a href="dashboard.php">
        Terug naar dashboard
    </a>

</body>

</html>