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

    // Nieuwe trainingstijd uit de dropdowns.
    $trainingUur = $_POST['training_uur'] ?? '';
    $trainingMinuut = $_POST['training_minuut'] ?? '';

    // Nieuwe reactiedeadline uit de dropdowns.
    $deadlineDatum = $_POST['deadline_datum'] ?? '';
    $deadlineUur = $_POST['deadline_uur'] ?? '';
    $deadlineMinuut = $_POST['deadline_minuut'] ?? '';

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

        // Maak de trainingstijd.
        $tijd = $trainingUur . ':' . $trainingMinuut;

        // Maak de reactiedeadline.
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

        if (
            $trainingMoment === false ||
            $deadlineMoment === false
        ) {

            $foutmelding =
                'De datum of tijd is niet geldig.';

        } elseif ($deadlineMoment >= $trainingMoment) {

            $foutmelding =
                'De reactiedeadline moet vóór de training liggen.';

        } else {

            // In MySQL kan de tijd bijvoorbeeld 18:00:00 zijn.
            // In het formulier gebruiken we 18:00.
            // Daarom vergelijken we alleen uur en minuten.
            $oudeTijd = substr(
                $training['tijd'],
                0,
                5
            );

            // Alleen datum- of tijdwijzigingen markeren
            // de training als gewijzigd.
            if (
                $datum !== $training['datum'] ||
                $tijd !== $oudeTijd
            ) {

                $gewijzigd = 1;

            } else {

                // Bewaar de bestaande status.
                $gewijzigd = $training['gewijzigd'];
            }


            // Training bijwerken.
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


// ----------------------------------------------------
// HUIDIGE WAARDEN VOOR HET FORMULIER
// ----------------------------------------------------

// Tijd van training.
$huidigUur = substr(
    $training['tijd'],
    0,
    2
);

$huidigeMinuut = substr(
    $training['tijd'],
    3,
    2
);


// Deadline.
$deadlineDatum = '';
$deadlineUur = '';
$deadlineMinuut = '';

if ($training['reactiedeadline'] !== null) {

    $deadlineDatum = date(
        'Y-m-d',
        strtotime($training['reactiedeadline'])
    );

    $deadlineUur = date(
        'H',
        strtotime($training['reactiedeadline'])
    );

    $deadlineMinuut = date(
        'i',
        strtotime($training['reactiedeadline'])
    );
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

        <!-- DATUM TRAINING -->

        <label for="datum">
            Datum training
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

                <option
                    value="<?php echo $uurWaarde; ?>"
                    <?php
                    if ($uurWaarde === $huidigUur) {
                        echo 'selected';
                    }
                    ?>
                >
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

            <?php
            $minuten = ['00', '15', '30', '45'];
            ?>

            <?php foreach ($minuten as $minuut) { ?>

                <option
                    value="<?php echo $minuut; ?>"
                    <?php
                    if ($minuut === $huidigeMinuut) {
                        echo 'selected';
                    }
                    ?>
                >
                    <?php echo $minuut; ?>
                </option>

            <?php } ?>

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
            value="<?php
                echo htmlspecialchars(
                    $deadlineDatum
                );
            ?>"
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

                <option
                    value="<?php echo $uurWaarde; ?>"
                    <?php
                    if ($uurWaarde === $deadlineUur) {
                        echo 'selected';
                    }
                    ?>
                >
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

            <?php foreach ($minuten as $minuut) { ?>

                <option
                    value="<?php echo $minuut; ?>"
                    <?php
                    if ($minuut === $deadlineMinuut) {
                        echo 'selected';
                    }
                    ?>
                >
                    <?php echo $minuut; ?>
                </option>

            <?php } ?>

        </select>

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