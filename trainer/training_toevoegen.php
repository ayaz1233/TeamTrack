<?php

session_start();
require_once '../config/database.php';

// Alleen een ingelogde trainer mag deze pagina gebruiken.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];

$foutmelding = '';
$succesmelding = '';


// ----------------------------------------------------
// TRAINING TOEVOEGEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $datum = $_POST['datum'] ?? '';
    $tijd = $_POST['tijd'] ?? '';
    $reactiedeadline = $_POST['reactiedeadline'] ?? '';

    // Controleer of alle velden zijn ingevuld.
    if (
        $datum === '' ||
        $tijd === '' ||
        $reactiedeadline === ''
    ) {

        $foutmelding = 'Vul alle velden in.';

    } else {

        // Maak van datum en tijd één waarde.
        // Zo kunnen we deze vergelijken met de deadline.
        $trainingMoment = strtotime(
            $datum . ' ' . $tijd
        );

        $deadlineMoment = strtotime(
            $reactiedeadline
        );

        // Controleer of de ingevulde waarden geldig zijn.
        if (
            $trainingMoment === false ||
            $deadlineMoment === false
        ) {

            $foutmelding =
                'De datum, tijd of reactiedeadline is niet geldig.';

        } elseif ($deadlineMoment >= $trainingMoment) {

            // De sporter moet vóór de training reageren.
            $foutmelding =
                'De reactiedeadline moet vóór de training liggen.';

        } else {

            // Sla de training op.
            // Het team komt uit de sessie en niet uit het formulier.
            $sql = "INSERT INTO training
                    (
                        team_id,
                        datum,
                        tijd,
                        gewijzigd,
                        reactiedeadline
                    )
                    VALUES (?, ?, ?, 0, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $teamId,
                $datum,
                $tijd,
                $reactiedeadline
            ]);

            $succesmelding =
                'Training is succesvol toegevoegd.';
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

    <title>TeamTrack - Training toevoegen</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

    <h1>Training toevoegen</h1>


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
            required
        >

        <br><br>


        <button type="submit">
            Training toevoegen
        </button>

    </form>


    <br>

    <a href="dashboard.php">
        Terug naar dashboard
    </a>

</body>

</html>