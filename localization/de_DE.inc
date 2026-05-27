<?php

/*
 * Localization file of the Roundcube IMAPsync plugin (de_DE — mirror of en_US).
 * Copyright (c) 2026 Mittwald CM Service GmbH & Co. KG. MIT licensed — see LICENSE at repo root.
 */

$labels = [];
$labels['pagetitle'] = 'E-Mail Umzug';
$labels['intro'] = 'Zieh deine Mails aus einem anderen IMAP-Konto direkt in dieses Postfach.';
$labels['noticetitle'] = 'Gut zu wissen';
$labels['noticepreserves'] = 'Deine vorhandenen Mails bleiben unverändert – wir fügen nur neue Nachrichten aus dem Quellkonto hinzu. Es wird nichts überschrieben oder gelöscht.';
$labels['noticesynchronoustitle'] = 'Tab offen lassen';
$labels['noticesynchronous'] = 'Lass diese Seite geöffnet, bis der Umzug abgeschlossen ist. Schließt du den Tab vorher, kann der Vorgang abbrechen – und du siehst am Ende möglicherweise nicht alles, was kopiert wurde.';
$labels['noticeretrytitle'] = 'Bei Verbindungsabbruch';
$labels['noticeretry'] = 'Kein Problem – der Umzug läuft serverseitig weiter, auch wenn dein Browser zwischendurch geschlossen wird oder die Verbindung kurz abbricht. Starte den Vorgang danach einfach neu; bereits kopierte Mails werden automatisch übersprungen.';
$labels['noticeretrylimit'] = 'Kein Problem – der Umzug läuft serverseitig bis zu %seconds% Sekunden weiter, auch wenn dein Browser zwischendurch geschlossen wird oder die Verbindung kurz abbricht. Starte den Vorgang danach einfach neu; bereits kopierte Mails werden automatisch übersprungen.';
$labels['noticeduration'] = 'Bei großen Postfächern kann der Umzug ein paar Minuten dauern. Den Fortschritt siehst du erst am Ende – nicht zwischendurch.';
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
