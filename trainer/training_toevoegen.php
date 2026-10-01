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

// Alleen een trainer mag deze pagina gebruiken.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

// Haal het team-ID van de trainer uit de sessie.
$teamId = $_SESSION['team_id'];

// Hier bewaren we meldingen.
$melding = '';
$foutmelding = '';

// Controleer of het formulier is verstuurd.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Haal de gegevens uit het formulier.
    $datum = $_POST['datum'];
    $tijd = $_POST['tijd'];
    $reactiedeadline = $_POST['reactiedeadline'];

    // Controleer of alle velden zijn ingevuld.
    if ($datum == '' || $tijd == '' || $reactiedeadline == '') {

        $foutmelding = 'Vul alle velden in.';

    } else {

        // Voeg de training toe aan het team van de trainer.
        // gewijzigd is 0 omdat dit een nieuwe training is.
        $sql = "INSERT INTO training
                (team_id, datum, tijd, gewijzigd, reactiedeadline)
                VALUES (?, ?, ?, 0, ?)";

        // Bereid de query veilig voor.
        $stmt = $pdo->prepare($sql);

        // Voer de query uit.
        $stmt->execute([
            $teamId,
            $datum,
            $tijd,
            $reactiedeadline
        ]);

        // Geef een duidelijke succesmelding.
        $melding = 'Training is toegevoegd.';
    }
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamTrack - Training toevoegen</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Training toevoegen</h2>

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

    <form method="POST">

        <label for="datum">Datum</label>
        <br>

        <input
            type="date"
            id="datum"
            name="datum"
            required
        >

        <br><br>

        <label for="tijd">Tijd</label>
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

    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

    <p>
        <a href="../logout.php">
            Uitloggen
        </a>
    </p>

</body>

</html>
