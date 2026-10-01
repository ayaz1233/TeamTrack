-- TeamTrack database
-- Database voor het Training & Team Portal van NextPlay.


-- =====================================================
-- TEAM
-- Hier worden de teams opgeslagen.
-- =====================================================

CREATE TABLE team (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL
);


-- =====================================================
-- USER
-- Hier worden trainers en sporters opgeslagen.
-- team_id geeft aan bij welk team de gebruiker hoort.
-- =====================================================

CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NULL,
    naam VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('sporter', 'trainer') NOT NULL,

    CONSTRAINT fk_user_team
        FOREIGN KEY (team_id)
        REFERENCES team(team_id)
);


-- =====================================================
-- GOAL
-- Persoonlijke doelen van een sporter.
-- =====================================================

CREATE TABLE goal (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titel VARCHAR(255) NOT NULL,
    voortgang INT NOT NULL DEFAULT 0,

    CONSTRAINT fk_goal_user
        FOREIGN KEY (user_id)
        REFERENCES user(user_id)
);


-- =====================================================
-- TRAINING
-- Trainingen worden gekoppeld aan een team.
-- reactiedeadline bepaalt tot wanneer een sporter
-- aanwezig of afwezig kan doorgeven.
-- =====================================================

CREATE TABLE training (
    training_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    datum DATE NOT NULL,
    tijd TIME NOT NULL,
    gewijzigd BOOLEAN NOT NULL DEFAULT FALSE,
    reactiedeadline DATETIME NULL,

    CONSTRAINT fk_training_team
        FOREIGN KEY (team_id)
        REFERENCES team(team_id)
);


-- =====================================================
-- ATTENDANCE
-- Hier wordt de aanwezigheid van sporters opgeslagen.
-- =====================================================

CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('aanwezig', 'afwezig') NOT NULL,
    is_definitief BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_attendance_training
        FOREIGN KEY (training_id)
        REFERENCES training(training_id),

    CONSTRAINT fk_attendance_user
        FOREIGN KEY (user_id)
        REFERENCES user(user_id),

    -- Een sporter kan per training maar één
    -- aanwezigheidsrecord hebben.
    UNIQUE (training_id, user_id)
);


-- =====================================================
-- EXERCISE
-- Oefeningen die een trainer kan gebruiken.
-- =====================================================

CREATE TABLE exercise (
    exercise_id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    omschrijving TEXT
);


-- =====================================================
-- TRAINING_EXERCISE
-- Koppelt oefeningen aan trainingen.
-- =====================================================

CREATE TABLE training_exercise (
    training_id INT NOT NULL,
    exercise_id INT NOT NULL,

    PRIMARY KEY (training_id, exercise_id),

    CONSTRAINT fk_training_exercise_training
        FOREIGN KEY (training_id)
        REFERENCES training(training_id),

    CONSTRAINT fk_training_exercise_exercise
        FOREIGN KEY (exercise_id)
        REFERENCES exercise(exercise_id)
);