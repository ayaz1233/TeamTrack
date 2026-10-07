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

De applicatie heeft twee rollen:

- Trainer
- Sporter

Een trainer kan zijn team en sporters beheren, trainingen plannen en aanpassen, oefeningen beheren en koppelen, doelen beheren en definitieve aanwezigheid registreren.

Een sporter kan zijn eigen trainingen bekijken, de trainingskalender gebruiken, voor de reactiedeadline aangeven of hij aanwezig of afwezig is en zijn eigen doelen en voortgang bekijken.


## Gebruikte technieken

Voor TeamTrack zijn de volgende technieken en programma's gebruikt:

- PHP
- MySQL / MariaDB
- HTML
- CSS
- XAMPP
- phpMyAdmin
- Git
- GitHub


## Projectstructuur

De applicatie is verdeeld in duidelijke mappen en bestanden.

`config/`  
Bevat de databaseverbinding.

`database/`  
Bevat het SQL-bestand waarmee de database kan worden aangemaakt.

`css/`  
Bevat de styling van TeamTrack.

`trainer/`  
Bevat de functies en pagina's voor trainers.

`sporter/`  
Bevat de functies en pagina's voor sporters.

`index.php`  
Stuurt de gebruiker door naar de loginpagina.

`login.php`  
Verwerkt het inloggen.

`logout.php`  
Logt de gebruiker uit.

`create_test_users.php`  
Maakt de testaccounts aan.


## Installatie lokaal

1. Installeer XAMPP.
2. Start Apache en MySQL in XAMPP.
3. Plaats de map `teamtrack` in:

   `C:\xampp\htdocs\`

4. Open phpMyAdmin.
5. Maak een database met de naam:

   `teamtrack`

6. Importeer:

   `database/teamtrack.sql`

7. Controleer de database-instellingen in:

   `config/database.php`

8. Open één keer:

   `http://localhost/teamtrack/create_test_users.php`

   Hiermee worden de testaccounts aangemaakt.

9. Open daarna:

   `http://localhost/teamtrack/`

De gebruiker wordt automatisch doorgestuurd naar de loginpagina.


## Testaccounts

### Trainer

E-mail:

`trainer@teamtrack.nl`

Wachtwoord:

`Trainer123!`

### Sporter

E-mail:

`sporter@teamtrack.nl`

Wachtwoord:

`Sporter123!`

De wachtwoorden worden niet als gewone tekst in de database opgeslagen. Hiervoor wordt PHP password hashing gebruikt.


## Functies trainer

De trainer kan:

- Veilig inloggen en uitloggen.
- Alleen trainerfuncties openen.
- Het eigen team bekijken en aanpassen.
- Sporters van het eigen team bekijken.
- Nieuwe sporters toevoegen.
- Sporters bewerken.
- Sporters verwijderen.
- Trainingen toevoegen.
- Trainingen bewerken.
- Trainingen verwijderen.
- Een reactiedeadline instellen.
- Gewijzigde trainingen herkennen.
- Oefeningen toevoegen en bekijken.
- Oefeningen aan trainingen koppelen.
- Bij trainingen zien welke oefeningen gekoppeld zijn.
- Definitieve aanwezigheid van sporters registreren.
- Persoonlijke doelen en voortgang van sporters beheren.


## Functies sporter

De sporter kan:

- Veilig inloggen en uitloggen.
- Alleen de eigen sporterfuncties gebruiken.
- Trainingen van het eigen team bekijken.
- De trainingskalender bekijken.
- Datum en tijd van een training bekijken.
- Zien wanneer een training gewijzigd is.
- Zien welke oefeningen bij een training horen.
- Voor de reactiedeadline aangeven of hij aanwezig of afwezig is.
- Na de reactiedeadline de keuze niet meer aanpassen.
- Een definitieve aanwezigheidsstatus niet meer aanpassen.
- Eigen persoonlijke doelen bekijken.
- Eigen voortgang bekijken.
- Het eigen aanwezigheidspercentage bekijken.


## Functionele eisen

De gebouwde functies zijn gekoppeld aan de functionele eisen uit het ontwerp.

### FE-01

De gebruiker kan veilig inloggen en krijgt alleen toegang tot functies die bij zijn rol horen.

### FE-02

De sporter kan trainingen van het eigen team bekijken via een lijst en trainingskalender.

### FE-03

De sporter kan vóór de reactiedeadline aangeven of hij aanwezig of afwezig is.

### FE-04

De trainer kan het team en de sporters beheren.

### FE-05

De trainer kan trainingen toevoegen en aanpassen. Een gewijzigde training wordt duidelijk aangegeven.

### FE-06

De trainer kan oefeningen beheren en oefeningen aan trainingen koppelen. De gekoppelde oefeningen zijn zichtbaar bij de training.

### FE-07

De trainer kan de definitieve aanwezigheid van sporters registreren.

### FE-08

De sporter kan alleen zijn eigen doelen en voortgang bekijken.

### FE-09

De sporter kan zijn eigen aanwezigheidspercentage bekijken. Als er nog geen definitieve aanwezigheden zijn, veroorzaakt dit geen fout.

### FE-10

De applicatie geeft duidelijke meldingen bij bijvoorbeeld succesvolle acties, fouten, lege gegevens en verboden toegang.


## Technische eisen

### TE-01

TeamTrack gebruikt PHP en MySQL/MariaDB voor de applicatie en database.

### TE-02

Rollen en teamtoegang worden aan de serverkant gecontroleerd. Een sporter kan geen trainerpagina's gebruiken.

### TE-03

Wachtwoorden worden veilig opgeslagen. Databasequery's met gebruikersgegevens gebruiken prepared statements. Invoer wordt gecontroleerd en uitvoer wordt veilig weergegeven.

### TE-04

De applicatie heeft een responsive ontwerp en gebruikt duidelijke labels, meldingen en statussen.

### TE-05

De code gebruikt begrijpelijke namen voor bestanden, variabelen, databasevelden en onderdelen van de applicatie.


## Beveiliging

Bij de ontwikkeling van TeamTrack is rekening gehouden met beveiliging.

De applicatie gebruikt:

- PHP-sessies voor ingelogde gebruikers.
- Controle op gebruikersrollen.
- Controle op het team van de gebruiker.
- `password_hash()` voor het veilig opslaan van wachtwoorden.
- `password_verify()` voor het controleren van wachtwoorden.
- Prepared statements voor databasequery's.
- Validatie van gebruikersinvoer.
- `htmlspecialchars()` bij het tonen van gegevens.
- Controle tegen toegang tot functies zonder toestemming.

Een sporter kan bijvoorbeeld niet rechtstreeks een trainerpagina openen.


## Database

TeamTrack gebruikt de volgende belangrijke tabellen:

- `team`
- `user`
- `training`
- `attendance`
- `exercise`
- `training_exercise`
- `goal`

De tabel `training_exercise` koppelt oefeningen aan trainingen.

De tabel `attendance` bewaart de aanwezigheid van sporters.

De tabel `goal` bewaart persoonlijke doelen en voortgang.


## Verschillen tussen ontwerp en realisatie

Tijdens de realisatie is TeamTrack op enkele punten verder uitgewerkt dan in het oorspronkelijke ontwerp.

### Reactiedeadline

Aan de tabel `training` is het veld `reactiedeadline` toegevoegd.

De functionele eis FE-03 bepaalt dat een sporter alleen vóór een deadline zijn aanwezigheid mag doorgeven. Tijdens de realisatie bleek daarom dat deze deadline ook in de database opgeslagen moest worden.

Door `reactiedeadline` toe te voegen kan TeamTrack automatisch controleren of een sporter nog mag reageren.

### Gekoppelde oefeningen zichtbaar bij training

De koppeling tussen oefeningen en trainingen was onderdeel van het ontwerp. Tijdens de realisatie is dit verder uitgewerkt door de gekoppelde oefeningen ook direct bij de training te tonen.

Hierdoor kunnen zowel de trainer als de sporter duidelijk zien welke oefeningen bij een bepaalde training horen.

Deze aanpassingen veranderen het oorspronkelijke doel van TeamTrack niet. Ze zorgen ervoor dat de geplande functies volledig en duidelijk gebruikt kunnen worden.


## Foutafhandeling

TeamTrack controleert verschillende situaties voordat gegevens worden opgeslagen.

Voorbeelden:

- Een gebruiker moet ingelogd zijn.
- De juiste rol wordt gecontroleerd.
- Een trainer werkt alleen met gegevens van het eigen team.
- Een sporter ziet alleen trainingen van het eigen team.
- Ongeldige aanwezigheidskeuzes worden geweigerd.
- De reactiedeadline wordt gecontroleerd.
- Definitieve aanwezigheid kan niet door de sporter worden gewijzigd.
- Databasequery's gebruiken prepared statements.
- Bij lege gegevens worden duidelijke meldingen getoond.


## Responsive ontwerp

TeamTrack is gemaakt voor gebruik op verschillende schermformaten.

De applicatie kan worden gebruikt op:

- Computer
- Laptop
- Tablet
- Telefoon

Onderdelen zoals kaarten, formulieren, knoppen, meldingen en statussen passen zich aan kleinere schermen aan.


## Testen

Tijdens de realisatie zijn de belangrijkste functies getest.

Onder andere is gecontroleerd:

- Trainer kan inloggen.
- Sporter kan inloggen.
- Trainer kan een sporter toevoegen.
- Nieuwe sporter kan inloggen.
- Trainer kan trainingen toevoegen en bewerken.
- Gewijzigde training wordt aangegeven.
- Reactiedeadline werkt.
- Sporter kan aanwezigheid doorgeven.
- Oefeningen kunnen worden aangemaakt en gekoppeld.
- Trainer kan gekoppelde oefeningen bij een training zien.
- Sporter kan gekoppelde oefeningen bij een training zien.
- Trainer kan definitieve aanwezigheid registreren.
- Sporter kan definitieve aanwezigheid niet aanpassen.
- Doelen en voortgang kunnen worden bekeken.
- Een sporter krijgt geen toegang tot trainerpagina's.
- PHP-bestanden zijn gecontroleerd op syntaxfouten.


## Versiebeheer

Voor versiebeheer wordt Git gebruikt.

De broncode staat in een centrale GitHub-repository.

Tijdens de ontwikkeling zijn meerdere commits gemaakt. Hierdoor is stap voor stap terug te zien hoe TeamTrack is opgebouwd en aangepast.

De commitberichten beschrijven welke functionaliteit is toegevoegd of gewijzigd.

Omdat TeamTrack een individueel project van beperkte omvang is, wordt de definitieve versie op de `main` branch bijgehouden.


## GitHub

Repository:

https://github.com/ayaz1233/TeamTrack


## Live versie

TeamTrack is ook online beschikbaar via Plesk:

https://teamtrack.s2209473.jouw.website


## Oplevering

De definitieve oplevering bevat:

- Volledige broncode.
- Databasebestand `database/teamtrack.sql`.
- README met installatie-instructies.
- Testaccounts.
- GitHub repository.
- Werkende live versie.
- Demo/schermopname van de applicatie.


## Eindresultaat

TeamTrack is een werkende Training & Team Portal voor NextPlay.

De belangrijkste geplande functies voor trainer en sporter zijn gerealiseerd. De applicatie gebruikt rollen en teamcontrole, slaat gegevens op in een MySQL/MariaDB-database en bevat beveiliging, invoercontrole en foutafhandeling.

De applicatie kan lokaal via XAMPP en online via de live Plesk-versie worden gebruikt.