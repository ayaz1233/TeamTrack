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


// ----------------------------------------------------
// TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM team
        WHERE team_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$team = $stmt->fetch();


// ----------------------------------------------------
// SPORTERS OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM user
        WHERE team_id = ?
        AND rol = 'sporter'
        ORDER BY naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$sporters = $stmt->fetchAll();


// ----------------------------------------------------
// TRAININGEN OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM training
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TeamTrack - Trainer Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

    <h1>TeamTrack</h1>

    <h2>
        Welkom,
        <?php echo htmlspecialchars($_SESSION['naam']); ?>
    </h2>

    <p>Je bent ingelogd als trainer.</p>


    <!-- ============================================= -->
    <!-- BEHEERMENU -->
    <!-- ============================================= -->

    <h3>Beheer</h3>

    <div>

        <p>
            <a href="team_bewerken.php">
                Team bewerken
            </a>
        </p>

        <p>
            <a href="sporter_toevoegen.php">
                Sporter toevoegen
            </a>
        </p>

        <p>
            <a href="training_toevoegen.php">
                Training toevoegen
            </a>
        </p>

        <p>
            <a href="oefeningen.php">
                Oefeningen beheren
            </a>
        </p>

        <p>
            <a href="oefening_koppelen.php">
                Oefening koppelen
            </a>
        </p>

        <p>
            <a href="aanwezigheid.php">
                Aanwezigheid registreren
            </a>
        </p>

        <p>
            <a href="doelen.php">
                Doelen beheren
            </a>
        </p>

    </div>


    <!-- ============================================= -->
    <!-- MIJN TEAM -->
    <!-- ============================================= -->

    <h3>Mijn team</h3>

    <?php if ($team) { ?>

        <div>

            <strong>
                <?php echo htmlspecialchars($team['naam']); ?>
            </strong>

            <br><br>

            <a href="team_bewerken.php">
                Team bewerken
            </a>

        </div>

    <?php } else { ?>

        <p>Geen team gevonden.</p>

    <?php } ?>


    <!-- ============================================= -->
    <!-- SPORTERS -->
    <!-- ============================================= -->

    <h3>Sporters</h3>

    <?php if (count($sporters) > 0) { ?>

        <?php foreach ($sporters as $sporter) { ?>

            <div>

                <strong>Naam:</strong>

                <?php
                echo htmlspecialchars($sporter['naam']);
                ?>

                <br>

                <strong>E-mail:</strong>

                <?php
                echo htmlspecialchars($sporter['email']);
                ?>

                <br><br>

                <a href="sporter_bewerken.php?id=<?php
                    echo $sporter['user_id'];
                ?>">
                    Bewerken
                </a>

                <form
                    method="POST"
                    action="sporter_verwijderen.php"
                    onsubmit="return confirm('Weet je zeker dat je deze sporter wilt verwijderen?');"
                >

                    <input
                        type="hidden"
                        name="user_id"
                        value="<?php
                            echo $sporter['user_id'];
                        ?>"
                    >

                    <button type="submit">
                        Verwijderen
                    </button>

                </form>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>
            Er zijn nog geen sporters in dit team.
        </p>

    <?php } ?>


    <!-- ============================================= -->
    <!-- TRAININGEN -->
    <!-- ============================================= -->

    <h3>Trainingen</h3>

    <p>
        <a href="training_toevoegen.php">
            Nieuwe training toevoegen
        </a>
    </p>


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

                <strong>Reactiedeadline:</strong>

                <?php

                if ($training['reactiedeadline'] !== null) {

                    echo htmlspecialchars(
                        $training['reactiedeadline']
                    );

                } else {

                    echo 'Geen deadline';
                }

                ?>

                <br>

                <strong>Status:</strong>

                <?php

                if ($training['gewijzigd'] == 1) {
                    echo 'Gewijzigd';
                } else {
                    echo 'Gepland';
                }

                ?>

                <br><br>


                <a href="training_bewerken.php?id=<?php
                    echo $training['training_id'];
                ?>">
                    Bewerken
                </a>


                <!-- Training verwijderen via POST -->

                <form
                    method="POST"
                    action="training_verwijderen.php"
                    onsubmit="return confirm('Weet je zeker dat je deze training wilt verwijderen?');"
                >

                    <input
                        type="hidden"
                        name="training_id"
                        value="<?php
                            echo $training['training_id'];
                        ?>"
                    >

                    <button type="submit">
                        Verwijderen
                    </button>

                </form>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>
            Er zijn nog geen trainingen gepland.
        </p>

    <?php } ?>


    <!-- ============================================= -->
    <!-- UITLOGGEN -->
    <!-- ============================================= -->

    <p>
        <a href="../logout.php">
            Uitloggen
        </a>
    </p>

</body>

</html>