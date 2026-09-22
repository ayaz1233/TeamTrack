<?php

// Gegevens die nodig zijn om verbinding te maken met de database.
$host = 'localhost';
$dbname = 'teamtrack';
$username = 'root';
$password = '';

try {
    // Maak verbinding met de TeamTrack database.
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Laat PHP een foutmelding maken als er iets fout gaat met de database.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Deze melding verschijnt als de verbinding niet lukt.
    die('Databaseverbinding mislukt.');
}