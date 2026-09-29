<?php

// Start een sessie.
// Hiermee kan PHP onthouden welke gebruiker is ingelogd.
session_start();

// Laad de verbinding met de TeamTrack database.
require_once 'config/database.php';

// Hier bewaren we een eventuele foutmelding.
$foutmelding = '';

// Controleer of het formulier is verstuurd.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Haal het ingevulde e-mailadres en wachtwoord uit het formulier.
    $email = $_POST['email'];
    $wachtwoord = $_POST['wachtwoord'];

    // Zoek de gebruiker met het ingevulde e-mailadres.
    // Het vraagteken zorgt ervoor dat de invoer niet
    // direct in de SQL-query wordt geplaatst.
    $sql = "SELECT * FROM user WHERE email = ?";

    // Bereid de SQL-query veilig voor.
    $stmt = $pdo->prepare($sql);

    // Voer de query uit met het ingevulde e-mailadres.
    $stmt->execute([$email]);

    // Haal de gevonden gebruiker op uit de database.
    $gebruiker = $stmt->fetch();

    // Controleer of:
    // 1. De gebruiker bestaat.
    // 2. Het ingevulde wachtwoord klopt.
    if ($gebruiker && password_verify($wachtwoord, $gebruiker['password_hash'])) {

        // Maak na het inloggen een nieuw sessie-ID.
        // Dit maakt de sessie veiliger.
        session_regenerate_id(true);

        // Bewaar belangrijke gegevens van de gebruiker in de sessie.
        $_SESSION['user_id'] = $gebruiker['user_id'];
        $_SESSION['naam'] = $gebruiker['naam'];
        $_SESSION['rol'] = $gebruiker['rol'];
        $_SESSION['team_id'] = $gebruiker['team_id'];

        // Controleer welke rol de gebruiker heeft.
        if ($gebruiker['rol'] === 'trainer') {

            // Een trainer gaat naar het trainer-dashboard.
            header('Location: trainer/dashboard.php');
            exit;

        } else {

            // Een sporter gaat naar het sporter-dashboard.
            header('Location: sporter/dashboard.php');
            exit;
        }

    } else {

        // Deze melding verschijnt als het e-mailadres
        // of wachtwoord niet klopt.
        $foutmelding = 'E-mailadres of wachtwoord is onjuist.';
    }
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>TeamTrack - Inloggen</title>
</head>

<body>

    <h1>TeamTrack</h1>

    <h2>Inloggen</h2>

    <?php

    // Toon de foutmelding alleen als er een fout is.
    if ($foutmelding != '') {
        echo '<p>' . htmlspecialchars($foutmelding) . '</p>';
    }

    ?>

    <form method="POST">

        <label for="email">E-mailadres</label>
        <br>

        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <br><br>

        <label for="wachtwoord">Wachtwoord</label>
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