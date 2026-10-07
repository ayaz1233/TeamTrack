<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen sporters.
if ($_SESSION['rol'] !== 'sporter') {
    die('Geen toegang tot deze pagina.');
}

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['team_id'];

$melding = '';
$foutmelding = '';


// ----------------------------------------------------
// AANWEZIGHEID OPSLAAN
// ----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $trainingId = $_POST['training_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (
        $status !== 'aanwezig'
        && $status !== 'afwezig'
    ) {

        $foutmelding = 'Ongeldige keuze.';

    } else {

        // Controleer of training bij eigen team hoort.
        $sql = "SELECT *
                FROM training
                WHERE training_id = ?
                AND team_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $trainingId,
            $teamId
        ]);

        $training = $stmt->fetch();

        if (!$training) {

            $foutmelding = 'Geen toegang tot deze training.';

        } else {

            // Bestaande aanwezigheid ophalen.
            $sql = "SELECT *
                    FROM attendance
                    WHERE training_id = ?
                    AND user_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $trainingId,
                $userId
            ]);

            $attendance = $stmt->fetch();

            // Definitieve aanwezigheid mag niet gewijzigd worden.
            if (
                $attendance
                && $attendance['is_definitief'] == 1
            ) {

                $foutmelding =
                    'Deze aanwezigheid is definitief en kan niet meer worden gewijzigd.';

            } else {

                $nu = date('Y-m-d H:i:s');

                // Controleer reactiedeadline.
                if (
                    $training['reactiedeadline'] != null
                    && $nu > $training['reactiedeadline']
                ) {

                    $foutmelding =
                        'De reactiedeadline voor deze training is voorbij.';

                } else {

                    if ($attendance) {

                        // Bestaande keuze aanpassen.
                        $sql = "UPDATE attendance
                                SET status = ?
                                WHERE training_id = ?
                                AND user_id = ?";

                        $stmt = $pdo->prepare($sql);

                        $stmt->execute([
                            $status,
                            $trainingId,
                            $userId
                        ]);

                    } else {

                        // Nieuwe keuze opslaan.
                        $sql = "INSERT INTO attendance
                                (
                                    training_id,
                                    user_id,
                                    status,
                                    is_definitief
                                )
                                VALUES (?, ?, ?, 0)";

                        $stmt = $pdo->prepare($sql);

                        $stmt->execute([
                            $trainingId,
                            $userId,
                            $status
                        ]);
                    }

                    $melding = 'Je aanwezigheid is opgeslagen.';
                }
            }
        }
    }
}


// ----------------------------------------------------
// PERSOONLIJKE DOELEN
// FE-08
// ----------------------------------------------------

$sql = "SELECT *
        FROM goal
        WHERE user_id = ?
        ORDER BY goal_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$doelen = $stmt->fetchAll();


// ----------------------------------------------------
// AANWEZIGHEIDSPERCENTAGE
// FE-09
// ----------------------------------------------------

$sql = "SELECT
            COUNT(*) AS totaal,
            SUM(
                CASE
                    WHEN status = 'aanwezig'
                    THEN 1
                    ELSE 0
                END
            ) AS aanwezig
        FROM attendance
        WHERE user_id = ?
        AND is_definitief = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$aanwezigheid = $stmt->fetch();

$totaalDefinitief = (int) $aanwezigheid['totaal'];
$aantalAanwezig = (int) $aanwezigheid['aanwezig'];

if ($totaalDefinitief > 0) {

    $aanwezigheidspercentage =
        round(
            ($aantalAanwezig / $totaalDefinitief) * 100
        );

} else {

    $aanwezigheidspercentage = 0;
}


// ----------------------------------------------------
// TRAININGEN VAN EIGEN TEAM
// ----------------------------------------------------

$sql = "SELECT
            training.*,
            attendance.status,
            attendance.is_definitief
        FROM training
        LEFT JOIN attendance
        ON training.training_id = attendance.training_id
        AND attendance.user_id = ?
        WHERE training.team_id = ?
        ORDER BY training.datum ASC,
                 training.tijd ASC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $userId,
    $teamId
]);

$trainingen = $stmt->fetchAll();


// ----------------------------------------------------
// GEKOPPELDE OEFENINGEN PER TRAINING
// FE-06
// ----------------------------------------------------

foreach ($trainingen as &$training) {

    $sql = "SELECT
                exercise.naam,
                exercise.omschrijving
            FROM training_exercise
            INNER JOIN exercise
            ON training_exercise.exercise_id = exercise.exercise_id
            WHERE training_exercise.training_id = ?
            ORDER BY exercise.naam ASC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $training['training_id']
    ]);

    $training['oefeningen'] = $stmt->fetchAll();
}

unset($training);

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TeamTrack - Sporter Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/style.css?v=4"
    >

    <style>

        /*
         * Extra styling voor het sporter-dashboard.
         */

        .sporter-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 24px;
            min-height: 190px;
        }

        .stat-label {
            display: block;
            color: #16A34A;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-number {
            display: block;
            color: #052E16;
            font-size: 36px;
            line-height: 1.1;
            margin-bottom: 14px;
        }

        .stat-card progress {
            width: 100%;
            max-width: none;
            margin-bottom: 12px;
        }

        .stat-card p {
            color: #475569;
            font-weight: normal;
            margin-bottom: 0;
        }


        /* Kalenderkaart */

        .stat-link {
            display: flex;
            flex-direction: column;
            text-decoration: none !important;
            color: #0F172A !important;
        }

        .stat-link strong {
            display: block;
            color: #052E16;
            font-size: 22px;
            margin-bottom: 8px;
            text-decoration: none;
        }

        .stat-link p {
            text-decoration: none;
            margin-bottom: 18px;
        }

        .card-arrow {
            display: inline-block;
            width: fit-content;
            margin-top: auto;
            background: #052E16;
            color: white;
            padding: 10px 15px;
            border-radius: 7px;
            font-weight: bold;
            text-decoration: none;
        }

        .stat-link:hover {
            border-color: #22C55E;
        }

        .stat-link:hover .card-arrow {
            background: #22C55E;
            color: #052E16;
        }


        /* Persoonlijke doelen */

        .goal-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .goal-header strong {
            display: block;
            color: #052E16;
            font-size: 17px;
        }

        .goal-progress {
            margin-top: 20px;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 8px;
        }

        .goal-progress progress {
            width: 100%;
            max-width: none;
        }


        /* Trainingen */

        .training-card {
            padding: 22px;
        }

        .training-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .training-details p {
            display: block;
            margin: 0;
            font-weight: normal;
        }


        /* Oefeningen */

        .exercise-box {
            margin-top: 15px;
            padding: 14px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
        }

        .exercise-box > strong {
            display: block;
            color: #052E16;
            margin-bottom: 8px;
        }

        .exercise-box p {
            margin: 5px 0;
            color: #475569;
        }

        .exercise-box p strong {
            color: #0F172A;
        }


        /* Statusmeldingen */

        .training-status-message {
            margin-top: 15px;
            padding: 14px;
            background: #ECFDF5;
            border-radius: 8px;
        }

        .training-status-message strong {
            color: #052E16;
        }

        .training-status-message p {
            margin: 5px 0 0;
            font-weight: normal;
        }

        .warning-message {
            background: #FEF3C7;
        }

        .warning-message strong {
            color: #92400E;
        }


        /* Aanwezigheidsknoppen */

        .attendance-actions {
            display: flex;
            gap: 10px;
            margin: 18px 0 0;
        }

        .attendance-present {
            background: #22C55E;
            color: #052E16;
        }

        .attendance-absent {
            background: #E2E8F0;
            color: #0F172A;
        }


        /* Mobiel */

        @media (max-width: 750px) {

            .sporter-stats-grid {
                grid-template-columns: 1fr;
            }

            .attendance-actions {
                flex-direction: column;
            }

            .attendance-actions button {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<main class="container">


    <!-- HEADER -->

    <header class="app-header">

        <div>

            <h1>TeamTrack</h1>

            <p>Sporter Dashboard</p>

        </div>

        <a
            class="logout-link"
            href="../logout.php"
        >
            Uitloggen
        </a>

    </header>


    <!-- WELKOM -->

    <section class="welcome-section">

        <div>

            <p class="small-label">
                MIJN OVERZICHT
            </p>

            <h2>
                Welkom,
                <?php
                echo htmlspecialchars($_SESSION['naam']);
                ?>
            </h2>

            <p>
                Bekijk je voortgang, doelen en trainingen.
            </p>

        </div>

        <div class="team-summary">

            <span>Rol</span>

            <strong>Sporter</strong>

        </div>

    </section>


    <!-- MELDINGEN -->

    <?php if ($melding !== '') { ?>

        <div class="success-message">

            <?php
            echo htmlspecialchars($melding);
            ?>

        </div>

    <?php } ?>


    <?php if ($foutmelding !== '') { ?>

        <div class="error-message">

            <?php
            echo htmlspecialchars($foutmelding);
            ?>

        </div>

    <?php } ?>


    <!-- VOORTGANG -->

    <section>

        <div class="section-heading">

            <div>

                <p class="small-label">
                    VOORTGANG
                </p>

                <h2>Mijn voortgang</h2>

            </div>

        </div>


        <div class="sporter-stats-grid">


            <!-- AANWEZIGHEID -->

            <div class="stat-card">

                <span class="stat-label">
                    Aanwezigheidspercentage
                </span>

                <strong class="stat-number">

                    <?php
                    echo $aanwezigheidspercentage;
                    ?>%

                </strong>

                <progress
                    value="<?php echo $aanwezigheidspercentage; ?>"
                    max="100"
                >
                </progress>


                <?php if ($totaalDefinitief == 0) { ?>

                    <p>
                        Er zijn nog geen definitieve
                        aanwezigheden geregistreerd.
                    </p>

                <?php } else { ?>

                    <p>
                        Aanwezig bij
                        <?php echo $aantalAanwezig; ?>
                        van de
                        <?php echo $totaalDefinitief; ?>
                        definitieve trainingen.
                    </p>

                <?php } ?>

            </div>


            <!-- TRAININGSKALENDER -->

            <a
                href="kalender.php"
                class="stat-card stat-link"
            >

                <span class="stat-label">
                    PLANNING
                </span>

                <strong>
                    Trainingskalender
                </strong>

                <p>
                    Bekijk alle geplande trainingen
                    van jouw team.
                </p>

                <span class="card-arrow">
                    Bekijk kalender →
                </span>

            </a>

        </div>

    </section>


    <!-- DOELEN -->

    <section>

        <div class="section-heading">

            <div>

                <p class="small-label">
                    PERSOONLIJKE ONTWIKKELING
                </p>

                <h2>Mijn doelen</h2>

            </div>

            <?php if (count($doelen) > 0) { ?>

                <span class="count-badge">

                    <?php echo count($doelen); ?>

                    <?php
                    echo count($doelen) == 1
                        ? 'doel'
                        : 'doelen';
                    ?>

                </span>

            <?php } ?>

        </div>


        <?php if (count($doelen) > 0) { ?>

            <div class="list-grid">

                <?php foreach ($doelen as $doel) { ?>

                    <div class="item-card">

                        <div class="goal-header">

                            <div class="card-icon">
                                D
                            </div>

                            <div>

                                <span class="info-label">
                                    Persoonlijk doel
                                </span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $doel['titel']
                                    );
                                    ?>

                                </strong>

                            </div>

                        </div>


                        <div class="goal-progress">

                            <div class="progress-text">

                                <span>Voortgang</span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $doel['voortgang']
                                    );
                                    ?>%

                                </strong>

                            </div>

                            <progress
                                value="<?php
                                echo htmlspecialchars(
                                    $doel['voortgang']
                                );
                                ?>"
                                max="100"
                            >
                            </progress>

                        </div>

                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-message">

                <strong>
                    Nog geen persoonlijke doelen
                </strong>

                <p>
                    Zodra je trainer een doel toevoegt,
                    verschijnt het hier.
                </p>

            </div>

        <?php } ?>

    </section>


    <!-- TRAININGEN -->

    <section>

        <div class="section-heading">

            <div>

                <p class="small-label">
                    PLANNING
                </p>

                <h2>Mijn trainingen</h2>

            </div>

            <a
                href="kalender.php"
                class="action-link"
            >
                Trainingskalender
            </a>

        </div>


        <?php if (count($trainingen) > 0) { ?>

            <div class="list-grid">

                <?php foreach ($trainingen as $training) { ?>

                    <?php

                    $deadlineVoorbij = false;

                    if (
                        $training['reactiedeadline'] != null
                        && date('Y-m-d H:i:s')
                            > $training['reactiedeadline']
                    ) {

                        $deadlineVoorbij = true;
                    }

                    ?>

                    <div class="item-card training-card">


                        <!-- BOVENKANT -->

                        <div class="training-top">

                            <div>

                                <span class="info-label">
                                    Training
                                </span>

                                <strong>

                                    <?php
                                    echo date(
                                        'd-m-Y',
                                        strtotime(
                                            $training['datum']
                                        )
                                    );
                                    ?>

                                </strong>

                            </div>


                            <?php if ($training['gewijzigd'] == 1) { ?>

                                <span class="status-badge changed">
                                    Gewijzigd
                                </span>

                            <?php } else { ?>

                                <span class="status-badge">
                                    Gepland
                                </span>

                            <?php } ?>

                        </div>


                        <!-- DETAILS -->

                        <div class="training-details">

                            <p>

                                <strong>Tijd:</strong>

                                <?php
                                echo date(
                                    'H:i',
                                    strtotime(
                                        $training['tijd']
                                    )
                                );
                                ?>

                            </p>


                            <p>

                                <strong>Reageren voor:</strong>

                                <?php

                                if (
                                    $training['reactiedeadline']
                                    != null
                                ) {

                                    echo date(
                                        'd-m-Y H:i',
                                        strtotime(
                                            $training[
                                                'reactiedeadline'
                                            ]
                                        )
                                    );

                                } else {

                                    echo 'Geen deadline';
                                }

                                ?>

                            </p>


                            <p>

                                <strong>Mijn keuze:</strong>

                                <?php

                                if ($training['status'] != null) {

                                    echo htmlspecialchars(
                                        ucfirst(
                                            $training['status']
                                        )
                                    );

                                } else {

                                    echo 'Nog niet ingevuld';
                                }

                                ?>

                            </p>

                        </div>


                        <!-- GEKOPPELDE OEFENINGEN -->

                        <div class="exercise-box">

                            <strong>
                                Oefeningen
                            </strong>

                            <?php
                            if (
                                count(
                                    $training['oefeningen']
                                ) > 0
                            ) {
                            ?>

                                <?php
                                foreach (
                                    $training['oefeningen']
                                    as $oefening
                                ) {
                                ?>

                                    <p>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $oefening['naam']
                                            );
                                            ?>
                                        </strong>

                                        <?php
                                        if (
                                            !empty(
                                                $oefening[
                                                    'omschrijving'
                                                ]
                                            )
                                        ) {
                                        ?>

                                            -
                                            <?php
                                            echo htmlspecialchars(
                                                $oefening[
                                                    'omschrijving'
                                                ]
                                            );
                                            ?>

                                        <?php } ?>

                                    </p>

                                <?php } ?>

                            <?php } else { ?>

                                <p>
                                    Voor deze training zijn nog
                                    geen oefeningen gekoppeld.
                                </p>

                            <?php } ?>

                        </div>


                        <!-- GEWIJZIGDE TRAINING -->

                        <?php if ($training['gewijzigd'] == 1) { ?>

                            <div
                                class="training-status-message warning-message"
                            >

                                <strong>
                                    Let op
                                </strong>

                                <p>
                                    De datum of tijd van deze
                                    training is gewijzigd.
                                </p>

                            </div>

                        <?php } ?>


                        <!-- DEFINITIEVE STATUS -->

                        <?php if ($training['is_definitief'] == 1) { ?>

                            <div class="training-status-message">

                                <strong>
                                    Definitieve aanwezigheid
                                </strong>

                                <p>

                                    Jouw status is:

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            ucfirst(
                                                $training['status']
                                            )
                                        );
                                        ?>

                                    </strong>

                                </p>

                            </div>


                        <?php } elseif ($deadlineVoorbij) { ?>


                            <div
                                class="training-status-message warning-message"
                            >

                                <strong>
                                    Reactietijd voorbij
                                </strong>

                                <p>
                                    Je kunt je keuze niet meer
                                    aanpassen.
                                </p>

                            </div>


                        <?php } else { ?>


                            <!-- AANWEZIG / AFWEZIG -->

                            <form
                                method="POST"
                                class="attendance-actions"
                            >

                                <input
                                    type="hidden"
                                    name="training_id"
                                    value="<?php
                                    echo $training['training_id'];
                                    ?>"
                                >

                                <button
                                    type="submit"
                                    name="status"
                                    value="aanwezig"
                                    class="attendance-present"
                                >
                                    Aanwezig
                                </button>

                                <button
                                    type="submit"
                                    name="status"
                                    value="afwezig"
                                    class="attendance-absent"
                                >
                                    Afwezig
                                </button>

                            </form>

                        <?php } ?>


                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-message">

                <strong>
                    Geen trainingen gepland
                </strong>

                <p>
                    Er staan op dit moment geen
                    trainingen voor jouw team gepland.
                </p>

            </div>

        <?php } ?>

    </section>


    <!-- FOOTER -->

    <footer class="dashboard-footer">

        <strong>TeamTrack</strong>

        <span>
            Training & Team Portal
        </span>

    </footer>


</main>

</body>

</html>