# TeamTrack

TeamTrack is een Training & Team Portal voor de commerciële sportacademie NextPlay.

Het project is gemaakt door:

Naam: Ayaz  
Studentnummer: 2209473  
Klas: 24A  

## Doel van TeamTrack

TeamTrack helpt trainers en sporters om trainingen, aanwezigheid, oefeningen, doelen en voortgang op één plek te beheren.

Er zijn twee rollen:

- Trainer
- Sporter

Een trainer kan onder andere sporters en trainingen beheren, oefeningen koppelen, doelen beheren en definitieve aanwezigheid registreren.

Een sporter kan zijn eigen trainingen bekijken, reageren op aanwezigheid vóór de deadline, zijn trainingskalender bekijken en zijn eigen doelen en voortgang bekijken.

## Gebruikte technieken

- PHP
- MySQL / MariaDB
- HTML
- CSS
- XAMPP
- phpMyAdmin
- Git en GitHub

## Installatie lokaal

1. Installeer en start XAMPP.
2. Start Apache en MySQL.
3. Plaats de map `teamtrack` in:

   C:\xampp\htdocs\

4. Open phpMyAdmin.
5. Maak een database aan met de naam:

   teamtrack

6. Importeer:

   database/teamtrack.sql

7. Controleer de databasegegevens in:

   config/database.php

8. Open één keer:

   http://localhost/teamtrack/create_test_users.php

   Hiermee worden de testaccounts aangemaakt.

9. Open daarna:

   http://localhost/teamtrack/login.php

## Testaccounts

Trainer:

E-mail: trainer@teamtrack.nl  
Wachtwoord: Trainer123!

Sporter:

E-mail: sporter@teamtrack.nl  
Wachtwoord: Sporter123!

De wachtwoorden worden niet als gewone tekst in de database opgeslagen. Hiervoor wordt PHP password hashing gebruikt.

## Belangrijkste functies

- Veilig inloggen
- Rollen voor trainer en sporter
- Controle op team en gebruiker
- Sporters beheren
- Team beheren
- Trainingen toevoegen, wijzigen en verwijderen
- Gewijzigde trainingen herkennen
- Trainingskalender
- Reactiedeadline voor aanwezigheid
- Aanwezig of afwezig doorgeven
- Definitieve aanwezigheid registreren
- Oefeningen beheren
- Oefeningen aan trainingen koppelen
- Persoonlijke doelen en voortgang
- Aanwezigheidspercentage
- Responsive weergave voor verschillende schermformaten
- Duidelijke meldingen en lege situaties

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

De database bevat onder andere de tabellen:

- team
- user
- training
- attendance
- exercise
- training_exercise
- goal

Voor de aanwezigheid is bij `training` ook `reactiedeadline` toegevoegd. Dit is nodig om te controleren tot wanneer een sporter zijn aanwezigheid mag doorgeven.

## GitHub

https://github.com/ayaz1233/TeamTrack

## Opdrachtgever

NextPlay  
Contactpersoon: Dhr. R. Bakker, Hoofdtrainer
