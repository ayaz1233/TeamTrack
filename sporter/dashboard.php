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

// Alleen sporters mogen deze pagina gebruiken.
if ($_SESSION['rol'] !== 'sporter') {
    die('Geen toegang tot deze pagina.');
}

// Gegevens van de ingelogde sporter.
$userId = $_SESSION['user_id'];
$teamId = $_SESSION['team_id'];

$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// AANWEZIGHEID DOOR SPORTER OPSLAAN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $trainingId = $_POST['training_id'];
    $status = $_POST['status'];

    // Controleer of de status geldig is.
    if (
        $status !== 'aanwezig'
        && $status !== 'afwezig'
    ) {

        $foutmelding = 'Ongeldige keuze.';

    } else {

        // Controleer of de training bij het eigen team hoort.
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

            $foutmelding = 'Geen toegang tot deze training.';

        } else {

            // Controleer of er al een aanwezigheid bestaat.
            $sql = "SELECT *
                    FROM attendance
                    WHERE training_id = ?
                    AND user_id = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $trainingId,
                $userId
            ]);

            $attendance = $stmt->fetch();


            // ------------------------------------------------
            // DEFINITIEVE STATUS CONTROLEREN
            // ------------------------------------------------

            // Als de trainer de aanwezigheid definitief heeft
            // gemaakt, mag de sporter deze niet meer veranderen.
            if (
                $attendance
                && $attendance['is_definitief'] == 1
            ) {

                $foutmelding =
                    'Deze aanwezigheid is definitief en kan niet meer worden gewijzigd.';

            } else {

                // Controleer de reactiedeadline.
                $nu = date('Y-m-d H:i:s');

                if (
                    $training['reactiedeadline'] != null
                    && $nu > $training['reactiedeadline']
                ) {

                    $foutmelding =
                        'De reactiedeadline voor deze training is voorbij.';

                } else {

                    // Als er al een keuze bestaat,
                    // wordt deze aangepast.
                    if ($attendance) {

                        $sql = "UPDATE attendance
                                SET status = ?
                                WHERE training_id = ?
                                AND user_id = ?";

                        $stmt = $pdo->prepare($sql);

                        $stmt->execute([
                            $status,
                            $trainingId,
                            $userId
                        ]);

                    } else {

                        // Anders maken we een nieuwe registratie.
                        $sql = "INSERT INTO attendance
                                (
                                    training_id,
                                    user_id,
                                    status,
                                    is_definitief
                                )
                                VALUES (?, ?, ?, 0)";

                        $stmt = $pdo->prepare($sql);

                        $stmt->execute([
                            $trainingId,
                            $userId,
                            $status
                        ]);
                    }

                    $melding = 'Je aanwezigheid is opgeslagen.';
                }
            }
        }
    }
}


// ----------------------------------------------------
// TRAININGEN VAN EIGEN TEAM OPHALEN
// ----------------------------------------------------

// LEFT JOIN zorgt ervoor dat we ook direct
// de aanwezigheid van deze sporter kunnen tonen.
$sql = "SELECT
            training.*,
            attendance.status,
            attendance.is_definitief

        FROM training

        LEFT JOIN attendance
        ON training.training_id = attendance.training_id
        AND attendance.user_id = ?

        WHERE training.team_id = ?

        ORDER BY training.datum ASC,
                 training.tijd ASC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $userId,
    $teamId
]);

$trainingen = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <title>TeamTrack - Sporter Dashboard</title>

</head>

<body>

    <h1>TeamTrack</h1>

    <h2>
        Welkom,
        <?php echo htmlspecialchars($_SESSION['naam']); ?>
    </h2>

    <p>
        Je bent ingelogd als sporter.
    </p>


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


    <h3>Mijn trainingen</h3>


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


                <?php if ($training['gewijzigd'] == 1) { ?>

                    <strong>
                        Deze training is gewijzigd.
                    </strong>

                    <br>

                <?php } ?>


                <strong>Reactiedeadline:</strong>

                <?php
                echo htmlspecialchars(
                    $training['reactiedeadline']
                );
                ?>

                <br>


                <strong>Mijn keuze:</strong>

                <?php

                if ($training['status'] != null) {

                    echo htmlspecialchars(
                        ucfirst($training['status'])
                    );

                } else {

                    echo 'Nog niet ingevuld';
                }

                ?>

                <br><br>


                <?php

                // Bepaal of de deadline voorbij is.
                $deadlineVoorbij = false;

                if (
                    $training['reactiedeadline'] != null
                    && date('Y-m-d H:i:s')
                        > $training['reactiedeadline']
                ) {
                    $deadlineVoorbij = true;
                }

                ?>


                <?php if ($training['is_definitief'] == 1) { ?>

                    <strong>
                        Definitieve aanwezigheid:
                        <?php
                        echo htmlspecialchars(
                            ucfirst($training['status'])
                        );
                        ?>
                    </strong>

                    <p>
                        Deze status is door de trainer definitief gemaakt.
                    </p>


                <?php } elseif ($deadlineVoorbij) { ?>

                    <p>
                        De reactiedeadline is voorbij.
                    </p>


                <?php } else { ?>

                    <!-- Sporter kan vóór de deadline reageren. -->

                    <form method="POST">

                        <input
                            type="hidden"
                            name="training_id"
                            value="<?php
                                echo $training['training_id'];
                            ?>"
                        >


                        <button
                            type="submit"
                            name="status"
                            value="aanwezig"
                        >
                            Aanwezig
                        </button>


                        <button
                            type="submit"
                            name="status"
                            value="afwezig"
                        >
                            Afwezig
                        </button>

                    </form>

                <?php } ?>


                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>
            Er zijn nog geen trainingen gepland.
        </p>

    <?php } ?>


    <p>
        <a href="../logout.php">
            Uitloggen
        </a>
    </p>

</body>

</html>