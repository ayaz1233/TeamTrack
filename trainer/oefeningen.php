<?php

// Start de sessie.
session_start();

// Laad de databaseverbinding.
require_once '../config/database.php';

// Controleer of iemand is ingelogd.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen trainers mogen oefeningen beheren.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

// Hier bewaren we meldingen.
$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// NIEUWE OEFENING TOEVOEGEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Haal de gegevens uit het formulier.
    $naam = $_POST['naam'];
    $omschrijving = $_POST['omschrijving'];

    // De naam van de oefening is verplicht.
    if ($naam == '') {

        $foutmelding = 'Vul een naam voor de oefening in.';

    } else {

        // Voeg de oefening toe aan de database.
        $sql = "INSERT INTO exercise
                (naam, omschrijving)
                VALUES (?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $naam,
            $omschrijving
        ]);

        $melding = 'Oefening is toegevoegd.';
    }
}


// ----------------------------------------------------
// ALLE OEFENINGEN OPHALEN
// ----------------------------------------------------

$sql = "SELECT * FROM exercise
        ORDER BY naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$oefeningen = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TeamTrack - Oefeningen</title>

<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Oefeningen beheren</h2>


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


    <h3>Nieuwe oefening</h3>

    <form method="POST">

        <label for="naam">
            Naam
        </label>

        <br>

        <input
            type="text"
            id="naam"
            name="naam"
            required
        >

        <br><br>


        <label for="omschrijving">
            Omschrijving
        </label>

        <br>

        <textarea
            id="omschrijving"
            name="omschrijving"
            rows="4"
            cols="40"
        ></textarea>

        <br><br>


        <button type="submit">
            Oefening toevoegen
        </button>

    </form>


    <h3>Bestaande oefeningen</h3>

    <?php if (count($oefeningen) > 0) { ?>

        <?php foreach ($oefeningen as $oefening) { ?>

            <div>

                <strong>
                    <?php
                    echo htmlspecialchars($oefening['naam']);
                    ?>
                </strong>

                <br>

                <?php
                echo htmlspecialchars($oefening['omschrijving']);
                ?>

                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>Er zijn nog geen oefeningen.</p>

    <?php } ?>


    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

</body>

</html>
