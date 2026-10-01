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
// NIEUW DOEL TOEVOEGEN
// ----------------------------------------------------

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['doel_toevoegen'])
) {

    $userId = $_POST['user_id'];
    $titel = trim($_POST['titel']);
    $voortgang = $_POST['voortgang'];

    // Controleer of alles is ingevuld.
    if (
        $userId == ''
        || $titel == ''
        || $voortgang == ''
    ) {

        $foutmelding = 'Vul alle velden in.';

    } elseif (
        $voortgang < 0
        || $voortgang > 100
    ) {

        // Voortgang moet tussen 0 en 100 zijn.
        $foutmelding =
            'Voortgang moet tussen 0 en 100 zijn.';

    } else {

        // Controleer of de gekozen sporter
        // echt bij het team van deze trainer hoort.
        $sql = "SELECT user_id
                FROM user
                WHERE user_id = ?
                AND team_id = ?
                AND rol = 'sporter'";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $userId,
            $teamId
        ]);

        $sporter = $stmt->fetch();


        if (!$sporter) {

            $foutmelding =
                'Geen toegang tot deze sporter.';

        } else {

            // Voeg het persoonlijke doel toe.
            $sql = "INSERT INTO goal
                    (
                        user_id,
                        titel,
                        voortgang
                    )
                    VALUES (?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $userId,
                $titel,
                $voortgang
            ]);

            $melding =
                'Persoonlijk doel is toegevoegd.';
        }
    }
}


// ----------------------------------------------------
// VOORTGANG VAN DOEL AANPASSEN
// ----------------------------------------------------

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['voortgang_opslaan'])
) {

    $goalId = $_POST['goal_id'];
    $voortgang = $_POST['voortgang'];

    if (
        $voortgang < 0
        || $voortgang > 100
    ) {

        $foutmelding =
            'Voortgang moet tussen 0 en 100 zijn.';

    } else {

        // Controleer of het doel bij een sporter
        // van het team van deze trainer hoort.
        $sql = "SELECT goal.goal_id

                FROM goal

                INNER JOIN user
                ON goal.user_id = user.user_id

                WHERE goal.goal_id = ?
                AND user.team_id = ?
                AND user.rol = 'sporter'";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $goalId,
            $teamId
        ]);

        $doel = $stmt->fetch();


        if (!$doel) {

            $foutmelding =
                'Geen toegang tot dit doel.';

        } else {

            // Pas alleen de voortgang aan.
            $sql = "UPDATE goal
                    SET voortgang = ?
                    WHERE goal_id = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $voortgang,
                $goalId
            ]);

            $melding =
                'Voortgang is aangepast.';
        }
    }
}


// ----------------------------------------------------
// SPORTERS VAN EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT
            user_id,
            naam

        FROM user

        WHERE team_id = ?
        AND rol = 'sporter'

        ORDER BY naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$sporters = $stmt->fetchAll();


// ----------------------------------------------------
// DOELEN VAN SPORTERS VAN EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT
            goal.goal_id,
            goal.titel,
            goal.voortgang,
            user.naam

        FROM goal

        INNER JOIN user
        ON goal.user_id = user.user_id

        WHERE user.team_id = ?
        AND user.rol = 'sporter'

        ORDER BY user.naam ASC,
                 goal.goal_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$doelen = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TeamTrack - Doelen</title>

<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Persoonlijke doelen</h2>


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
    <!-- NIEUW DOEL -->
    <!-- ============================================= -->

    <h3>Nieuw doel toevoegen</h3>


    <?php if (count($sporters) > 0) { ?>

        <form method="POST">

            <label for="user_id">
                Sporter
            </label>

            <br>

            <select
                id="user_id"
                name="user_id"
                required
            >

                <option value="">
                    Kies een sporter
                </option>


                <?php foreach ($sporters as $sporter) { ?>

                    <option
                        value="<?php
                            echo $sporter['user_id'];
                        ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $sporter['naam']
                        );
                        ?>

                    </option>

                <?php } ?>

            </select>


            <br><br>


            <label for="titel">
                Doel
            </label>

            <br>

            <input
                type="text"
                id="titel"
                name="titel"
                required
            >


            <br><br>


            <label for="voortgang">
                Voortgang (%)
            </label>

            <br>

            <input
                type="number"
                id="voortgang"
                name="voortgang"
                min="0"
                max="100"
                value="0"
                required
            >


            <br><br>


            <button
                type="submit"
                name="doel_toevoegen"
            >
                Doel toevoegen
            </button>

        </form>

    <?php } else { ?>

        <p>
            Er zijn nog geen sporters in dit team.
        </p>

    <?php } ?>


    <!-- ============================================= -->
    <!-- BESTAANDE DOELEN -->
    <!-- ============================================= -->

    <h3>Bestaande doelen</h3>


    <?php if (count($doelen) > 0) { ?>

        <?php foreach ($doelen as $doel) { ?>

            <div>

                <strong>Sporter:</strong>

                <?php
                echo htmlspecialchars($doel['naam']);
                ?>

                <br>


                <strong>Doel:</strong>

                <?php
                echo htmlspecialchars($doel['titel']);
                ?>

                <br>


                <strong>Huidige voortgang:</strong>

                <?php
                echo htmlspecialchars(
                    $doel['voortgang']
                );
                ?>%

                <br><br>


                <!-- Trainer kan de voortgang aanpassen. -->

                <form method="POST">

                    <input
                        type="hidden"
                        name="goal_id"
                        value="<?php
                            echo $doel['goal_id'];
                        ?>"
                    >


                    <label>
                        Nieuwe voortgang:
                    </label>

                    <input
                        type="number"
                        name="voortgang"
                        min="0"
                        max="100"
                        value="<?php
                            echo htmlspecialchars(
                                $doel['voortgang']
                            );
                        ?>"
                        required
                    >

                    %


                    <button
                        type="submit"
                        name="voortgang_opslaan"
                    >
                        Opslaan
                    </button>

                </form>


                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>
            Er zijn nog geen persoonlijke doelen.
        </p>

    <?php } ?>


    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

</body>

</html>
