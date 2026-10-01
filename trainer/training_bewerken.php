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

// Team van de ingelogde trainer.
$teamId = $_SESSION['team_id'];

$foutmelding = '';


// ----------------------------------------------------
// TRAINING-ID CONTROLEREN
// ----------------------------------------------------

// Controleer of er een training-ID is meegegeven.
if (!isset($_GET['id'])) {
    die('Geen training gekozen.');
}

$trainingId = $_GET['id'];


// ----------------------------------------------------
// TRAINING OPHALEN
// ----------------------------------------------------

// We controleren ook het team-ID.
// Hierdoor kan een trainer geen training
// van een ander team bewerken.
$sql = "SELECT * FROM training
        WHERE training_id = ?
        AND team_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $trainingId,
    $teamId
]);

$training = $stmt->fetch();


// Stop als de training niet bestaat
// of niet bij het team hoort.
if (!$training) {
    die('Geen toegang tot deze training.');
}


// ----------------------------------------------------
// TRAINING BEWERKEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $datum = $_POST['datum'];
    $tijd = $_POST['tijd'];
    $reactiedeadline = $_POST['reactiedeadline'];

    // Controleer of alles is ingevuld.
    if ($datum == '' || $tijd == '' || $reactiedeadline == '') {

        $foutmelding = 'Vul alle velden in.';

    } else {

        // Werk de training bij.
        // gewijzigd wordt 1 zodat sporters kunnen zien
        // dat deze training aangepast is.
        $sql = "UPDATE training
                SET datum = ?,
                    tijd = ?,
                    reactiedeadline = ?,
                    gewijzigd = 1
                WHERE training_id = ?
                AND team_id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $datum,
            $tijd,
            $reactiedeadline,
            $trainingId,
            $teamId
        ]);

        // Ga na het opslaan terug naar het dashboard.
        header('Location: dashboard.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TeamTrack - Training bewerken</title>

<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Training bewerken</h2>


    <?php if ($foutmelding != '') { ?>

        <p>
            <?php echo htmlspecialchars($foutmelding); ?>
        </p>

    <?php } ?>


    <form method="POST">

        <label for="datum">
            Datum
        </label>

        <br>

        <input
            type="date"
            id="datum"
            name="datum"
            value="<?php echo htmlspecialchars($training['datum']); ?>"
            required
        >

        <br><br>


        <label for="tijd">
            Tijd
        </label>

        <br>

        <input
            type="time"
            id="tijd"
            name="tijd"
            value="<?php echo htmlspecialchars($training['tijd']); ?>"
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
            value="<?php
                echo date(
                    'Y-m-d\TH:i',
                    strtotime($training['reactiedeadline'])
                );
            ?>"
            required
        >

        <br><br>


        <button type="submit">
            Wijzigingen opslaan
        </button>

    </form>


    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

</body>

</html>

