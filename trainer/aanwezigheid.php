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

// Alleen trainers mogen deze pagina gebruiken.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

// Team van de trainer.
$teamId = $_SESSION['team_id'];

$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// GEKOZEN TRAINING
// ----------------------------------------------------

// Training kan via GET of POST binnenkomen.
$trainingId = '';

if (isset($_GET['training_id'])) {
    $trainingId = $_GET['training_id'];
}

if (isset($_POST['training_id'])) {
    $trainingId = $_POST['training_id'];
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
// CONTROLEREN OF GEKOZEN TRAINING BIJ TEAM HOORT
// ----------------------------------------------------

$gekozenTraining = false;

if ($trainingId != '') {

    $sql = "SELECT *
            FROM training
            WHERE training_id = ?
            AND team_id = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $trainingId,
        $teamId
    ]);

    $gekozenTraining = $stmt->fetch();

    if (!$gekozenTraining) {
        die('Geen toegang tot deze training.');
    }
}


// ----------------------------------------------------
// DEFINITIEVE AANWEZIGHEID OPSLAAN
// ----------------------------------------------------

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['opslaan'])
    && $gekozenTraining
) {

    // Haal alle sporters van het eigen team op.
    $sql = "SELECT user_id
            FROM user
            WHERE team_id = ?
            AND rol = 'sporter'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$teamId]);

    $sportersVoorOpslaan = $stmt->fetchAll();

    $allesIngevuld = true;

    // Controleer eerst of iedere sporter een status heeft.
    foreach ($sportersVoorOpslaan as $sporter) {

        $userId = $sporter['user_id'];

        if (!isset($_POST['status'][$userId])) {
            $allesIngevuld = false;
        }
    }

    if (!$allesIngevuld) {

        $foutmelding = 'Kies voor iedere sporter aanwezig of afwezig.';

    } else {

        foreach ($sportersVoorOpslaan as $sporter) {

            $userId = $sporter['user_id'];
            $status = $_POST['status'][$userId];

            // Alleen deze twee waarden zijn toegestaan.
            if (
                $status !== 'aanwezig'
                && $status !== 'afwezig'
            ) {
                continue;
            }

            // Controleer of er al een aanwezigheid bestaat.
            $sql = "SELECT attendance_id
                    FROM attendance
                    WHERE training_id = ?
                    AND user_id = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $trainingId,
                $userId
            ]);

            $bestaandeAanwezigheid = $stmt->fetch();


            if ($bestaandeAanwezigheid) {

                // Bestaande aanwezigheid definitief maken.
                $sql = "UPDATE attendance
                        SET status = ?,
                            is_definitief = 1
                        WHERE training_id = ?
                        AND user_id = ?";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $status,
                    $trainingId,
                    $userId
                ]);

            } else {

                // Er bestaat nog geen keuze van de sporter.
                // De trainer maakt daarom een nieuwe registratie.
                $sql = "INSERT INTO attendance
                        (
                            training_id,
                            user_id,
                            status,
                            is_definitief
                        )
                        VALUES (?, ?, ?, 1)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $trainingId,
                    $userId,
                    $status
                ]);
            }
        }

        $melding = 'Definitieve aanwezigheid is opgeslagen.';
    }
}


// ----------------------------------------------------
// SPORTERS + HUIDIGE AANWEZIGHEID OPHALEN
// ----------------------------------------------------

$sporters = [];

if ($gekozenTraining) {

    $sql = "SELECT
                user.user_id,
                user.naam,
                attendance.status,
                attendance.is_definitief

            FROM user

            LEFT JOIN attendance
            ON user.user_id = attendance.user_id
            AND attendance.training_id = ?

            WHERE user.team_id = ?
            AND user.rol = 'sporter'

            ORDER BY user.naam ASC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $trainingId,
        $teamId
    ]);

    $sporters = $stmt->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <title>TeamTrack - Aanwezigheid</title>

</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Definitieve aanwezigheid</h2>


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


    <!-- TRAINING KIEZEN -->

    <h3>Kies een training</h3>

    <form method="GET">

        <select
            name="training_id"
            required
        >

            <option value="">
                Kies een training
            </option>

            <?php foreach ($trainingen as $training) { ?>

                <option
                    value="<?php echo $training['training_id']; ?>"

                    <?php
                    if ($trainingId == $training['training_id']) {
                        echo 'selected';
                    }
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $training['datum']
                        . ' - '
                        . $training['tijd']
                    );
                    ?>

                </option>

            <?php } ?>

        </select>

        <button type="submit">
            Training openen
        </button>

    </form>


    <?php if ($gekozenTraining) { ?>

        <hr>

        <h3>
            Training:
            <?php
            echo htmlspecialchars(
                $gekozenTraining['datum']
                . ' - '
                . $gekozenTraining['tijd']
            );
            ?>
        </h3>


        <?php if (count($sporters) > 0) { ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="training_id"
                    value="<?php echo $trainingId; ?>"
                >


                <?php foreach ($sporters as $sporter) { ?>

                    <div>

                        <strong>
                            <?php
                            echo htmlspecialchars($sporter['naam']);
                            ?>
                        </strong>

                        <br>


                        <label>

                            <input
                                type="radio"
                                name="status[<?php
                                    echo $sporter['user_id'];
                                ?>]"
                                value="aanwezig"

                                <?php
                                if ($sporter['status'] === 'aanwezig') {
                                    echo 'checked';
                                }
                                ?>
                            >

                            Aanwezig

                        </label>


                        <label>

                            <input
                                type="radio"
                                name="status[<?php
                                    echo $sporter['user_id'];
                                ?>]"
                                value="afwezig"

                                <?php
                                if ($sporter['status'] === 'afwezig') {
                                    echo 'checked';
                                }
                                ?>
                            >

                            Afwezig

                        </label>


                        <?php
                        if ($sporter['is_definitief'] == 1) {
                            echo '<p>Status: definitief</p>';
                        }
                        ?>

                        <hr>

                    </div>

                <?php } ?>


                <button
                    type="submit"
                    name="opslaan"
                >
                    Definitieve aanwezigheid opslaan
                </button>

            </form>

        <?php } else { ?>

            <p>
                Er zijn geen sporters in dit team.
            </p>

        <?php } ?>

    <?php } ?>


    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

</body>

</html>