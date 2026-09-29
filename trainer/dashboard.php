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

// Controleer of de ingelogde gebruiker een trainer is.
if ($_SESSION['rol'] !== 'trainer') {

    // Een sporter mag het trainer-dashboard niet openen.
    die('Geen toegang tot deze pagina.');
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>TeamTrack - Trainer</title>
</head>

<body>

    <h1>TeamTrack</h1>

    <!-- Toon veilig de naam van de ingelogde trainer. -->
    <h2>
        Welkom,
        <?php echo htmlspecialchars($_SESSION['naam']); ?>
    </h2>

    <p>Je bent ingelogd als trainer.</p>

</body>

</html>