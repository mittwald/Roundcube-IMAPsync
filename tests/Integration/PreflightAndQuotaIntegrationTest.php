<?php

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../lib/RoundcubeImapSyncPreflightResult.php';

#[Group('integration')]
final class PreflightAndQuotaIntegrationTest extends TestCase
{
    private const QUOTA_KILOBYTES = 10;
    private const SOURCE_MESSAGE_COUNT = 12;
    private const SOURCE_MESSAGE_SIZE = 1500;

    private static ?DovecotContainer $sourceContainer = null;
    private static ?DovecotContainer $destinationContainer = null;

    public static function setUpBeforeClass(): void
    {
        if (getenv('DOVECOT_INTEGRATION_SKIP') === '1') {
            self::markTestSkipped('DOVECOT_INTEGRATION_SKIP=1; skipping Dovecot integration tests.');
        }

        exec('docker info 2>&1', $dockerOutput, $dockerExitCode);
        if ($dockerExitCode !== 0) {
            self::markTestSkipped('Docker is not available; skipping Dovecot integration tests.');
        }

        if (!class_exists(Testcontainers\Container\GenericContainer::class)) {
            self::markTestSkipped('testcontainers-php is not installed; skipping Dovecot integration tests.');
        }

        self::$sourceContainer = (new DovecotContainer())->start();
        self::$destinationContainer = (new DovecotContainer())
            ->withQuotaKilobytes(self::QUOTA_KILOBYTES)
            ->start();

        self::seedSourceContainer();
    }

    public static function tearDownAfterClass(): void
    {
        try {
            self::$sourceContainer?->stop();
        } finally {
            self::$destinationContainer?->stop();
            self::$sourceContainer = null;
            self::$destinationContainer = null;
        }
    }

    public function testOverquotaResponseStopsSyncAsFatal(): void
    {
        $result = self::runSync();

        // The structural assertion is what we care about: the engine
        // identified the failure as an over-quota response (via
        // rcube_imap_generic's resultcode, RFC 5530) and stopped the sync
        // before walking every source message. We deliberately do NOT
        // assert on $result->fatalError's wording — rcube_imap_generic
        // strips the [OVERQUOTA] tag from the error string, and Dovecot's
        // human-readable text is just "Quota exceeded (mailbox for user
        // is full)" with no further token we could pin on.
        self::assertTrue($result->quotaExceeded, 'Expected quotaExceeded flag to be set.');
        self::assertNotNull($result->fatalError);
        self::assertLessThan(self::SOURCE_MESSAGE_COUNT, $result->messagesCopied);
    }

    public function testPreflightDetectsQuotaMismatch(): void
    {
        $result = self::runPreflight();

        self::assertTrue($result->connectionOk);
        self::assertTrue($result->foldersOk);
        self::assertTrue($result->quotaChecked, 'Destination should announce QUOTA capability.');
        self::assertFalse($result->quotaFits);
        self::assertFalse($result->readyToStart);
        self::assertSame(self::QUOTA_KILOBYTES * 1024, $result->destinationLimit);
        self::assertGreaterThan(0, $result->sourceBytes);
    }

    public function testPreflightDetectsQuotaFitWithLargeLimit(): void
    {
        $generousContainer = (new DovecotContainer())
            ->withQuotaKilobytes(10_000)
            ->start();

        try {
            $result = self::runPreflight($generousContainer);

            self::assertTrue($result->quotaChecked);
            self::assertTrue($result->quotaFits);
            self::assertTrue($result->readyToStart);
        } finally {
            $generousContainer->stop();
        }
    }

    public function testOverquotaIsDetectedWithCustomEnhancedStatusMessage(): void
    {
        // Some Dovecot installations (e.g. Mittwald's default) replace the
        // standard [OVERQUOTA] response code with a custom human message
        // keyed on RFC 3463 enhanced status code 5.2.2. This test exercises
        // that variant against real Dovecot to guard against the detection
        // silently falling back to "per-message error".
        $customMessageContainer = (new DovecotContainer())
            ->withQuotaKilobytes(self::QUOTA_KILOBYTES)
            ->withQuotaExceededMessage('552 5.2.2 No space left in mailbox / Der Speicherplatz des Postfachs ist vollstaendig belegt')
            ->start();

        try {
            $result = self::runSync($customMessageContainer);

            self::assertTrue($result->quotaExceeded, 'Expected quotaExceeded flag to be set under custom 5.2.2 message.');
            self::assertNotNull($result->fatalError);
            self::assertLessThan(self::SOURCE_MESSAGE_COUNT, $result->messagesCopied);
        } finally {
            $customMessageContainer->stop();
        }
    }

    private static function seedSourceContainer(): void
    {
        $sourceImap = self::connectToContainer(self::requireSourceContainer());

        try {
            for ($messageNumber = 1; $messageNumber <= self::SOURCE_MESSAGE_COUNT; $messageNumber++) {
                self::appendMessage(
                    $sourceImap,
                    'INBOX',
                    self::buildMessage(
                        "<integration-quota-{$messageNumber}@example.test>",
                        "Quota source {$messageNumber}",
                        str_repeat('x', self::SOURCE_MESSAGE_SIZE),
                    ),
                );
            }

            self::consumeRecentFlags($sourceImap, 'INBOX');
        } finally {
            $sourceImap->closeConnection();
        }
    }

    private static function runSync(?DovecotContainer $destinationContainer = null): RoundcubeImapSyncResult
    {
        $sourceContainer = self::requireSourceContainer();
        $destinationContainer ??= self::requireDestinationContainer();
        $sourceClient = new RoundcubeImapSyncGenericClient(new rcube_imap_generic());
        $destinationClient = new RoundcubeImapSyncGenericClient(new rcube_imap_generic());
        $destinationClient->connect(
            $destinationContainer->getHost(),
            $destinationContainer->getMappedImapPort(),
            $destinationContainer->getUser(),
            $destinationContainer->getPassword(),
            ['ssl_mode' => null, 'timeout' => 10],
        );

        try {
            $job = new RoundcubeImapSyncJob(
                $sourceContainer->getHost(),
                $sourceContainer->getMappedImapPort(),
                'none',
                $sourceContainer->getUser(),
                $sourceContainer->getPassword(),
            );
            $engine = new RoundcubeImapSyncEngine($sourceClient, $destinationClient);

            return $engine->run($job, static function (string $folder, int $current, int $total): void {
            });
        } finally {
            $destinationClient->close();
        }
    }

    private static function runPreflight(?DovecotContainer $destinationContainer = null): RoundcubeImapSyncPreflightResult
    {
        $sourceContainer = self::requireSourceContainer();
        $destinationContainer ??= self::requireDestinationContainer();
        $sourceClient = new RoundcubeImapSyncGenericClient(new rcube_imap_generic());
        $destinationClient = new RoundcubeImapSyncGenericClient(new rcube_imap_generic());
        $destinationClient->connect(
            $destinationContainer->getHost(),
            $destinationContainer->getMappedImapPort(),
            $destinationContainer->getUser(),
            $destinationContainer->getPassword(),
            ['ssl_mode' => null, 'timeout' => 10],
        );

        try {
            $job = new RoundcubeImapSyncJob(
                $sourceContainer->getHost(),
                $sourceContainer->getMappedImapPort(),
                'none',
                $sourceContainer->getUser(),
                $sourceContainer->getPassword(),
            );
            $engine = new RoundcubeImapSyncEngine($sourceClient, $destinationClient);

            return $engine->preflight($job);
        } finally {
            $destinationClient->close();
        }
    }

    private static function appendMessage(rcube_imap_generic $imap, string $folder, string $message): void
    {
        $messageToAppend = $message;

        if (!$imap->append($folder, $messageToAppend, ['SEEN'], '19-May-2026 12:00:00 +0000', false)) {
            self::fail("Could not append message to {$folder}: " . $imap->error);
        }
    }

    private static function consumeRecentFlags(rcube_imap_generic $imap, string $folder): void
    {
        if ($imap->fetchHeaders($folder, '1:*', false, false, ['MESSAGE-ID']) === false) {
            self::fail("Could not consume recent flags in {$folder}: " . $imap->error);
        }
    }

    private static function buildMessage(string $messageId, string $subject, string $body): string
    {
        return implode("\r\n", [
            'Message-ID: ' . $messageId,
            'Date: Tue, 19 May 2026 12:00:00 +0000',
            'From: Source <source@example.test>',
            'To: Destination <destination@example.test>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            '',
            $body,
        ]);
    }

    private static function connectToContainer(DovecotContainer $container): rcube_imap_generic
    {
        $imap = new rcube_imap_generic();

        if (!$imap->connect(
            $container->getHost(),
            $container->getUser(),
            $container->getPassword(),
            [
                'port' => $container->getMappedImapPort(),
                'ssl_mode' => null,
                'timeout' => 10,
            ],
        )) {
            self::fail('Could not connect to Dovecot: ' . $imap->error);
        }

        return $imap;
    }

    private static function requireSourceContainer(): DovecotContainer
    {
        if (self::$sourceContainer === null) {
            self::fail('Source Dovecot container was not started.');
        }

        return self::$sourceContainer;
    }

    private static function requireDestinationContainer(): DovecotContainer
    {
        if (self::$destinationContainer === null) {
            self::fail('Destination Dovecot container was not started.');
        }

        return self::$destinationContainer;
    }
}
