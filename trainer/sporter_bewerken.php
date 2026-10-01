<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde trainers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];

$foutmelding = '';


// ----------------------------------------------------
// SPORTER-ID CONTROLEREN
// ----------------------------------------------------

if (!isset($_GET['id'])) {
    die('Geen sporter gekozen.');
}

$userId = $_GET['id'];


// ----------------------------------------------------
// SPORTER OPHALEN
// ----------------------------------------------------

// De teamcontrole is belangrijk.
// Een trainer kan hierdoor geen sporter
// van een ander team aanpassen.
$sql = "SELECT *
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
    die('Geen toegang tot deze sporter.');
}


// ----------------------------------------------------
// SPORTER BEWERKEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);

    if ($naam == '' || $email == '') {

        $foutmelding = 'Vul alle velden in.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $foutmelding = 'Vul een geldig e-mailadres in.';

    } else {

        // Controleer of een andere gebruiker
        // dit e-mailadres al gebruikt.
        $sql = "SELECT user_id
                FROM user
                WHERE email = ?
                AND user_id != ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $email,
            $userId
        ]);

        $bestaandeGebruiker = $stmt->fetch();

        if ($bestaandeGebruiker) {

            $foutmelding =
                'Dit e-mailadres is al in gebruik.';

        } else {

            // Naam en e-mailadres aanpassen.
            $sql = "UPDATE user
                    SET naam = ?,
                        email = ?
                    WHERE user_id = ?
                    AND team_id = ?
                    AND rol = 'sporter'";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $naam,
                $email,
                $userId,
                $teamId
            ]);

            // Terug naar dashboard.
            header('Location: dashboard.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamTrack - Sporter bewerken</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Sporter bewerken</h2>


    <?php if ($foutmelding != '') { ?>

        <p>
            <?php echo htmlspecialchars($foutmelding); ?>
        </p>

    <?php } ?>


    <form method="POST">

        <label for="naam">
            Naam
        </label>

        <br>

        <input
            type="text"
            id="naam"
            name="naam"
            value="<?php
                echo htmlspecialchars($sporter['naam']);
            ?>"
            required
        >

        <br><br>


        <label for="email">
            E-mailadres
        </label>

        <br>

        <input
            type="email"
            id="email"
            name="email"
            value="<?php
                echo htmlspecialchars($sporter['email']);
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
