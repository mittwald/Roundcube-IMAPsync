<?php

interface RoundcubeImapSyncClient
{
    public function connect(string $host, int $port, string $user, string $password, array $options): void;

    public function close(): void;

    public function listFolders(): array;

    public function getHierarchyDelimiter(): string;

    public function createFolder(string $folder): bool;

    public function selectFolder(string $folder): int;

    public function getFolderSize(string $folder): int;

    public function getQuota(string $folder): ?array;

    public function supportsStatusSize(): bool;

    public function fetchMessageIdentities(string $folder): array;

    public function fetchMessageRaw(string $folder, int $uid): ?array;

    public function appendMessage(string $folder, string $rawMessage, array $flags, ?string $internalDate): bool;
}

class RoundcubeImapSyncGenericClient implements RoundcubeImapSyncClient
{
    private ?bool $statusSizeSupported = null;

    public function __construct(private readonly rcube_imap_generic $imap)
    {
    }

    #[\Override]
    public function connect(string $host, int $port, string $user, string $password, array $options): void
    {
        $options['port'] = $port;

        if (!$this->imap->connect($host, $user, $password, $options)) {
            throw new RoundcubeImapSyncException($this->getErrorMessage('Could not connect to IMAP server.'));
        }
    }

    #[\Override]
    public function close(): void
    {
        $this->imap->closeConnection();
    }

    #[\Override]
    public function listFolders(): array
    {
        $folders = $this->imap->listMailboxes('', '*');
        if ($folders === false) {
            throw new RoundcubeImapSyncException($this->getErrorMessage('Could not list IMAP folders.'));
        }

        return array_values(array_map('strval', $folders));
    }

    #[\Override]
    public function getHierarchyDelimiter(): string
    {
        $delimiter = $this->imap->getHierarchyDelimiter();

        return is_string($delimiter) && $delimiter !== '' ? $delimiter : '/';
    }

    #[\Override]
    public function createFolder(string $folder): bool
    {
        return $this->imap->createFolder($folder);
    }

    #[\Override]
    public function selectFolder(string $folder): int
    {
        $status = $this->imap->status($folder, ['MESSAGES']);
        if ($status === false) {
            throw new RoundcubeImapSyncException($this->getErrorMessage("Could not inspect folder {$folder}."));
        }

        return (int) ($status['MESSAGES'] ?? 0);
    }

    #[\Override]
    public function getFolderSize(string $folder): int
    {
        if ($this->supportsStatusSize()) {
            $status = $this->imap->status($folder, ['SIZE']);
            if (is_array($status) && array_key_exists('SIZE', $status)) {
                return (int) $status['SIZE'];
            }
        }

        $totalMessages = $this->selectFolder($folder);
        if ($totalMessages === 0) {
            return 0;
        }

        $messages = $this->imap->fetch($folder, '1:*', false, ['UID', 'RFC822.SIZE']);
        if ($messages === false) {
            throw new RoundcubeImapSyncException($this->getErrorMessage("Could not fetch sizes for {$folder}."));
        }

        $totalSize = 0;
        foreach ($messages as $message) {
            $totalSize += (int) ($message->size ?? 0);
        }

        return $totalSize;
    }

    #[\Override]
    public function getQuota(string $folder): ?array
    {
        $quota = $this->imap->getQuota($folder);
        if ($quota === false) {
            return null;
        }

        return [
            'used' => (int) $quota['used'] * 1024,
            'total' => (int) $quota['total'] * 1024,
        ];
    }

    #[\Override]
    public function supportsStatusSize(): bool
    {
        if ($this->statusSizeSupported === null) {
            $this->statusSizeSupported = (bool) $this->imap->getCapability('STATUS=SIZE');
        }

        return $this->statusSizeSupported;
    }

    #[\Override]
    public function fetchMessageIdentities(string $folder): array
    {
        $totalMessages = $this->selectFolder($folder);
        if ($totalMessages === 0) {
            return [];
        }

        $headers = $this->imap->fetchHeaders($folder, '1:*', false, false, ['MESSAGE-ID']);
        if ($headers === false) {
            throw new RoundcubeImapSyncException($this->getErrorMessage("Could not fetch message headers for {$folder}."));
        }

        $identities = [];
        foreach ($headers as $sequenceId => $header) {
            $uid = (int) ($header->uid ?? $sequenceId);
            $messageId = $this->normalizeMessageId($header->messageID ?? null);
            $subject = isset($header->subject) ? (string) $header->subject : null;
            $date = isset($header->date) ? (string) $header->date : null;

            $identities[$uid] = [
                'message_id' => $messageId,
                'size' => isset($header->size) ? (int) $header->size : null,
                'date' => $date,
                'subject_hash' => $subject !== null ? sha1($subject) : null,
            ];
        }

        return $identities;
    }

    #[\Override]
    public function fetchMessageRaw(string $folder, int $uid): ?array
    {
        $messages = $this->imap->fetch($folder, (string) $uid, true, ['UID', 'RFC822', 'FLAGS', 'INTERNALDATE']);
        if ($messages === false) {
            throw new RoundcubeImapSyncException($this->getErrorMessage("Could not fetch message {$uid} from {$folder}."));
        }

        $message = array_shift($messages);
        if (!$message || !isset($message->body)) {
            return null;
        }

        return [
            'raw' => (string) $message->body,
            'flags' => array_keys((array) ($message->flags ?? [])),
            'internaldate' => isset($message->internaldate) ? (string) $message->internaldate : null,
        ];
    }

    #[\Override]
    public function appendMessage(string $folder, string $rawMessage, array $flags, ?string $internalDate): bool
    {
        $message = $rawMessage;
        $result = $this->imap->append($folder, $message, $flags, $internalDate, false);
        if (!$result && $this->imap->error) {
            $errorMessage = $this->getErrorMessage("Could not append message to {$folder}.");
            $resultCode = is_string($this->imap->resultcode ?? null) ? $this->imap->resultcode : null;
            if (self::isQuotaErrorResponse($resultCode, $this->imap->error)) {
                throw new RoundcubeImapSyncQuotaExceededException($errorMessage);
            }

            throw new RoundcubeImapSyncException($errorMessage);
        }

        // rcube_imap_generic::append returns mixed: false on failure, true on
        // plain success, or the appended UID (string) when the server supports
        // UIDPLUS. Our interface only contracts a bool, so collapse the
        // success cases to true.
        return (bool) $result;
    }

    /**
     * Decide whether an IMAP NO/BAD response signals "destination mailbox is over quota".
     *
     * Three signals, any one of which is sufficient:
     *
     * 1. RFC 5530 / RFC 9208 IMAP response code OVERQUOTA — what stock Dovecot
     *    >= 2.2.30 returns. rcube_imap_generic extracts response codes into
     *    `resultcode` and removes them from the human error text, which is
     *    why we cannot just substring-match the error.
     * 2. RFC 3463 enhanced status code "5.2.2" ("Mailbox full") in the error
     *    text. Operators with a custom `quota_exceeded_message` (e.g. the
     *    Mittwald default) replace the standard response code with a sentence
     *    keyed on 5.2.2 — we want to catch those too. Bounded with whitespace
     *    so we don't false-positive on IP addresses or unrelated dotted-number
     *    fragments.
     * 3. Substring match on "OVERQUOTA" in the error text — defensive catch
     *    for setups where the tag survives into the human message but somehow
     *    not as a response code.
     */
    public static function isQuotaErrorResponse(?string $resultCode, ?string $errorText): bool
    {
        if ($resultCode !== null && strcasecmp($resultCode, 'OVERQUOTA') === 0) {
            return true;
        }

        $error = (string) $errorText;
        if ($error === '') {
            return false;
        }

        if (preg_match('/(?:^|\s)5\.2\.2(?:\s|$)/', $error) === 1) {
            return true;
        }

        if (stripos($error, 'OVERQUOTA') !== false) {
            return true;
        }

        return false;
    }

    private function normalizeMessageId(?string $messageId): ?string
    {
        $messageId = trim((string) $messageId);
        if ($messageId === '' || str_starts_with($messageId, 'mid:')) {
            return null;
        }

        return $messageId;
    }

    private function getErrorMessage(string $fallback): string
    {
        $message = trim((string) ($this->imap->error ?? ''));

        return $message !== '' ? $message : $fallback;
    }
}
