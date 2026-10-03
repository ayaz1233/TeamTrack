<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen sporters mogen deze pagina bekijken.
if ($_SESSION['rol'] !== 'sporter') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];


// ----------------------------------------------------
// TRAININGEN VAN HET EIGEN TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM training
        WHERE team_id = ?
        ORDER BY datum ASC, tijd ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$trainingen = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TeamTrack - Trainingskalender</title>

    <link
        rel="stylesheet"
        href="../css/style.css?v=5"
    >

    <style>

        /*
         * Opmaak van de trainingskalender.
         * Elke training krijgt een eigen kaart.
         */

        .calendar-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }

        .calendar-header {
            margin-bottom: 30px;
        }

        .calendar-header h1 {
            margin-bottom: 8px;
        }

        .calendar-header p {
            color: #475569;
            margin-bottom: 0;
        }


        /* Trainingen */

        .calendar-list {
            display: grid;
            gap: 16px;
        }

        .calendar-training {
            background: white;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 22px;
        }

        .calendar-training-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 1px solid #E2E8F0;
        }

        .calendar-training-top strong {
            color: #052E16;
            font-size: 19px;
        }


        /* Status */

        .calendar-status {
            display: inline-block;
            background: #DCFCE7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .calendar-status.changed {
            background: #FEF3C7;
            color: #92400E;
        }


        /* Details */

        .calendar-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .calendar-details p {
            margin: 0;
            color: #0F172A;
        }

        .calendar-details strong {
            color: #052E16;
        }


        /* Waarschuwing */

        .calendar-warning {
            background: #FFF7ED;
            border-left: 4px solid #F59E0B;
            color: #92400E;
            padding: 11px 14px;
            border-radius: 6px;
            margin-top: 16px;
        }

        .calendar-warning p {
            margin: 0;
        }


        /* Terugknop */

        .calendar-back {
            display: inline-block;
            margin-top: 25px;
            background: #052E16;
            color: white;
            padding: 10px 16px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }

        .calendar-back:hover {
            background: #22C55E;
            color: #052E16;
        }


        /* Geen trainingen */

        .calendar-empty {
            background: white;
            border: 1px dashed #CBD5E1;
            border-radius: 10px;
            padding: 25px;
            color: #64748B;
        }


        /* Mobiel */

        @media (max-width: 600px) {

            .calendar-training {
                padding: 18px;
            }

            .calendar-training-top {
                align-items: flex-start;
            }
        }

    </style>

</head>

<body>

<div class="calendar-container">


    <!-- PAGINATITEL -->

    <div class="calendar-header">

        <h1>Trainingskalender</h1>

        <p>
            Hier zie je de geplande trainingen van jouw team.
        </p>

    </div>


    <!-- TRAININGEN -->

    <?php if (count($trainingen) > 0) { ?>

        <div class="calendar-list">

            <?php foreach ($trainingen as $training) { ?>

                <div class="calendar-training">


                    <!-- BOVENKANT -->

                    <div class="calendar-training-top">

                        <strong>

                            <?php
                            echo date(
                                'd-m-Y',
                                strtotime($training['datum'])
                            );
                            ?>

                        </strong>


                        <?php if ($training['gewijzigd'] == 1) { ?>

                            <span class="calendar-status changed">
                                Gewijzigd
                            </span>

                        <?php } else { ?>

                            <span class="calendar-status">
                                Gepland
                            </span>

                        <?php } ?>

                    </div>


                    <!-- DETAILS -->

                    <div class="calendar-details">

                        <p>
                            <strong>Datum:</strong>

                            <?php
                            echo date(
                                'd-m-Y',
                                strtotime($training['datum'])
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Tijd:</strong>

                            <?php
                            echo date(
                                'H:i',
                                strtotime($training['tijd'])
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Status:</strong>

                            <?php

                            if ($training['gewijzigd'] == 1) {
                                echo 'Gewijzigd';
                            } else {
                                echo 'Gepland';
                            }

                            ?>
                        </p>


                        <p>
                            <strong>Reactiedeadline:</strong>

                            <?php

                            if ($training['reactiedeadline'] !== null) {

                                echo date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $training['reactiedeadline']
                                    )
                                );

                            } else {

                                echo 'Geen deadline ingesteld';
                            }

                            ?>
                        </p>

                    </div>


                    <!-- WAARSCHUWING BIJ GEWIJZIGDE TRAINING -->

                    <?php if ($training['gewijzigd'] == 1) { ?>

                        <div class="calendar-warning">

                            <p>
                                <strong>Let op:</strong>
                                de datum of tijd van deze training
                                is gewijzigd.
                            </p>

                        </div>

                    <?php } ?>


                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="calendar-empty">

            Er zijn op dit moment geen trainingen gepland.

        </div>

    <?php } ?>


    <!-- TERUG -->

    <a
        href="dashboard.php"
        class="calendar-back"
    >
        ← Terug naar dashboard
    </a>


</div>

</body>

</html>