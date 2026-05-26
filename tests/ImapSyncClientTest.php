<?php

use PHPUnit\Framework\TestCase;

final class ImapSyncClientTest extends TestCase
{
    public function testOverquotaResultCodeIsQuotaError(): void
    {
        self::assertTrue(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse('OVERQUOTA', 'APPEND: Quota exceeded (mailbox for user is full).'),
        );
    }

    public function testOverquotaResultCodeIsDetectedCaseInsensitively(): void
    {
        self::assertTrue(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse('overquota', 'whatever'),
        );
    }

    public function testEnhancedStatusCode522IsQuotaError(): void
    {
        $mittwaldStyleError = '552 5.2.2 No space left in mailbox / Der Speicherplatz des Postfachs ist vollstaendig belegt';

        self::assertTrue(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse(null, $mittwaldStyleError),
        );
    }

    public function testOverquotaSubstringIsQuotaError(): void
    {
        self::assertTrue(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse(null, 'APPEND: [OVERQUOTA] Mailbox is over quota'),
        );
    }

    public function testUnrelatedErrorIsNotQuotaError(): void
    {
        self::assertFalse(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse(null, 'APPEND: Could not write to disk'),
        );
        self::assertFalse(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse('TRYCREATE', 'Mailbox does not exist'),
        );
    }

    public function testEmptyErrorWithNoResultCodeIsNotQuotaError(): void
    {
        self::assertFalse(RoundcubeImapSyncGenericClient::isQuotaErrorResponse(null, null));
        self::assertFalse(RoundcubeImapSyncGenericClient::isQuotaErrorResponse(null, ''));
    }

    public function testDottedNumberSubstringDoesNotFalsePositive(): void
    {
        // 5.2.2 inside a larger token (e.g. an IP fragment) must not trigger.
        self::assertFalse(
            RoundcubeImapSyncGenericClient::isQuotaErrorResponse(null, 'APPEND: rejected by host 10.5.2.21'),
        );
    }
}
