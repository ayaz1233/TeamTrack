<?php

// Start de sessie zodat we kunnen controleren
// welke gebruiker is ingelogd.
session_start();

// Controleer of er een gebruiker is ingelogd.
if (!isset($_SESSION['user_id'])) {

    // Niet ingelogd? Stuur terug naar de loginpagina.
    header('Location: ../login.php');
    exit;
}

// Controleer of de ingelogde gebruiker een sporter is.
if ($_SESSION['rol'] !== 'sporter') {

    // Een trainer mag het sporter-dashboard niet openen.
    die('Geen toegang tot deze pagina.');
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>TeamTrack - Sporter</title>
</head>

<body>

    <h1>TeamTrack</h1>

    <!-- Toon de naam van de ingelogde sporter. -->
    <h2>
        Welkom,
        <?php echo htmlspecialchars($_SESSION['naam']); ?>
    </h2>

    <p>Je bent ingelogd als sporter.</p>

</body>

</html>