<?php

/*
 * Localization file of the Roundcube IMAPsync plugin (de_DE — mirror of en_US).
 * Copyright (c) 2026 Mittwald CM Service GmbH & Co. KG. MIT licensed — see LICENSE at repo root.
 */

$labels = [];
$labels['pagetitle'] = 'E-Mail Umzug';
$labels['intro'] = 'Kopiert E-Mails aus einem anderen IMAP-Konto in dieses Postfach.';
$labels['noticetitle'] = 'Bitte beachten';
$labels['noticepreserves'] = 'Deine vorhandenen Mails bleiben unangetastet — der Umzug fügt nur die neuen Nachrichten aus dem Quellkonto zusätzlich hinzu. Es wird nichts überschrieben oder gelöscht.';
$labels['noticesynchronous'] = 'Lass diesen Tab geöffnet, bis der Vorgang abgeschlossen ist. Schließt du ihn vorher, läuft der Umzug möglicherweise nicht vollständig durch — und du siehst am Ende auch nicht, was tatsächlich kopiert wurde.';
$labels['noticeretry'] = 'Wenn etwas dazwischenkommt — Tab versehentlich geschlossen, kurz kein Internet, Timeout — starte nicht sofort neu. Der Vorgang läuft im Hintergrund oft noch ein paar Minuten weiter. Schau ins Postfach: sobald keine neuen Mails mehr eintreffen und die Postfachgröße nicht weiter wächst, ist er abgeschlossen und du kannst beruhigt erneut starten. Bereits kopierte Mails überspringt der nächste Lauf automatisch.';
$labels['noticeretrylimit'] = 'Der Sync läuft bis zu %seconds% Sekunden. Wenn dein Browser vorher aufgibt (Tab geschlossen, kurz kein Internet, JavaScript-Timeout), läuft der Vorgang serverseitig bis zu dieser Grenze trotzdem weiter, auch wenn du nichts mehr siehst. Starte deshalb nicht sofort neu. Schau ins Postfach: sobald keine neuen Mails mehr eintreffen und die Postfachgröße nicht weiter wächst, ist er abgeschlossen und du kannst beruhigt erneut starten. Bereits kopierte Mails überspringt der nächste Lauf automatisch.';
$labels['noticeduration'] = 'Bei großen Postfächern kann das ein paar Minuten dauern. Während es läuft, siehst du keinen Fortschritt — die Übersicht erscheint erst am Ende.';
$labels['sourcehost'] = 'Quellserver';
$labels['sourceport'] = 'Quellport';
$labels['encryption'] = 'Verschlüsselung';
$labels['encryptionssl'] = 'SSL';
$labels['encryptiontls'] = 'STARTTLS';
$labels['encryptionnone'] = 'Keine';
$labels['sourceuser'] = 'Quellbenutzername';
$labels['sourcepassword'] = 'Quellpasswort';
$labels['startsync'] = 'Umzug starten';
$labels['cancelsync'] = 'Abbrechen';
$labels['confirmstart'] = 'E-Mails aus dem Quellkonto in dieses Postfach kopieren?';
$labels['progresstitle'] = 'Fortschritt';
$labels['summarytitle'] = 'Zusammenfassung';
$labels['folderssynced'] = 'Umgezogene Ordner';
$labels['messagescopied'] = 'Kopierte Nachrichten';
$labels['messagesskipped'] = 'Übersprungene Nachrichten';
$labels['errors'] = 'Fehler';
$labels['errorvalidation'] = 'Bitte prüfe die IMAP-Zugangsdaten der Quelle.';
$labels['errorconnect'] = 'Verbindung zum IMAP-Quellserver fehlgeschlagen.';
$labels['errorinsecure'] = 'Unverschlüsseltes IMAP wurde vom Administrator deaktiviert.';
$labels['errorhostblocked'] = 'Dieser Quellserver ist nicht erlaubt.';
$labels['errorquota'] = 'Das Ziel-Postfach ist voll. Schaffe Platz (alte Nachrichten löschen oder den Administrator um eine Quota-Erhöhung bitten) und starte den Umzug erneut. Bereits kopierte Nachrichten werden beim nächsten Lauf übersprungen.';
$labels['preflightbutton'] = 'Umzug prüfen';
$labels['preflighttitle'] = 'Vorab-Prüfung';
$labels['preflightcheckconnection'] = 'Verbindung zum Quellserver';
$labels['preflightcheckfolders'] = 'Ordnerliste lesbar';
$labels['preflightcheckquota'] = 'Ziel-Postfach hat voraussichtlich Platz';
$labels['preflightfoldersdetail'] = '%count% Ordner gefunden';
$labels['preflightquotaokdetail'] = 'Quelle: %source%, Ziel frei: %free%';
$labels['preflightquotafaildetail'] = 'Quelle (%source%) passt nicht in %free% freien Speicher.';
$labels['preflightquotaunknowndetail'] = 'Ziel-Server stellt keine Quota-Information bereit. Die Größe konnte nicht abgeschätzt werden.';
$labels['preflighttimeoutwarn'] = 'Die Quelldaten (%source%) reichen voraussichtlich nicht in das Sync-Limit von %seconds% Sekunden. Falls der Sync ausläuft, starte ihn neu: bereits kopierte Mails werden übersprungen.';
$labels['preflightreadyhint'] = 'Du kannst den Umzug jetzt starten.';
$labels['preflightnotreadyhint'] = 'Behebe die Punkte oben und prüfe erneut.';
$labels['donesuccess'] = 'Umzug erfolgreich abgeschlossen.';
$labels['donewitherrors'] = 'Umzug mit Fehlern abgeschlossen.';
$labels['supportnotice'] = 'Fragen oder Probleme? %link%';
$labels['supportnoticelink'] = 'Ein Issue auf GitHub erstellen';
