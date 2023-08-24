<?php

if (!file_exists('./install/config.php')) {
    die("Bitte führen Sie das Installationsskript zuerst aus.");
}

// Konfigurationsdatei und Funktionen einbinden
include './install/config.php';
include 'telegram_bot_funktionen.php';

// Debug-Modus aus einer Datei lesen (angenommen, die Datei enthält entweder "true" oder "false")
$debugMode = (file_get_contents('debug_mode.txt') === 'true');

// Daten von Telegram API lesen
$input = file_get_contents("php://input");
$update = json_decode($input, true);

// Debugging: Eingehende Anfragen speichern, falls Debug-Modus aktiviert ist
speichereAnfrage($input);

// Nachrichten aus dem Kanal in die Gruppen weiterleiten
weiterleitenNachricht($update, $token, $channel_id, $group_ids);

// Kommandos im Gruppenchat behandeln
handleGruppenkommando($update, $token);