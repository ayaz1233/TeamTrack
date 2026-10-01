<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen trainers mogen hun team beheren.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];

$foutmelding = '';
$succesmelding = '';


// ----------------------------------------------------
// TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM team
        WHERE team_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$team = $stmt->fetch();

if (!$team) {
    die('Team niet gevonden.');
}


// ----------------------------------------------------
// TEAMNAAM WIJZIGEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naam = trim($_POST['naam'] ?? '');

    if ($naam === '') {

        $foutmelding = 'Vul een teamnaam in.';

    } else {

        // Alleen het eigen team wordt aangepast.
        $sql = "UPDATE team
                SET naam = ?
                WHERE team_id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $naam,
            $teamId
        ]);

        $succesmelding =
            'Teamnaam is succesvol gewijzigd.';

        // Nieuwe gegevens opnieuw ophalen.
        $sql = "SELECT *
                FROM team
                WHERE team_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$teamId]);

        $team = $stmt->fetch();
    }
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

    <title>TeamTrack - Team bewerken</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

    <h1>Team bewerken</h1>


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

        <label for="naam">
            Teamnaam
        </label>

        <br>

        <input
            type="text"
            id="naam"
            name="naam"
            value="<?php
                echo htmlspecialchars($team['naam']);
            ?>"
            required
        >

        <br><br>

        <button type="submit">
            Teamnaam opslaan
        </button>

    </form>


    <br>

    <a href="dashboard.php">
        Terug naar dashboard
    </a>

</body>

</html>