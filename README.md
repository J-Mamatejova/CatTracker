# About

This framework was created to support the teaching of the subject Development of intranet and intranet applications 
(VAII) at the [Faculty of Management Science and Informatics](https://www.fri.uniza.sk/) of
[University of Žilina](https://www.uniza.sk/). Framework demonstrates how the MVC architecture works.

# Instructions and documentation 

The framework source code is fully commented. In case you need additional information to understand,
visit the [WIKI stránky](https://github.com/thevajko/vaiicko/wiki/00-%C3%9Avodn%C3%A9-inform%C3%A1cie) (only in Slovak).

# Docker configuration

The Framework has a basic configuration for running and debugging web applications in the `<root>/docker` directory. 
All necessary services are set in `docker-compose.yml` file. After starting them, it creates the following services:

- web server (Apache) with the __PHP 8.3__ 
- MariaDB database server with a created _database_ named according `MYSQL_DATABASE` environment variable
- Adminer application for MariaDB administration

## Other notes:

- __WWW document root__ is set to the `public` in the project directory.
- This repository contains the CatTracker application based on a small teaching MVC framework.
- The website is available at [http://localhost/](http://localhost/).
- The server includes an extension for PHP code debugging [__Xdebug 3__](https://xdebug.org/), uses the  
  port __9003__ and works in "auto-start" mode.
- PHP contains the __PDO__ extension.
- The database server is available locally on the port __3306__. The default login details can be found in `.env` file.
- Adminer is available at [http://localhost:8080/](http://localhost:8080/)

# CatTracker

CatTracker je webová aplikácia vytvorená v rámci semestrálnej práce z predmetu **Vývoj aplikácií pre internet a intranet (VAII)**.  
Aplikácia slúži na evidenciu a zdieľanie informácií o túlavých mačkách v komunitách – ich výskyte, stave a súvisiacich príspevkoch používateľov.

---

## Motivácia

Aplikácia vznikla ako reakcia na reálny problém – vo viacerých oblastiach sa nachádza veľké množstvo túlavých mačiek a ľudia často zdieľajú informácie o ich výskyte, zraneniach alebo správaní neprehľadným spôsobom (napr. v rôznych skupinách).  
Cieľom aplikácie je tieto informácie centralizovať a sprístupniť ich prehľadnou formou.

---

## Funkcionalita aplikácie

Aplikácia obsahuje nasledujúce časti:

### 🏠 Home page
- úvodná stránka aplikácie
- odkazy na databázu mačiek, mapu a feed príspevkov

### 🐱 Databáza mačiek
- zobrazenie zoznamu mačiek s fotografiou, menom a popisom
- detail mačky v samostatnom zobrazení
- CRUD operácie nad entitou **mačky** (Create, Read, Update, Delete)

### 🗺️ Mapa
- zobrazenie mapy s lokalitami výskytu mačiek
- údaje sú načítavané z databázy (entita `locations`)

### 📰 Feed / Príspevky
- feed príspevkov podobný sociálnej sieti
- každý príspevok je viazaný na konkrétnu mačku
- CRUD operácie nad entitou **posts**
- vytváranie a mazanie príspevkov prebieha asynchrónne pomocou **AJAX**

### 👤 Používateľ
- aplikácia obsahuje používateľov (entita `users`)
- príspevky sú viazané na konkrétneho používateľa

---

## Technológie

- **PHP** – serverová logika
- **MySQL** – databáza
- **Docker & Docker Compose** – spúšťanie aplikácie a databázy
- **HTML, CSS** – používateľské rozhranie
- **JavaScript (AJAX)** – asynchrónna komunikácia (vytváranie a mazanie príspevkov)
- **MVC architektúra** – oddelenie aplikačnej logiky a prezentačnej vrstvy
- **Framework Vaiíčko** – poskytnutý univerzitou
- **Git & GitHub** – verzovanie projektu

---

## Databázový model

Aplikácia pracuje s nasledujúcimi hlavnými entitami:

- **macky** – informácie o mačkách
- **locations** – lokality výskytu mačiek (viazané na mačky)
- **posts** – príspevky používateľov (viazané na mačky a používateľov)
- **users** – používatelia aplikácie

Vzťahy:
- mačka → lokácie (1:N)
- mačka → príspevky (1:N)
- používateľ → príspevky (1:N)

---

## AJAX

AJAX je v aplikácii použitý minimálne v dvoch prípadoch:
- vytváranie nového príspevku bez znovunačítania stránky
- mazanie príspevku bez znovunačítania stránky

---

## Spustenie aplikácie

### Požiadavky
- Docker
- Docker Compose

### Postup

1. Naklonuj repozitár:
   ```bash
   git clone https://github.com/J-Mamatejova/CatTracker.git
2. Prejdi do adresára s projektom:
   ```bash
   cd CatTracker/docker
3. Spusti aplikáciu pomocou Docker Compose:
   ```bash
   docker-compose up -d
4. Aplikácia bude dostupná na: 
http://localhost/S