#!/bin/bash

# Aktualisiere Paketlisten
sudo apt update

# Installiere MariaDB-Server
sudo apt install -y mariadb-server

# Sichere die MariaDB-Installation (dieser Schritt erfordert Interaktion vom Benutzer)
sudo mysql_secure_installation

# Erstelle Datenbank, Benutzer und Tabellen

# Verwende ein starkes Passwort für den Datenbankbenutzer
DB_PASS="St4rk3sP@ssw0rt!"

# Befehle für MariaDB
SQL_COMMANDS="
CREATE DATABASE telegramBotDB;
CREATE USER 'telegramBotUser'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON telegramBotDB.* TO 'telegramBotUser'@'localhost';
FLUSH PRIVILEGES;

USE telegramBotDB;

CREATE TABLE warteschlange (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE skript_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(10) NOT NULL DEFAULT 'inaktiv',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO skript_status (status) VALUES ('inaktiv');
"

# Führe die SQL-Befehle aus
echo "$SQL_COMMANDS" | sudo mysql -u root -p

echo "Setup abgeschlossen!"