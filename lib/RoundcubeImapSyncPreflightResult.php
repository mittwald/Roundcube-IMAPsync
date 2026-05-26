<?php

class RoundcubeImapSyncPreflightResult
{
    public bool $connectionOk = false;
    public ?string $connectionError = null;
    public bool $foldersOk = false;
    public ?string $folderError = null;
    public int $folderCount = 0;
    public ?int $sourceBytes = null;
    public bool $quotaChecked = false;
    public ?int $destinationUsed = null;
    public ?int $destinationLimit = null;
    public bool $quotaFits = false;
    public bool $readyToStart = false;

    public function toArray(): array
    {
        return [
            'connectionOk' => $this->connectionOk,
            'connectionError' => $this->connectionError,
            'foldersOk' => $this->foldersOk,
            'folderError' => $this->folderError,
            'folderCount' => $this->folderCount,
            'sourceBytes' => $this->sourceBytes,
            'quotaChecked' => $this->quotaChecked,
            'destinationUsed' => $this->destinationUsed,
            'destinationLimit' => $this->destinationLimit,
            'quotaFits' => $this->quotaFits,
            'readyToStart' => $this->readyToStart,
        ];
    }
}
