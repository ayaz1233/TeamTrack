CREATE TABLE team (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL
);

CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NULL,
    naam VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('sporter', 'trainer') NOT NULL,
    CONSTRAINT fk_user_team
        FOREIGN KEY (team_id) REFERENCES team(team_id)
);

CREATE TABLE goal (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titel VARCHAR(255) NOT NULL,
    voortgang INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_goal_user
        FOREIGN KEY (user_id) REFERENCES user(user_id)
);

CREATE TABLE training (
    training_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    datum DATE NOT NULL,
    tijd TIME NOT NULL,
    gewijzigd BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_training_team
        FOREIGN KEY (team_id) REFERENCES team(team_id)
);

CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('aanwezig', 'afwezig') NOT NULL,
    is_definitief BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_attendance_training
        FOREIGN KEY (training_id) REFERENCES training(training_id),
    CONSTRAINT fk_attendance_user
        FOREIGN KEY (user_id) REFERENCES user(user_id),
    UNIQUE (training_id, user_id)
);

CREATE TABLE exercise (
    exercise_id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    omschrijving TEXT
);

CREATE TABLE training_exercise (
    training_id INT NOT NULL,
    exercise_id INT NOT NULL,
    PRIMARY KEY (training_id, exercise_id),
    CONSTRAINT fk_training_exercise_training
        FOREIGN KEY (training_id) REFERENCES training(training_id),
    CONSTRAINT fk_training_exercise_exercise
        FOREIGN KEY (exercise_id) REFERENCES exercise(exercise_id)
);