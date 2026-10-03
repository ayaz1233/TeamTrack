<?php

// Tijdzone voor TeamTrack.
date_default_timezone_set('Europe/Amsterdam');

// Lokale databasegegevens voor XAMPP.
$host = 'localhost';
$dbname = 'teamtrack';
$username = 'root';
$password = '';

try {
    // Verbinding maken met de lokale TeamTrack database.
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die('Databaseverbinding mislukt.');
}