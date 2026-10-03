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

    // Tijd van de training.
    $trainingUur = $_POST['training_uur'] ?? '';
    $trainingMinuut = $_POST['training_minuut'] ?? '';

    // Datum en tijd van de reactiedeadline.
    $deadlineDatum = $_POST['deadline_datum'] ?? '';
    $deadlineUur = $_POST['deadline_uur'] ?? '';
    $deadlineMinuut = $_POST['deadline_minuut'] ?? '';

    // Controleer of alle velden zijn ingevuld.
    if (
        $datum === '' ||
        $trainingUur === '' ||
        $trainingMinuut === '' ||
        $deadlineDatum === '' ||
        $deadlineUur === '' ||
        $deadlineMinuut === ''
    ) {

        $foutmelding = 'Vul alle velden in.';

    } else {

        // Maak de tijd van de training.
        $tijd = $trainingUur . ':' . $trainingMinuut;

        // Maak datum en tijd van de deadline.
        $reactiedeadline =
            $deadlineDatum . ' ' .
            $deadlineUur . ':' .
            $deadlineMinuut . ':00';

        // Maak vergelijkbare tijdstippen.
        $trainingMoment = strtotime(
            $datum . ' ' . $tijd
        );

        $deadlineMoment = strtotime(
            $reactiedeadline
        );

        // Controleer of datum en tijd geldig zijn.
        if (
            $trainingMoment === false ||
            $deadlineMoment === false
        ) {

            $foutmelding =
                'De datum of tijd is niet geldig.';

        } elseif ($deadlineMoment >= $trainingMoment) {

            // De sporter moet vóór de training reageren.
            $foutmelding =
                'De reactiedeadline moet vóór de training liggen.';

        } else {

            // Sla de training op.
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

        <!-- DATUM TRAINING -->

        <label for="datum">
            Datum training
        </label>

        <br>

        <input
            type="date"
            id="datum"
            name="datum"
            required
        >

        <br><br>


        <!-- TIJD TRAINING -->

        <label>
            Tijd training
        </label>

        <br>

        <select
            name="training_uur"
            required
        >

            <option value="">
                Uur
            </option>

            <?php for ($uur = 0; $uur <= 23; $uur++) { ?>

                <?php
                $uurWaarde = str_pad(
                    $uur,
                    2,
                    '0',
                    STR_PAD_LEFT
                );
                ?>

                <option value="<?php echo $uurWaarde; ?>">
                    <?php echo $uurWaarde; ?>
                </option>

            <?php } ?>

        </select>


        <select
            name="training_minuut"
            required
        >

            <option value="">
                Minuten
            </option>

            <option value="00">00</option>
            <option value="15">15</option>
            <option value="30">30</option>
            <option value="45">45</option>

        </select>

        <br><br>


        <!-- REACTIEDEADLINE -->

        <h3>Reactiedeadline</h3>

        <p>
            Tot wanneer mag de sporter reageren?
        </p>


        <label for="deadline_datum">
            Datum deadline
        </label>

        <br>

        <input
            type="date"
            id="deadline_datum"
            name="deadline_datum"
            required
        >

        <br><br>


        <label>
            Tijd deadline
        </label>

        <br>

        <select
            name="deadline_uur"
            required
        >

            <option value="">
                Uur
            </option>

            <?php for ($uur = 0; $uur <= 23; $uur++) { ?>

                <?php
                $uurWaarde = str_pad(
                    $uur,
                    2,
                    '0',
                    STR_PAD_LEFT
                );
                ?>

                <option value="<?php echo $uurWaarde; ?>">
                    <?php echo $uurWaarde; ?>
                </option>

            <?php } ?>

        </select>


        <select
            name="deadline_minuut"
            required
        >

            <option value="">
                Minuten
            </option>

            <option value="00">00</option>
            <option value="15">15</option>
            <option value="30">30</option>
            <option value="45">45</option>

        </select>

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