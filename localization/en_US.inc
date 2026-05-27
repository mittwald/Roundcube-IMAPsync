<?php

/*
 * Localization file of the Roundcube IMAPsync plugin (en_US — source of truth).
 * Copyright (c) 2026 Mittwald CM Service GmbH & Co. KG. MIT licensed — see LICENSE at repo root.
 */

$labels = [];
$labels['pagetitle'] = 'Email migration';
$labels['intro'] = 'Copy mail from another IMAP account into this mailbox.';
$labels['noticetitle'] = 'Before you start';
$labels['noticepreserves'] = 'Your existing messages stay untouched — the migration only adds new ones from the source account on top of what is already here. Nothing is overwritten or deleted.';
$labels['noticesynchronoustitle'] = 'Keep this tab open';
$labels['noticesynchronous'] = 'Leave this page open until the migration finishes. If you close the tab first, the run can stop, and you might not see everything that was copied.';
$labels['noticeretrytitle'] = 'If the connection drops';
$labels['noticeretry'] = 'No problem. The migration keeps running on the server even if your browser closes or the connection drops briefly. Just start it again afterwards; already-copied messages are skipped automatically.';
$labels['noticeretrylimit'] = 'No problem. The migration keeps running on the server for up to %seconds% seconds, even if your browser closes or the connection drops briefly. Just start it again afterwards; already-copied messages are skipped automatically.';
$labels['noticeduration'] = 'Large mailboxes can take a few minutes. You won\'t see progress while it runs — the summary shows up at the end.';
$labels['sourcehost'] = 'Source host';
$labels['sourceport'] = 'Source port';
$labels['encryption'] = 'Encryption';
$labels['encryptionssl'] = 'SSL';
$labels['encryptiontls'] = 'STARTTLS';
$labels['encryptionnone'] = 'None';
$labels['sourceuser'] = 'Source username';
$labels['sourcepassword'] = 'Source password';
$labels['startsync'] = 'Start migration';
$labels['cancelsync'] = 'Cancel';
$labels['confirmstart'] = 'Start copying mail from the source account into this mailbox?';
$labels['progresstitle'] = 'Progress';
$labels['summarytitle'] = 'Summary';
$labels['folderssynced'] = 'Folders migrated';
$labels['messagescopied'] = 'Messages copied';
$labels['messagesskipped'] = 'Messages skipped';
$labels['errors'] = 'Errors';
$labels['errorvalidation'] = 'Please check the source IMAP settings.';
$labels['errorconnect'] = 'Could not connect to the source IMAP server.';
$labels['errorinsecure'] = 'Plaintext IMAP is disabled by the administrator.';
$labels['errorhostblocked'] = 'This source host is not allowed.';
$labels['errorquota'] = 'The target mailbox is full. Free up space (delete old messages, or ask your administrator to increase the quota) and start the migration again. Already-copied messages will be skipped on the next run.';
$labels['preflightbutton'] = 'Verify migration';
$labels['preflighttitle'] = 'Pre-flight check';
$labels['preflightcheckconnection'] = 'Connection to source server';
$labels['preflightcheckfolders'] = 'Folder list readable';
$labels['preflightcheckquota'] = 'Target mailbox has enough space';
$labels['preflightfoldersdetail'] = '%count% folders found';
$labels['preflightquotaokdetail'] = 'Source: %source%, destination free: %free%';
$labels['preflightquotafaildetail'] = 'Source (%source%) does not fit into %free% of free space.';
$labels['preflightquotaunknowndetail'] = 'Target server does not provide quota information. Size could not be estimated.';
$labels['preflighttimeoutwarn'] = 'Source data (%source%) may not complete within the %seconds% second sync limit. If the sync times out, restart it: already-copied messages are skipped.';
$labels['preflightreadyhint'] = 'You can start the migration now.';
$labels['preflightnotreadyhint'] = 'Fix the issues above and verify again.';
$labels['donesuccess'] = 'Migration completed successfully.';
$labels['donewitherrors'] = 'Migration completed with errors.';
$labels['supportnotice'] = 'Questions or running into trouble? %link%';
$labels['supportnoticelink'] = 'Open an issue on GitHub';
