<?php

// Start de sessie.
// Hiermee kunnen we onthouden welke gebruiker is ingelogd.
session_start();

// Laad de databaseverbinding.
require_once 'config/database.php';

// Hier bewaren we een eventuele foutmelding.
$foutmelding = '';


// ----------------------------------------------------
// LOGIN VERWERKEN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Haal e-mail en wachtwoord uit het formulier.
    $email = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];

    // Controleer of beide velden zijn ingevuld.
    if ($email == '' || $wachtwoord == '') {

        $foutmelding = 'Vul je e-mailadres en wachtwoord in.';

    } else {

        // Zoek de gebruiker op via het e-mailadres.
        // Prepared statement beschermt tegen SQL-injection.
        $sql = "SELECT *
                FROM user
                WHERE email = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        $gebruiker = $stmt->fetch();


        // Controleer of de gebruiker bestaat
        // en of het wachtwoord klopt.
        if (
            $gebruiker
            && password_verify(
                $wachtwoord,
                $gebruiker['password_hash']
            )
        ) {

            // Maak na het inloggen een nieuw sessie-ID.
            session_regenerate_id(true);

            // Bewaar de gegevens van de gebruiker.
            $_SESSION['user_id'] =
                $gebruiker['user_id'];

            $_SESSION['naam'] =
                $gebruiker['naam'];

            $_SESSION['rol'] =
                $gebruiker['rol'];

            $_SESSION['team_id'] =
                $gebruiker['team_id'];


            // Stuur de gebruiker naar het juiste dashboard.
            if ($gebruiker['rol'] === 'trainer') {

                header('Location: trainer/dashboard.php');
                exit;

            } else {

                header('Location: sporter/dashboard.php');
                exit;
            }

        } else {

            // Algemene melding.
            // We vertellen niet of het e-mailadres
            // of wachtwoord fout was.
            $foutmelding =
                'E-mailadres of wachtwoord is onjuist.';
        }
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

    <title>TeamTrack - Inloggen</title>

    <!-- TeamTrack stylesheet -->
    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Inloggen</h2>

    <p>
        Log in om naar jouw TeamTrack-dashboard te gaan.
    </p>


    <?php if ($foutmelding != '') { ?>

        <p>
            <?php echo htmlspecialchars($foutmelding); ?>
        </p>

    <?php } ?>


    <form method="POST">

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
            Inloggen
        </button>

    </form>

</body>

</html>