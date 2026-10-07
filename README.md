# TeamTrack

TeamTrack is een Training & Team Portal voor de commerciële sportacademie NextPlay.

Het project is gemaakt door:

Naam: Ayaz  
Studentnummer: 2209473  
Klas: 24A  

## Opdrachtgever

NextPlay  
Contactpersoon: Dhr. R. Bakker, Hoofdtrainer

## Doel van TeamTrack

TeamTrack helpt trainers en sporters om trainingen, aanwezigheid, oefeningen, doelen en voortgang op één plek te beheren.

Er zijn twee rollen:

- Trainer
- Sporter

Een trainer kan sporters en trainingen beheren, oefeningen beheren en koppelen, doelen beheren en definitieve aanwezigheid registreren.

Een sporter kan zijn eigen trainingen bekijken, reageren op aanwezigheid vóór de deadline, zijn trainingskalender bekijken en zijn eigen doelen en voortgang bekijken.

## Gebruikte technieken

- PHP
- MySQL / MariaDB
- HTML
- CSS
- XAMPP
- phpMyAdmin
- Git
- GitHub

## Installatie lokaal

1. Installeer en start XAMPP.

2. Start Apache en MySQL.

3. Plaats de map `teamtrack` in:

   C:\xampp\htdocs\

4. Open phpMyAdmin.

5. Maak een database aan met de naam:

   teamtrack

6. Importeer het bestand:

   database/teamtrack.sql

7. Controleer de databasegegevens in:

   config/database.php

8. Open één keer:

   http://localhost/teamtrack/create_test_users.php

   Hiermee worden de testaccounts aangemaakt.

9. Open daarna:

   http://localhost/teamtrack/

De gebruiker wordt automatisch doorgestuurd naar de inlogpagina.

## Testaccounts

### Trainer

E-mail: trainer@teamtrack.nl  
Wachtwoord: Trainer123!

### Sporter

E-mail: sporter@teamtrack.nl  
Wachtwoord: Sporter123!

De wachtwoorden worden niet als gewone tekst in de database opgeslagen. Hiervoor wordt PHP password hashing gebruikt.

## Belangrijkste functies

### Trainer

- Veilig inloggen
- Eigen team bekijken en beheren
- Sporters toevoegen, wijzigen en verwijderen
- Trainingen toevoegen, wijzigen en verwijderen
- Oefeningen beheren
- Oefeningen aan trainingen koppelen
- Definitieve aanwezigheid registreren
- Persoonlijke doelen en voortgang beheren

### Sporter

- Veilig inloggen
- Eigen teamdashboard bekijken
- Eigen trainingen bekijken
- Trainingen in een lijst bekijken
- Trainingskalender bekijken
- Aanwezig of afwezig doorgeven vóór de reactiedeadline
- Gewijzigde trainingen herkennen
- Eigen persoonlijke doelen bekijken
- Eigen voortgang bekijken
- Aanwezigheidspercentage bekijken

## Beveiliging

TeamTrack gebruikt onder andere:

- PHP sessions
- password_hash()
- password_verify()
- Prepared statements
- Server-side rolcontrole
- Teamcontrole
- Inputvalidatie

Een sporter krijgt alleen toegang tot informatie die bij zijn eigen account en team hoort.

## Database

De database bevat de volgende belangrijke tabellen:

- team
- user
- training
- attendance
- exercise
- training_exercise
- goal

Bij een training wordt ook een `reactiedeadline` opgeslagen. Hiermee controleert TeamTrack tot wanneer een sporter zijn aanwezigheid mag doorgeven.

## Responsive ontwerp

TeamTrack is gemaakt voor gebruik op verschillende schermformaten, zoals computer, tablet en telefoon.

De applicatie gebruikt duidelijke formulieren, knoppen, statussen en meldingen.

## Live versie

TeamTrack is online te bekijken via:

https://teamtrack.s2209473.jouw.website

## GitHub

https://github.com/ayaz1233/TeamTrack