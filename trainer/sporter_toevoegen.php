<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde trainers mogen deze pagina gebruiken.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];

$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// SPORTER TOEVOEGEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];

    if (
        $naam == ''
        || $email == ''
        || $wachtwoord == ''
    ) {

        $foutmelding = 'Vul alle velden in.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $foutmelding = 'Vul een geldig e-mailadres in.';

    } else {

        // Controleer of het e-mailadres al bestaat.
        $sql = "SELECT user_id
                FROM user
                WHERE email = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        $bestaandeGebruiker = $stmt->fetch();

        if ($bestaandeGebruiker) {

            $foutmelding =
                'Dit e-mailadres is al in gebruik.';

        } else {

            // Wachtwoord veilig opslaan.
            $passwordHash =
                password_hash(
                    $wachtwoord,
                    PASSWORD_DEFAULT
                );

            // Nieuwe gebruiker wordt automatisch
            // sporter van het team van deze trainer.
            $sql = "INSERT INTO user
                    (
                        team_id,
                        naam,
                        email,
                        password_hash,
                        rol
                    )
                    VALUES (?, ?, ?, ?, 'sporter')";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $teamId,
                $naam,
                $email,
                $passwordHash
            ]);

            $melding = 'Sporter is toegevoegd.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamTrack - Sporter toevoegen</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Sporter toevoegen</h2>

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


        <label for="email">
            E-mailadres
        </label>

        <br>

        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <br><br>


        <label for="wachtwoord">
            Wachtwoord
        </label>

        <br>

        <input
            type="password"
            id="wachtwoord"
            name="wachtwoord"
            required
        >

        <br><br>


        <button type="submit">
            Sporter toevoegen
        </button>

    </form>


    <p>
        <a href="dashboard.php">
            Terug naar dashboard
        </a>
    </p>

</body>

</html>
