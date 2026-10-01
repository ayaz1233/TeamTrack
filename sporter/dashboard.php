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

    // Alleen deze twee waarden zijn toegestaan.
    if (
        $status !== 'aanwezig'
        && $status !== 'afwezig'
    ) {

        $foutmelding = 'Ongeldige keuze.';

    } else {

        // Controleer of de training bij het team
        // van de sporter hoort.
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

            // Kijk of er al een aanwezigheid bestaat.
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


            // Als de trainer de aanwezigheid definitief
            // heeft gemaakt, mag de sporter niet wijzigen.
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

                    // Bestaande keuze aanpassen.
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

                        // Nieuwe keuze opslaan.
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
// PERSOONLIJKE DOELEN OPHALEN
// FE-08
// ----------------------------------------------------

// Alleen doelen van de ingelogde sporter worden opgehaald.
$sql = "SELECT *
        FROM goal
        WHERE user_id = ?
        ORDER BY goal_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$doelen = $stmt->fetchAll();


// ----------------------------------------------------
// AANWEZIGHEIDSPERCENTAGE BEREKENEN
// FE-09
// ----------------------------------------------------

// We tellen alleen definitieve registraties.
// Daardoor telt een voorlopige keuze nog niet mee.
$sql = "SELECT
            COUNT(*) AS totaal,
            SUM(
                CASE
                    WHEN status = 'aanwezig'
                    THEN 1
                    ELSE 0
                END
            ) AS aanwezig

        FROM attendance

        WHERE user_id = ?
        AND is_definitief = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$aanwezigheid = $stmt->fetch();

$totaalDefinitief = (int) $aanwezigheid['totaal'];
$aantalAanwezig = (int) $aanwezigheid['aanwezig'];


// Belangrijk:
// als er nog geen definitieve registraties zijn,
// delen we niet door 0.
if ($totaalDefinitief > 0) {

    $aanwezigheidspercentage =
        round(
            ($aantalAanwezig / $totaalDefinitief) * 100
        );

} else {

    $aanwezigheidspercentage = 0;
}


// ----------------------------------------------------
// TRAININGEN VAN HET EIGEN TEAM OPHALEN
// ----------------------------------------------------

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TeamTrack - Sporter Dashboard</title>

<link rel="stylesheet" href="../css/style.css">
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


    <!-- ============================================= -->
    <!-- MIJN VOORTGANG -->
    <!-- ============================================= -->

    <h3>Mijn voortgang</h3>

    <p>
        <strong>Aanwezigheidspercentage:</strong>

        <?php
        echo $aanwezigheidspercentage;
        ?>%
    </p>


    <?php if ($totaalDefinitief == 0) { ?>

        <p>
            Er zijn nog geen definitieve aanwezigheden geregistreerd.
        </p>

    <?php } else { ?>

        <p>
            Aanwezig bij
            <?php echo $aantalAanwezig; ?>
            van de
            <?php echo $totaalDefinitief; ?>
            definitieve trainingen.
        </p>

    <?php } ?>


    <!-- ============================================= -->
    <!-- MIJN DOELEN -->
    <!-- ============================================= -->

    <h3>Mijn doelen</h3>


    <?php if (count($doelen) > 0) { ?>

        <?php foreach ($doelen as $doel) { ?>

            <div>

                <strong>
                    <?php
                    echo htmlspecialchars($doel['titel']);
                    ?>
                </strong>

                <br>

                Voortgang:

                <?php
                echo htmlspecialchars($doel['voortgang']);
                ?>%

                <br>

                <!-- Eenvoudige voortgangsbalk. -->
                <progress
                    value="<?php
                        echo htmlspecialchars(
                            $doel['voortgang']
                        );
                    ?>"
                    max="100"
                >
                </progress>

                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>
            Je hebt nog geen persoonlijke doelen.
        </p>

    <?php } ?>


    <!-- ============================================= -->
    <!-- MIJN TRAININGEN -->
    <!-- ============================================= -->

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

                // Controleer of de deadline voorbij is.
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
