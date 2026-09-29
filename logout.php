<?php

// Start de sessie.
// Dit is nodig om de gegevens van de ingelogde gebruiker te kunnen verwijderen.
session_start();

// Verwijder alle gegevens die in de sessie zijn opgeslagen.
session_unset();

// Stop en verwijder de huidige sessie.
session_destroy();

// Stuur de gebruiker terug naar de loginpagina.
header('Location: login.php');

// Stop het uitvoeren van de rest van de PHP-code.
exit;

?>