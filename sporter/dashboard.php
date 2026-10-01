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

// Alleen een sporter mag deze pagina openen.
if ($_SESSION['rol'] !== 'sporter') {
    die('Geen toegang tot deze pagina.');
}

// Gegevens van de ingelogde sporter.
$userId = $_SESSION['user_id'];
$teamId = $_SESSION['team_id'];

// Hier bewaren we meldingen voor de gebruiker.
$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// AANWEZIGHEID OPSLAAN
// ----------------------------------------------------

// Controleer of het aanwezigheidsformulier is verstuurd.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $trainingId = $_POST['training_id'];
    $status = $_POST['status'];

    // Alleen deze twee waarden zijn toegestaan.
    if ($status !== 'aanwezig' && $status !== 'afwezig') {

        $foutmelding = 'Ongeldige keuze.';

    } else {

        // Zoek de training.
        // We controleren ook direct of deze training
        // bij het team van de ingelogde sporter hoort.
        $sql = "SELECT * FROM training
                WHERE training_id = ?
                AND team_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$trainingId, $teamId]);

        $training = $stmt->fetch();

        // Training bestaat niet of hoort bij een ander team.
        if (!$training) {

            $foutmelding = 'Geen toegang tot deze training.';

        } else {

            // Controleer of de reactiedeadline voorbij is.
            $nu = date('Y-m-d H:i:s');

            if (
                $training['reactiedeadline'] != null &&
                $nu > $training['reactiedeadline']
            ) {

                $foutmelding = 'De reactiedeadline is voorbij.';

            } else {

                // Controleer of de sporter al een keuze heeft gemaakt.
                $sql = "SELECT * FROM attendance
                        WHERE training_id = ?
                        AND user_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$trainingId, $userId]);

                $attendance = $stmt->fetch();

                if ($attendance) {

                    // Er bestaat al een keuze.
                    // Werk de bestaande keuze bij.
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

                    // Er bestaat nog geen keuze.
                    // Voeg een nieuwe aanwezigheid toe.
                    $sql = "INSERT INTO attendance
                            (training_id, user_id, status, is_definitief)
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


// ----------------------------------------------------
// TRAININGEN OPHALEN
// ----------------------------------------------------

// Haal alleen trainingen van het eigen team op.
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

    <title>TeamTrack - Sporter Dashboard</title>

</head>

<body>

    <h1>TeamTrack</h1>

    <h2>
        Welkom,
        <?php echo htmlspecialchars($_SESSION['naam']); ?>
    </h2>

    <p>Je bent ingelogd als sporter.</p>


    <!-- Succesmelding -->
    <?php if ($melding != '') { ?>

        <p>
            <?php echo htmlspecialchars($melding); ?>
        </p>

    <?php } ?>


    <!-- Foutmelding -->
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


                <strong>Status training:</strong>

                <?php

                // Laat zien of de training is gewijzigd.
                if ($training['gewijzigd'] == 1) {

                    echo 'Gewijzigd';

                } else {

                    echo 'Gepland';
                }

                ?>

                <br>


                <strong>Reactiedeadline:</strong>

                <?php
                echo htmlspecialchars($training['reactiedeadline']);
                ?>

                <br><br>


                <?php

                // Controleer of de deadline nog niet voorbij is.
                $nu = date('Y-m-d H:i:s');

                if (
                    $training['reactiedeadline'] == null ||
                    $nu <= $training['reactiedeadline']
                ) {

                ?>


                    <!-- Formulier om aanwezigheid door te geven -->
                    <form method="POST">

                        <input
                            type="hidden"
                            name="training_id"
                            value="<?php echo $training['training_id']; ?>"
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


                <?php

                } else {

                    echo '<p>De reactiedeadline is voorbij.</p>';
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