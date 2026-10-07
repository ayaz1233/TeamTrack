<?php

session_start();
require_once '../config/database.php';

// Alleen ingelogde gebruikers.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Alleen trainers.
if ($_SESSION['rol'] !== 'trainer') {
    die('Geen toegang tot deze pagina.');
}

$teamId = $_SESSION['team_id'];


// ----------------------------------------------------
// TEAM OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM team
        WHERE team_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$team = $stmt->fetch();


// ----------------------------------------------------
// SPORTERS OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM user
        WHERE team_id = ?
        AND rol = 'sporter'
        ORDER BY naam ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

$sporters = $stmt->fetchAll();


// ----------------------------------------------------
// TRAININGEN OPHALEN
// ----------------------------------------------------

$sql = "SELECT *
        FROM training
        WHERE team_id = ?
        ORDER BY datum ASC, tijd ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teamId]);

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

    <title>TeamTrack - Trainer Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

    <main class="container">


        <!-- ============================================= -->
        <!-- HEADER -->
        <!-- ============================================= -->

        <header class="app-header">

            <div>
                <h1>TeamTrack</h1>
                <p>Trainer Dashboard</p>
            </div>

            <a
                class="logout-link"
                href="../logout.php"
            >
                Uitloggen
            </a>

        </header>


        <!-- ============================================= -->
        <!-- WELKOM -->
        <!-- ============================================= -->

        <section class="welcome-section">

            <div>

                <p class="small-label">
                    OVERZICHT
                </p>

                <h2>
                    Welkom,
                    <?php
                    echo htmlspecialchars(
                        $_SESSION['naam']
                    );
                    ?>
                </h2>

                <p>
                    Beheer hier je team, sporters,
                    trainingen en voortgang.
                </p>

            </div>

            <div class="team-summary">

                <span>Mijn team</span>

                <strong>
                    <?php

                    if ($team) {
                        echo htmlspecialchars(
                            $team['naam']
                        );
                    } else {
                        echo 'Geen team';
                    }

                    ?>
                </strong>

            </div>

        </section>


        <!-- ============================================= -->
        <!-- SNEL BEHEER -->
        <!-- ============================================= -->

        <section>

            <div class="section-heading">

                <div>
                    <p class="small-label">
                        BEHEER
                    </p>

                    <h2>
                        Wat wil je beheren?
                    </h2>
                </div>

            </div>


            <div class="dashboard-grid">


                <a
                    class="dashboard-card"
                    href="team_bewerken.php"
                >
                    <span class="card-icon">T</span>

                    <strong>Team</strong>

                    <span>
                        Teamgegevens aanpassen
                    </span>
                </a>


                <a
                    class="dashboard-card"
                    href="sporter_toevoegen.php"
                >
                    <span class="card-icon">S</span>

                    <strong>Sporters</strong>

                    <span>
                        Nieuwe sporter toevoegen
                    </span>
                </a>


                <a
                    class="dashboard-card"
                    href="training_toevoegen.php"
                >
                    <span class="card-icon">+</span>

                    <strong>Training</strong>

                    <span>
                        Nieuwe training plannen
                    </span>
                </a>


                <a
                    class="dashboard-card"
                    href="oefeningen.php"
                >
                    <span class="card-icon">O</span>

                    <strong>Oefeningen</strong>

                    <span>
                        Oefeningen beheren
                    </span>
                </a>


                <a
                    class="dashboard-card"
                    href="oefening_koppelen.php"
                >
                    <span class="card-icon">K</span>

                    <strong>Koppelen</strong>

                    <span>
                        Oefening aan training koppelen
                    </span>
                </a>


                <a
                    class="dashboard-card"
                    href="aanwezigheid.php"
                >
                    <span class="card-icon">A</span>

                    <strong>Aanwezigheid</strong>

                    <span>
                        Aanwezigheid registreren
                    </span>
                </a>


                <a
                    class="dashboard-card"
                    href="doelen.php"
                >
                    <span class="card-icon">D</span>

                    <strong>Doelen</strong>

                    <span>
                        Voortgang en doelen beheren
                    </span>
                </a>


            </div>

        </section>


        <!-- ============================================= -->
        <!-- TEAMOVERZICHT -->
        <!-- ============================================= -->

        <section>

            <div class="section-heading">

                <div>
                    <p class="small-label">
                        TEAM
                    </p>

                    <h2>Mijn team</h2>
                </div>

                <a
                    class="action-link"
                    href="team_bewerken.php"
                >
                    Team bewerken
                </a>

            </div>


            <?php if ($team) { ?>

                <div class="info-card">

                    <div>

                        <span class="info-label">
                            Teamnaam
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $team['naam']
                            );
                            ?>
                        </strong>

                    </div>


                    <div>

                        <span class="info-label">
                            Aantal sporters
                        </span>

                        <strong>
                            <?php
                            echo count($sporters);
                            ?>
                        </strong>

                    </div>


                    <div>

                        <span class="info-label">
                            Trainingen
                        </span>

                        <strong>
                            <?php
                            echo count($trainingen);
                            ?>
                        </strong>

                    </div>

                </div>

            <?php } else { ?>

                <div class="empty-message">
                    Geen team gevonden.
                </div>

            <?php } ?>

        </section>


        <!-- ============================================= -->
        <!-- SPORTERS -->
        <!-- ============================================= -->

        <section>

            <div class="section-heading">

                <div>

                    <p class="small-label">
                        LEDEN
                    </p>

                    <h2>
                        Sporters
                        <span class="count-badge">
                            <?php
                            echo count($sporters);
                            ?>
                        </span>
                    </h2>

                </div>

                <a
                    class="action-link"
                    href="sporter_toevoegen.php"
                >
                    + Sporter toevoegen
                </a>

            </div>


            <?php if (count($sporters) > 0) { ?>

                <div class="list-grid">

                    <?php foreach ($sporters as $sporter) { ?>

                        <article class="item-card">

                            <div class="item-card-header">

                                <div class="avatar">
                                    <?php
                                    echo strtoupper(
                                        substr(
                                            $sporter['naam'],
                                            0,
                                            1
                                        )
                                    );
                                    ?>
                                </div>

                                <div>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $sporter['naam']
                                        );
                                        ?>
                                    </strong>

                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $sporter['email']
                                        );
                                        ?>
                                    </span>

                                </div>

                            </div>


                            <div class="card-actions">

                                <a
                                    href="sporter_bewerken.php?id=<?php
                                    echo $sporter['user_id'];
                                    ?>"
                                >
                                    Bewerken
                                </a>


                                <form
                                    method="POST"
                                    action="sporter_verwijderen.php"
                                    onsubmit="return confirm('Weet je zeker dat je deze sporter wilt verwijderen?');"
                                >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?php
                                        echo $sporter['user_id'];
                                        ?>"
                                    >

                                    <button
                                        class="delete-button"
                                        type="submit"
                                    >
                                        Verwijderen
                                    </button>

                                </form>

                            </div>

                        </article>

                    <?php } ?>

                </div>

            <?php } else { ?>

                <div class="empty-message">

                    Er zijn nog geen sporters
                    in dit team.

                </div>

            <?php } ?>

        </section>


        <!-- ============================================= -->
        <!-- TRAININGEN -->
        <!-- ============================================= -->

        <section>

            <div class="section-heading">

                <div>

                    <p class="small-label">
                        PLANNING
                    </p>

                    <h2>
                        Trainingen
                        <span class="count-badge">
                            <?php
                            echo count($trainingen);
                            ?>
                        </span>
                    </h2>

                </div>

                <a
                    class="action-link"
                    href="training_toevoegen.php"
                >
                    + Training toevoegen
                </a>

            </div>


            <?php if (count($trainingen) > 0) { ?>

                <div class="list-grid">

                    <?php foreach ($trainingen as $training) { ?>

                        <article class="item-card">

                            <div class="training-top">

                                <div>

                                    <span class="info-label">
                                        Datum
                                    </span>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $training['datum']
                                        );
                                        ?>
                                    </strong>

                                </div>


                                <?php
                                if ($training['gewijzigd'] == 1) {
                                ?>

                                    <span class="status-badge changed">
                                        Gewijzigd
                                    </span>

                                <?php } else { ?>

                                    <span class="status-badge">
                                        Gepland
                                    </span>

                                <?php } ?>

                            </div>


                            <div class="training-details">

                                <p>
                                    <strong>Tijd:</strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $training['tijd']
                                    );
                                    ?>
                                </p>


                                <p>
                                    <strong>Reactiedeadline:</strong>

                                    <?php

                                    if (
                                        $training['reactiedeadline']
                                        !== null
                                    ) {

                                        echo htmlspecialchars(
                                            $training[
                                                'reactiedeadline'
                                            ]
                                        );

                                    } else {

                                        echo 'Geen deadline';
                                    }

                                    ?>
                                </p>

                            </div>


                            <!-- ===================================== -->
                            <!-- GEKOPPELDE OEFENINGEN -->
                            <!-- ===================================== -->

                            <div class="training-details">

                                <p>
                                    <strong>Oefeningen:</strong>
                                </p>

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
                                        Nog geen oefeningen gekoppeld.
                                    </p>

                                <?php } ?>

                            </div>


                            <div class="card-actions">

                                <a
                                    href="training_bewerken.php?id=<?php
                                    echo $training['training_id'];
                                    ?>"
                                >
                                    Bewerken
                                </a>


                                <form
                                    method="POST"
                                    action="training_verwijderen.php"
                                    onsubmit="return confirm('Weet je zeker dat je deze training wilt verwijderen?');"
                                >

                                    <input
                                        type="hidden"
                                        name="training_id"
                                        value="<?php
                                        echo $training['training_id'];
                                        ?>"
                                    >

                                    <button
                                        class="delete-button"
                                        type="submit"
                                    >
                                        Verwijderen
                                    </button>

                                </form>

                            </div>

                        </article>

                    <?php } ?>

                </div>

            <?php } else { ?>

                <div class="empty-message">

                    Er zijn nog geen trainingen gepland.

                </div>

            <?php } ?>

        </section>


        <!-- ============================================= -->
        <!-- FOOTER -->
        <!-- ============================================= -->

        <footer class="dashboard-footer">

            <strong>TeamTrack</strong>

            <span>
                Training & Team Portal
            </span>

        </footer>


    </main>

</body>

</html>